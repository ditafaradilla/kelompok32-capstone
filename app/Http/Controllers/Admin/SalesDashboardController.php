<?php

namespace App\Http\Controllers\Admin;

use App\Services\ProductSalesRepository;
use App\Services\SalesAnalytics;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesDashboardController extends BiController
{
    /**
     * Dashboard analisis penjualan (jika masih diperlukan).
     */
    public function index(
        Request $request,
        SalesAnalytics $analytics,
        ProductSalesRepository $products
    ) {
        $data = $this->dashboardData($request, $analytics, $products);

        return view('Admin.BI.dashboard', $data);
    }

    /**
     * Beranda Admin dengan Dashboard Penjualan dan Analisis Bazar.
     */
    public function home(
        Request $request,
        SalesAnalytics $analytics,
        ProductSalesRepository $products
    ) {
        $data = $this->dashboardData($request, $analytics, $products);

        return view('Admin.beranda-admin', $data);
    }

    /**
     * Mengambil seluruh data untuk dashboard.
     */
    private function dashboardData(
        Request $request,
        SalesAnalytics $analytics,
        ProductSalesRepository $products
    ): array {
        [$from, $to] = $this->period($request);

        $request->validate([
            'group' => 'nullable|in:harian,bulanan',
        ]);

        $days = (int) abs(
            Carbon::parse($from)->diffInDays(Carbon::parse($to))
        ) + 1;

        $group = $request->input('group')
            ?: ($days > 62 ? 'bulanan' : 'harian');

        // RINGKASAN PENJUALAN
        $kpi = $analytics->kpi($from, $to);
        $trend = $analytics->trend($from, $to, $group);
        $channel = $analytics->byChannel($from, $to);

        // ANALISIS BAZAR
        $events = $analytics->bazaarPerformance($from, $to);
        $untagged = $analytics->untaggedBazaarSales($from, $to);

        // Bazar paling menguntungkan
        $topBazaar = $events->first();

        // DATA PRODUK DAN PESANAN
        $orderCount = null;
        $topProducts = null;
        $productError = null;

        if ($products->enabled()) {
            [$productData, $productError] = $this->tryProducts(fn () => [
                'orders' => $products->orderCount($from, $to),
                'top' => $products->productSales($from, $to)
                    ->sortByDesc('qty')
                    ->take(10)
                    ->values(),
            ]);

            $orderCount = $productData['orders'] ?? null;
            $topProducts = $productData['top'] ?? null;
        }

        return [
            'from' => $from,
            'to' => $to,
            'group' => $group,

            // Penjualan
            'kpi' => $kpi,
            'trend' => $trend,
            'channel' => $channel,

            // Bazar
            'events' => $events,
            'untagged' => $untagged,
            'topBazaar' => $topBazaar,

            // Produk
            'orderCount' => $orderCount,
            'topProducts' => $topProducts,
            'productError' => $productError,
            'productsOn' => $products->enabled(),

            'presets' => $this->presets(),
        ];
    }
}