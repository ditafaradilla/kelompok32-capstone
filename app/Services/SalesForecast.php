<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Forecasting penjualan MINGGUAN yang sederhana:
 *  - rata-rata bergerak (rata-rata 4 minggu terakhir, hasil datar)
 *  - tren linear (garis lurus dari riwayat)
 * Hanya minggu PENUH (Senin-Minggu) yang dipakai; minggu berjalan diabaikan.
 */
class SalesForecast
{
    public const MIN_WEEKS = 4;

    public function __construct(private SalesAnalytics $analytics)
    {
    }

    /** Riwayat penjualan per minggu penuh. Minggu kosong sebelum penjualan pertama dibuang. */
    public function weeklyHistory(int $weeks): array
    {
        $lastWeekStart = now()->startOfWeek(Carbon::MONDAY)->subWeek();
        $lastEnd       = $lastWeekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $firstStart    = $lastWeekStart->copy()->subWeeks($weeks - 1);

        $rows = $this->analytics->salesQuery($firstStart->toDateString(), $lastEnd->toDateString())->toBase()
            ->selectRaw('transaction_date AS d, SUM(amount) AS total')
            ->groupBy('transaction_date')
            ->get();

        $totals = [];
        for ($d = $firstStart->copy(); $d <= $lastWeekStart; $d->addWeek()) {
            $totals[$d->toDateString()] = 0;
        }
        foreach ($rows as $row) {
            $key = Carbon::parse(substr((string) $row->d, 0, 10))->startOfWeek(Carbon::MONDAY)->toDateString();
            if (isset($totals[$key])) {
                $totals[$key] += (int) $row->total;
            }
        }

        $keys   = array_keys($totals);
        $values = array_values($totals);

        $skip = 0;
        while ($skip < count($values) && $values[$skip] === 0) {
            $skip++;
        }
        $keys   = array_slice($keys, $skip);
        $values = array_slice($values, $skip);

        return [
            'keys'   => $keys,
            'values' => $values,
            'labels' => array_map(fn ($k) => Carbon::parse($k)->locale('id')->translatedFormat('d M'), $keys),
            'count'  => count($values),
            'from'   => $keys[0] ?? null,
            'to'     => $lastEnd->toDateString(),
        ];
    }

    public function forecast(array $values, int $horizon): array
    {
        $window = min(4, count($values));

        return [
            'window'         => $window,
            'moving_average' => array_fill(0, $horizon, (int) round($this->movingAverage($values, $window))),
            'linear'         => $this->linearTrend($values, $horizon),
            'mape'           => $this->backtestMape($values, $window),
        ];
    }

    public function movingAverage(array $series, int $window): float
    {
        $slice = array_slice($series, -$window);

        return count($slice) > 0 ? array_sum($slice) / count($slice) : 0.0;
    }

    /** Garis tren (kuadrat terkecil). Hasil tidak boleh negatif. @return int[] */
    public function linearTrend(array $series, int $horizon): array
    {
        $n = count($series);
        if ($n < 2) {
            return array_fill(0, $horizon, (int) ($series[0] ?? 0));
        }

        $meanX = ($n - 1) / 2;
        $meanY = array_sum($series) / $n;
        $num = 0.0;
        $den = 0.0;
        foreach (array_values($series) as $i => $y) {
            $num += ($i - $meanX) * ($y - $meanY);
            $den += ($i - $meanX) ** 2;
        }
        $slope     = $den > 0 ? $num / $den : 0.0;
        $intercept = $meanY - $slope * $meanX;

        $out = [];
        for ($h = 1; $h <= $horizon; $h++) {
            $out[] = max(0, (int) round($intercept + $slope * ($n - 1 + $h)));
        }

        return $out;
    }

    /** Rata-rata kesalahan persen (MAPE) bila rata-rata bergerak dipakai menebak 1 minggu ke depan. */
    public function backtestMape(array $series, int $window): ?float
    {
        $errors = [];
        for ($i = $window; $i < count($series); $i++) {
            if ($series[$i] > 0) {
                $pred     = array_sum(array_slice($series, $i - $window, $window)) / $window;
                $errors[] = abs($series[$i] - $pred) / $series[$i];
            }
        }

        return $errors ? round(array_sum($errors) / count($errors) * 100, 1) : null;
    }

    /**
     * Anggaran belanja stok = rasio (pembelian stok / penjualan) pada riwayat x ramalan penjualan.
     * Memakai data Pembukuan saja, jadi berlaku walau tabel produk belum ada.
     */
    public function stockBudget(array $history, int $forecastSales): array
    {
        $sales = array_sum($history['values']);
        $purchases = (int) Transaction::query()
            ->where('type', Transaction::TYPE_EXPENSE)
            ->where('category', Transaction::STOCK_PURCHASE_CATEGORY)
            ->whereBetween('transaction_date', [$history['from'], $history['to']])
            ->sum('amount');

        $ratio = $sales > 0 ? $purchases / $sales : null;

        return [
            'sales'     => $sales,
            'purchases' => $purchases,
            'ratio'     => $ratio !== null ? round($ratio * 100, 1) : null,
            'budget'    => $ratio !== null ? (int) round($ratio * $forecastSales) : null,
        ];
    }

    /**
     * Rekomendasi stok per produk (butuh ProductSalesRepository aktif).
     * kebutuhan = rata-rata terjual per minggu x horizon; target = kebutuhan + cadangan 20%.
     */
    public function stockRecommendations(Collection $productSales, int $historyWeeks, int $horizon, float $safety = 0.2): Collection
    {
        return $productSales->map(function ($p) use ($historyWeeks, $horizon, $safety) {
            $weekly = $historyWeeks > 0 ? $p->qty / $historyWeeks : 0;
            $demand = $weekly * $horizon;
            $target = (int) ceil($demand * (1 + $safety));
            $cover  = ($p->stock !== null && $weekly > 0) ? $p->stock / $weekly : null;

            $status = match (true) {
                $p->stock === null => 'Stok tidak tercatat',
                $cover !== null && $cover < 1 => 'Kritis',
                $cover !== null && $cover < 2 => 'Segera restok',
                default => 'Aman',
            };

            return (object) [
                'name'    => $p->name,
                'stock'   => $p->stock,
                'weekly'  => round($weekly, 1),
                'demand'  => (int) ceil($demand),
                'target'  => $target,
                'restock' => $p->stock !== null ? max(0, $target - $p->stock) : null,
                'cover'   => $cover !== null ? round($cover, 1) : null,
                'status'  => $status,
            ];
        })->sortBy(fn ($r) => match ($r->status) {
            'Kritis' => 0, 'Segera restok' => 1, 'Stok tidak tercatat' => 3, default => 2,
        })->values();
    }
}