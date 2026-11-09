<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Tampilkan halaman keranjang belanja pengguna.
     */
    public function index(CartService $cartService)
    {
        $summary = $cartService->summary(Auth::user());

        return view('User.keranjang', compact('summary'));
    }

    /**
     * Tambahkan varian produk ke keranjang belanja.
     */
    public function store(Request $request, CartService $cartService)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'jumlah'             => 'nullable|integer|min:1',
        ], [
            'product_variant_id.required' => 'Pilih varian ukuran dan warna produk terlebih dahulu.',
            'product_variant_id.exists'   => 'Varian produk yang dipilih tidak valid.',
            'jumlah.integer'              => 'Jumlah produk harus berupa angka bulat.',
            'jumlah.min'                  => 'Jumlah produk minimal 1.',
        ]);

        $qty = (int) ($validated['jumlah'] ?? 1);
        $cartItem = $cartService->add(Auth::user(), (int) $validated['product_variant_id'], $qty);
        $product = $cartItem->variant?->product;

        return back()
            ->with('cart_success', [
                'product_name' => $product?->name ?? 'Produk',
                'variant'      => ($cartItem->variant?->ukuran ?? '') . ' / ' . ($cartItem->variant?->warna ?? ''),
                'jumlah'       => $qty,
            ])
            ->with('success', 'Produk berhasil ditambahkan ke keranjang belanja!');
    }

    /**
     * Ubah jumlah pesanan varian di keranjang.
     */
    public function update(Request $request, CartItem $item, CartService $cartService)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah item keranjang ini.');
        }

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1',
        ], [
            'jumlah.required' => 'Jumlah produk wajib diisi.',
            'jumlah.integer'  => 'Jumlah produk harus berupa angka bulat.',
            'jumlah.min'      => 'Jumlah produk minimal 1.',
        ]);

        $cartService->updateQty(Auth::user(), $item->id, (int) $validated['jumlah']);

        return back()->with('success', 'Jumlah produk di keranjang berhasil diperbarui.');
    }

    /**
     * Hapus satu item dari keranjang belanja.
     */
    public function destroy(CartItem $item, CartService $cartService)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus item keranjang ini.');
        }

        $cartService->remove(Auth::user(), $item->id);

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Kosongkan seluruh keranjang belanja pengguna.
     */
    public function clear(CartService $cartService)
    {
        $cartService->clear(Auth::user());

        return back()->with('success', 'Keranjang belanja berhasil dikosongkan.');
    }
}

