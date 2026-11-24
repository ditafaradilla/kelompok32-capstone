<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SizeGuide;
use App\Services\SizeRecommender;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Tampilkan Beranda Pelanggan (8 produk terbaru + filter)
     */
    public function home()
    {
        $products = Product::with(['variants.stocks.location'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(8)
            ->get();
        $sizeGuides = SizeGuide::all();

        return view('User.beranda-user', compact('products', 'sizeGuides'));
    }

    /**
     * Tampilkan Katalog Produk dengan Pencarian, Filter & Pengurutan
     */
    public function index(Request $request)
    {
        $query = Product::with(['variants.stocks.location'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // 1. Pencarian berdasarkan nama produk
        if ($request->filled('q')) {
            $searchTerm = trim($request->q);
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        // 2. Filter Kategori
        if ($request->filled('category') && $request->category !== 'Semua') {
            $query->where('category', $request->category);
        }

        // 3. Filter Ukuran
        if ($request->filled('size') && $request->size !== 'Semua') {
            $size = trim($request->size);
            $query->whereHas('variants', function ($vQuery) use ($size) {
                $vQuery->where('ukuran', $size);
            });
        }

        // 4. Urutan / Sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'harga_terendah':
                $query->orderBy('price', 'asc');
                break;
            case 'harga_tertinggi':
                $query->orderBy('price', 'desc');
                break;
            case 'rating_tertinggi':
                $query->orderByDesc('reviews_avg_rating')->latest('id');
                break;
            case 'terlama':
                $query->oldest();
                break;
            case 'terbaru':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Product::select('category')->distinct()->whereNotNull('category')->pluck('category');
        $sizeGuides = SizeGuide::all();

        return view('User.katalog', compact('products', 'categories', 'sizeGuides'));
    }

    /**
     * Tampilkan Halaman Detail Produk & Pemilih Varian
     */
    public function show(Product $product, SizeRecommender $sizeRecommender)
    {
        $product->load(['variants.stocks.location']);

        // 4 Produk Terkait dari kategori yang sama
        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->with(['variants.stocks.location'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->take(4)
            ->get();

        $sizeGuides = SizeGuide::all();

        // Data ulasan & rating produk (FRI-07)
        $averageRating = round($product->reviews()->avg('rating') ?? 0, 1);
        $totalReviews = $product->reviews()->count();

        $ratingDistribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $product->reviews()->where('rating', $star)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            $ratingDistribution[$star] = [
                'count'      => $count,
                'percentage' => $percentage,
            ];
        }

        $reviews = $product->reviews()->with(['user', 'orderItem'])->latest('id')->get();

        // Data bayi & rekomendasi ukuran (FRI-01)
        $user = auth()->user();
        $baby = $user ? $user->baby : null;
        $recommendation = $baby ? $sizeRecommender->recommend($baby) : null;
        $canReview = $user ? $product->canBeReviewedBy($user) : false;
        $userReview = $user ? $product->reviews()->where('user_id', $user->id)->first() : null;

        $variantsData = $product->variants->map(function ($v) {
            return [
                'id'            => $v->id,
                'sku'           => $v->sku,
                'ukuran'        => $v->ukuran,
                'warna'         => $v->warna,
                'nama_varian'   => $v->nama_varian,
                'variant_label' => $v->variant_label,
                'stok_tersedia' => $v->stokTersedia(),
            ];
        });

        return view('User.detail-produk', compact(
            'product', 
            'relatedProducts', 
            'sizeGuides', 
            'variantsData', 
            'baby', 
            'recommendation',
            'averageRating',
            'totalReviews',
            'ratingDistribution',
            'reviews',
            'canReview',
            'userReview'
        ));
    }
}

