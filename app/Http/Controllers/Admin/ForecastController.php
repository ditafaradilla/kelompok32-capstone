<?php

namespace App\Http\Controllers\Admin;

use App\Services\ProductSalesRepository;
use App\Services\SalesForecast;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ForecastController extends BiController
{
    public function index(Request $request, SalesForecast $forecast, ProductSalesRepository $products)
    {
        $request->validate([
            'weeks'   => 'nullable|integer|min:4|max:52',
            'horizon' => 'nullable|integer|min:1|max:12',
        ]);

        $weeks   = (int) ($request->input('weeks') ?: 12);
        $horizon = (int) ($request->input('horizon') ?: 4);

        $history = $forecast->weeklyHistory($weeks);
        $enough  = $history['count'] >= SalesForecast::MIN_WEEKS;

        $result = $chart = $budget = $futureLabels = null;
        $stock = null;
        $productError = null;

        if ($enough) {
            $result = $forecast->forecast($history['values'], $horizon);

            $lastStart    = Carbon::parse(end($history['keys']));
            $futureLabels = [];
            for ($h = 1; $h <= $horizon; $h++) {
                $futureLabels[] = $lastStart->copy()->addWeeks($h)->locale('id')->translatedFormat('d M');
            }

            $lastActual = $history['values'][$history['count'] - 1];
            $padding    = array_fill(0, $history['count'] - 1, null);

            $chart = [
                'labels'  => array_merge($history['labels'], $futureLabels),
                'history' => array_merge($history['values'], array_fill(0, $horizon, null)),
                'ma'      => array_merge($padding, [$lastActual], $result['moving_average']),
                'linear'  => array_merge($padding, [$lastActual], $result['linear']),
            ];

            $budget = $forecast->stockBudget($history, array_sum($result['moving_average']));

            if ($products->enabled()) {
                [$stock, $productError] = $this->tryProducts(
                    fn () => $forecast->stockRecommendations(
                        $products->productSales($history['from'], $history['to']),
                        $history['count'],
                        $horizon
                    )
                );
            }
        }

        return view('Admin.bi.forecast', [
            'weeks'        => $weeks,
            'horizon'      => $horizon,
            'history'      => $history,
            'enough'       => $enough,
            'minWeeks'     => SalesForecast::MIN_WEEKS,
            'result'       => $result,
            'chart'        => $chart,
            'futureLabels' => $futureLabels,
            'budget'       => $budget,
            'stock'        => $stock,
            'productError' => $productError,
            'productsOn'   => $products->enabled(),
        ]);
    }
}