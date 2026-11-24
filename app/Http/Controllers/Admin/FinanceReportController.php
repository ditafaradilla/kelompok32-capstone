<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from'  => 'nullable|date',
            'to'    => 'nullable|date|after_or_equal:from',
            'group' => 'nullable|in:harian,bulanan',
        ]);

        $from  = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to    = $request->input('to') ?: now()->endOfMonth()->toDateString();
        $group = $request->input('group', 'harian');

        $base = Transaction::query()->whereBetween('transaction_date', [$from, $to]);

        // Ringkasan memakai perhitungan yang sama dengan halaman Pembukuan
        $summary = Transaction::summarize($base);

        // Laporan kas: hanya transaksi LUNAS
        $paid = (clone $base)->where('payment_status', Transaction::STATUS_PAID);

        $rows = (clone $paid)
            ->selectRaw('transaction_date, type, SUM(amount) AS total')
            ->groupBy('transaction_date', 'type')
            ->orderBy('transaction_date')
            ->get();

        $timeline = [];
        foreach ($rows as $row) {
            $key = $group === 'bulanan'
                ? substr($row->transaction_date, 0, 7)
                : substr($row->transaction_date, 0, 10);

            $timeline[$key] ??= ['income' => 0, 'expense' => 0];
            $timeline[$key][$row->type] += (int) $row->total;
        }
        ksort($timeline);

        $timeline = collect($timeline)->map(function ($v, $key) use ($group) {
            $date = Carbon::parse($group === 'bulanan' ? $key . '-01' : $key)->locale('id');

            return [
                'label'   => $group === 'bulanan' ? $date->translatedFormat('F Y') : $date->translatedFormat('d M Y'),
                'income'  => $v['income'],
                'expense' => $v['expense'],
                'balance' => $v['income'] - $v['expense'],
            ];
        })->values();

        $byCategory = (clone $paid)
            ->selectRaw('type, category, SUM(amount) AS total')
            ->groupBy('type', 'category')
            ->orderByDesc('total')
            ->get()
            ->groupBy('type');

        $presets = [
            'Bulan ini'  => [now()->startOfMonth(), now()->endOfMonth()],
            'Bulan lalu' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'Tahun ini'  => [now()->startOfYear(), now()->endOfYear()],
        ];
        $presets = collect($presets)->map(fn ($p) => [
            'from' => $p[0]->toDateString(),
            'to'   => $p[1]->toDateString(),
        ]);

        return view('Admin.keuangan.laporan', [
            'summary'    => $summary,
            'timeline'   => $timeline,
            'incomeCats' => $byCategory->get(Transaction::TYPE_INCOME, collect()),
            'expenseCats'=> $byCategory->get(Transaction::TYPE_EXPENSE, collect()),
            'from'       => $from,
            'to'         => $to,
            'group'      => $group,
            'presets'    => $presets,
        ]);
    }
}