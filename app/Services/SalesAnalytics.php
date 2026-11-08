<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Perhitungan penjualan dari tabel `transactions` (pemasukan LUNAS kategori penjualan).
 * Dipakai bersama oleh Dashboard, Analisis Bazar, dan Forecasting.
 */
class SalesAnalytics
{
    public function salesQuery(string $from, string $to): Builder
    {
        return Transaction::query()
            ->where('type', Transaction::TYPE_INCOME)
            ->where('payment_status', Transaction::STATUS_PAID)
            ->whereIn('category', Transaction::SALES_CATEGORIES)
            ->whereBetween('transaction_date', [$from, $to]);
    }

    /** KPI periode ini dibanding periode sebelumnya yang panjangnya sama. */
    public function kpi(string $from, string $to): array
    {
        $current = $this->totals($from, $to);

        $days     = (int) abs(Carbon::parse($from)->diffInDays(Carbon::parse($to))) + 1;
        $prevTo   = Carbon::parse($from)->subDay()->toDateString();
        $prevFrom = Carbon::parse($from)->subDays($days)->toDateString();
        $previous = $this->totals($prevFrom, $prevTo);

        $growth = $previous['total'] > 0
            ? round(($current['total'] - $previous['total']) / $previous['total'] * 100, 1)
            : null;

        return $current + [
            'previous_total' => $previous['total'],
            'growth'         => $growth,
            'prev_from'      => $prevFrom,
            'prev_to'        => $prevTo,
            'days'           => $days,
        ];
    }

    private function totals(string $from, string $to): array
    {
        $row = $this->salesQuery($from, $to)->toBase()
            ->selectRaw('COALESCE(SUM(amount), 0) AS total, COUNT(*) AS trx')
            ->first();

        $total = (int) $row->total;
        $count = (int) $row->trx;

        return [
            'total'   => $total,
            'count'   => $count,
            'average' => $count > 0 ? intdiv($total, $count) : 0,
        ];
    }

    /** Tren penjualan, hari/bulan tanpa transaksi diisi 0. @return array{labels:array,values:array} */
    public function trend(string $from, string $to, string $group = 'harian'): array
    {
        $byDate = $this->salesQuery($from, $to)->toBase()
            ->selectRaw('transaction_date AS d, SUM(amount) AS total')
            ->groupBy('transaction_date')
            ->get()
            ->mapWithKeys(fn ($r) => [substr((string) $r->d, 0, 10) => (int) $r->total]);

        $labels = [];
        $values = [];

        if ($group === 'bulanan') {
            $sums = [];
            foreach ($byDate as $d => $t) {
                $k = substr($d, 0, 7);
                $sums[$k] = ($sums[$k] ?? 0) + $t;
            }
            $cursor = Carbon::parse($from)->startOfMonth();
            $end    = Carbon::parse($to)->startOfMonth();
            while ($cursor <= $end) {
                $labels[] = $cursor->copy()->locale('id')->translatedFormat('M Y');
                $values[] = $sums[$cursor->format('Y-m')] ?? 0;
                $cursor->addMonth();
            }
        } else {
            $cursor = Carbon::parse($from);
            $end    = Carbon::parse($to);
            while ($cursor <= $end) {
                $labels[] = $cursor->copy()->locale('id')->translatedFormat('d M');
                $values[] = $byDate[$cursor->toDateString()] ?? 0;
                $cursor->addDay();
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** Penjualan per saluran: online, bazar, offline. @return array<string,int> */
    public function byChannel(string $from, string $to): array
    {
        $sums = $this->salesQuery($from, $to)->toBase()
            ->selectRaw('category, SUM(amount) AS total')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->category => (int) $r->total]);

        $out = [];
        foreach (Transaction::SALES_CATEGORIES as $category) {
            $out[$category] = $sums[$category] ?? 0;
        }

        return $out;
    }

    /**
     * Kinerja tiap bazar (dikelompokkan dari kolom "Nama bazar" di Pembukuan).
     * Pemasukan = penjualan LUNAS. Pengeluaran dihitung penuh (termasuk yang belum dibayar)
     * karena biaya bazar tetap terjadi, supaya laba bazar tidak terlalu optimistis.
     */
    public function bazaarPerformance(string $from, string $to): Collection
    {
        return Transaction::query()
            ->whereNotNull('event_name')
            ->where('event_name', '!=', '')
            ->whereBetween('transaction_date', [$from, $to])
            ->toBase()
            ->selectRaw("
                event_name,
                MIN(transaction_date) AS start_date,
                MAX(transaction_date) AS end_date,
                COUNT(*) AS trx_count,
                COALESCE(SUM(CASE WHEN type = 'income' AND payment_status = 'lunas' THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
            ")
            ->groupBy('event_name')
            ->get()
            ->map(function ($r) {
                $income  = (int) $r->income;
                $expense = (int) $r->expense;
                $net     = $income - $expense;

                return (object) [
                    'name'    => $r->event_name,
                    'start'   => substr((string) $r->start_date, 0, 10),
                    'end'     => substr((string) $r->end_date, 0, 10),
                    'trx'     => (int) $r->trx_count,
                    'income'  => $income,
                    'expense' => $expense,
                    'net'     => $net,
                    'roi'     => $expense > 0 ? round($net / $expense * 100, 1) : null,
                ];
            })
            ->sortByDesc('net')
            ->values();
    }

    /** Penjualan bazar yang belum diberi nama bazar (tidak masuk tabel per bazar). */
    public function untaggedBazaarSales(string $from, string $to): int
    {
        return (int) $this->salesQuery($from, $to)
            ->where('category', 'Penjualan Bazar')
            ->where(fn ($q) => $q->whereNull('event_name')->orWhere('event_name', ''))
            ->sum('amount');
    }
}