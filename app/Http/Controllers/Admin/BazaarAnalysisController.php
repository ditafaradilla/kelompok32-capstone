<?php

namespace App\Http\Controllers\Admin;

use App\Services\ProductSalesRepository;
use App\Services\SalesAnalytics;
use Illuminate\Http\Request;

class BazaarAnalysisController extends BiController
{
    public function index(Request $request, SalesAnalytics $analytics, ProductSalesRepository $products)
    {
        // Default: 6 bulan terakhir, karena bazar tidak diadakan tiap minggu
        [$from, $to] = $this->period($request, now()->subMonths(6)->startOfMonth()->toDateString());

        $events   = $analytics->bazaarPerformance($from, $to);
        $channel  = $analytics->byChannel($from, $to);
        $untagged = $analytics->untaggedBazaarSales($from, $to);

        $topProducts = null;
        $productError = null;
        if ($products->enabled()) {
            [$topProducts, $productError] = $this->tryProducts(
                fn () => $products->productSales($from, $to)->sortByDesc('qty')->take(10)->values()
            );
        }

        return view('Admin.bi.bazar', [
            'from'         => $from,
            'to'           => $to,
            'events'       => $events,
            'channel'      => $channel,
            'untagged'     => $untagged,
            'topProducts'  => $topProducts,
            'productError' => $productError,
            'productsOn'   => $products->enabled(),
            'presets'      => $this->presets(),
        ]);
    }
}