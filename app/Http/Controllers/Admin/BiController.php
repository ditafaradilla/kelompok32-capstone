<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Dasar untuk controller dashboard / bazar / forecast: validasi periode dan tombol periode cepat. */
abstract class BiController extends Controller
{
    /** @return array{0:string,1:string} [dari, sampai] */
    protected function period(Request $request, ?string $defaultFrom = null): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->input('from') ?: ($defaultFrom ?? now()->startOfMonth()->toDateString());
        $to   = $request->input('to') ?: now()->toDateString();

        if ((int) abs(Carbon::parse($from)->diffInDays(Carbon::parse($to))) > 366) {
            throw ValidationException::withMessages(['to' => 'Rentang tanggal maksimal 366 hari.']);
        }

        return [$from, $to];
    }

    protected function presets(): array
    {
        $lastMonth = now()->subMonthNoOverflow();

        return collect([
            'Bulan ini'        => [now()->startOfMonth(), now()],
            '30 hari terakhir' => [now()->subDays(29), now()],
            'Bulan lalu'       => [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()],
            'Tahun ini'        => [now()->startOfYear(), now()],
        ])->map(fn ($p) => ['from' => $p[0]->toDateString(), 'to' => $p[1]->toDateString()])->all();
    }

    /** Menjalankan pembacaan data produk; bila config/bi.php belum cocok, tampilkan pesan, bukan error 500. */
    protected function tryProducts(callable $fn): array
    {
        try {
            return [$fn(), null];
        } catch (\Throwable $e) {
            report($e);

            return [null, 'Pengaturan config/bi.php belum sesuai dengan database: ' . $e->getMessage()];
        }
    }
}