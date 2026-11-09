<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SizeGuide;
use App\Services\SizeRecommender;
use Illuminate\Http\Request;

class SizeGuideController extends Controller
{
    /**
     * Tampilkan Halaman Panduan Ukuran & Kalkulator Cepat Rekomendasi Ukuran
     */
    public function index(Request $request, SizeRecommender $recommender)
    {
        $sizeGuides = SizeGuide::orderBy('id')->get();
        $calcResult = null;

        if ($request->filled('usia')) {
            $usia = (int) $request->input('usia');
            $berat = $request->filled('berat') ? (float) $request->input('berat') : null;
            $tinggi = $request->filled('tinggi') ? (float) $request->input('tinggi') : null;

            $calcResult = $recommender->recommendFromMeasurements($usia, $berat, $tinggi);
        }

        return view('User.panduan-ukuran', compact('sizeGuides', 'calcResult'));
    }
}

