<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Tampilkan form untuk membuat ulasan produk (FRI-07).
     */
    public function create(Product $product)
    {
        $user = Auth::user();

        // 1. Validasi hak akses: hanya pelanggan dengan pesanan 'selesai' yang boleh mengulas
        if (!$product->canBeReviewedBy($user)) {
            abort(403, 'Anda hanya dapat memberikan ulasan untuk produk yang sudah Anda beli dan pesanannya telah selesai.');
        }

        // 2. Satu pelanggan satu ulasan: jika sudah ada, arahkan ke form edit
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return redirect()
                ->route('ulasan.edit', $existingReview)
                ->with('info', 'Anda sudah memberikan ulasan untuk produk ini. Anda dapat memperbaruinya di sini.');
        }

        $orderItem = $product->getCompletedOrderItemFor($user);

        return view('User.ulasan.form', [
            'product'   => $product,
            'orderItem' => $orderItem,
            'review'    => null,
            'isEdit'    => false,
        ]);
    }

    /**
     * Simpan ulasan baru produk (FRI-07).
     */
    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        // 1. Validasi hak akses di server
        if (!$product->canBeReviewedBy($user)) {
            abort(403, 'Anda hanya dapat memberikan ulasan untuk produk yang sudah Anda beli dan pesanannya telah selesai.');
        }

        // 2. Tolak atau arahkan jika ulasan sudah pernah dibuat
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return redirect()
                ->route('ulasan.edit', $existingReview)
                ->with('error', 'Anda sudah pernah memberikan ulasan untuk produk ini. Silakan ubah ulasan yang sudah ada.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Rating bintang 1-5 wajib dipilih.',
            'rating.integer'  => 'Nilai rating tidak valid.',
            'rating.min'      => 'Rating minimal adalah 1 bintang.',
            'rating.max'      => 'Rating maksimal adalah 5 bintang.',
            'ulasan.max'      => 'Ulasan maksimal 1000 karakter.',
        ]);

        $orderItem = $product->getCompletedOrderItemFor($user);

        Review::create([
            'user_id'       => $user->id,
            'product_id'    => $product->id,
            'order_item_id' => $orderItem?->id,
            'rating'        => $validated['rating'],
            'ulasan'        => $validated['ulasan'] ?? null,
        ]);

        return redirect()
            ->route('produk.show', $product)
            ->with('success', 'Terima kasih! Ulasan dan penilaian Anda berhasil disimpan.');
    }

    /**
     * Tampilkan form untuk mengubah ulasan yang sudah ada (FRI-07).
     */
    public function edit(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah ulasan ini.');
        }

        $product = $review->product;
        $orderItem = $review->orderItem ?: $product->getCompletedOrderItemFor(Auth::user());

        return view('User.ulasan.form', [
            'product'   => $product,
            'orderItem' => $orderItem,
            'review'    => $review,
            'isEdit'    => true,
        ]);
    }

    /**
     * Perbarui ulasan produk milik sendiri (FRI-07).
     */
    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah ulasan ini.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Rating bintang 1-5 wajib dipilih.',
            'rating.integer'  => 'Nilai rating tidak valid.',
            'rating.min'      => 'Rating minimal adalah 1 bintang.',
            'rating.max'      => 'Rating maksimal adalah 5 bintang.',
            'ulasan.max'      => 'Ulasan maksimal 1000 karakter.',
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'ulasan' => $validated['ulasan'] ?? null,
        ]);

        return redirect()
            ->route('produk.show', $review->product_id)
            ->with('success', 'Ulasan dan penilaian Anda berhasil diperbarui.');
    }

    /**
     * Hapus ulasan produk milik sendiri (FRI-07).
     */
    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus ulasan ini.');
        }

        $productId = $review->product_id;
        $review->delete();

        return back()->with('success', 'Ulasan Anda berhasil dihapus.');
    }
}

