<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Tambahkan varian produk ke keranjang belanja pengguna.
     * Jika varian sudah ada di keranjang, jumlah ditambahkan.
     * Total jumlah tidak boleh melebihi stok tersedia di toko.
     *
     * @param User|int $user
     * @param int $variantId
     * @param int $qty
     * @return CartItem
     * @throws ValidationException
     */
    public function add($user, int $variantId, int $qty = 1): CartItem
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        if ($qty < 1) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah produk minimal 1.',
            ]);
        }

        $variant = ProductVariant::with(['stocks.location', 'product'])->findOrFail($variantId);
        $availableStock = $variant->stokTersedia();

        if ($availableStock <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Stok produk habis.',
            ]);
        }

        $existingItem = CartItem::where('user_id', $userId)
            ->where('product_variant_id', $variantId)
            ->first();

        $newQty = ($existingItem ? $existingItem->jumlah : 0) + $qty;

        if ($newQty > $availableStock) {
            throw ValidationException::withMessages([
                'jumlah' => "Stok hanya tersisa {$availableStock}.",
            ]);
        }

        if ($existingItem) {
            $existingItem->jumlah = $newQty;
            $existingItem->save();
            return $existingItem;
        }

        return CartItem::create([
            'user_id'            => $userId,
            'product_variant_id' => $variantId,
            'jumlah'             => $newQty,
        ]);
    }

    /**
     * Ubah jumlah pesanan varian di keranjang belanja.
     *
     * @param User|int $user
     * @param int $cartItemId
     * @param int $qty
     * @return CartItem
     * @throws ValidationException
     */
    public function updateQty($user, int $cartItemId, int $qty): CartItem
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        $cartItem = CartItem::with(['variant.stocks.location', 'variant.product'])->findOrFail($cartItemId);

        if ($cartItem->user_id !== $userId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah item keranjang ini.');
        }

        if ($qty < 1) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah produk minimal 1.',
            ]);
        }

        $availableStock = $cartItem->variant->stokTersedia();

        if ($qty > $availableStock) {
            throw ValidationException::withMessages([
                'jumlah' => "Stok hanya tersisa {$availableStock}.",
            ]);
        }

        $cartItem->jumlah = $qty;
        $cartItem->save();

        return $cartItem;
    }

    /**
     * Hapus satu item dari keranjang belanja.
     *
     * @param User|int $user
     * @param int $cartItemId
     * @return void
     */
    public function remove($user, int $cartItemId): void
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        $cartItem = CartItem::findOrFail($cartItemId);

        if ($cartItem->user_id !== $userId) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus item keranjang ini.');
        }

        $cartItem->delete();
    }

    /**
     * Kosongkan seluruh isi keranjang belanja pengguna.
     *
     * @param User|int $user
     * @return void
     */
    public function clear($user): void
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        CartItem::where('user_id', $userId)->delete();
    }

    /**
     * Ambil ringkasan keranjang belanja pengguna:
     * daftar item, subtotal per item, total, jumlah item, serta daftar masalah stok.
     *
     * @param User|int $user
     * @return array
     */
    public function summary($user): array
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        $items = CartItem::with(['variant.stocks.location', 'variant.product'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $stockIssues = [];
        $totalPrice = 0;
        $totalQty = 0;

        $processedItems = $items->map(function ($item) use (&$stockIssues, &$totalPrice, &$totalQty) {
            $variant = $item->variant;
            $product = $variant?->product;
            $unitPrice = (float) ($product?->price ?? 0);
            $subtotal = $unitPrice * $item->jumlah;
            $availableStock = $variant ? $variant->stokTersedia() : 0;
            $hasIssue = $item->jumlah > $availableStock;

            if ($hasIssue) {
                $stockIssues[] = [
                    'cart_item_id'    => $item->id,
                    'product_name'    => $product?->name ?? 'Produk',
                    'variant_label'   => $variant ? "{$variant->ukuran} / {$variant->warna}" : '-',
                    'requested_qty'   => $item->jumlah,
                    'available_stock' => $availableStock,
                    'message'         => $availableStock > 0 
                        ? "Stok hanya tersisa {$availableStock}."
                        : "Stok produk habis.",
                ];
            }

            $totalPrice += $subtotal;
            $totalQty += $item->jumlah;

            $item->unit_price = $unitPrice;
            $item->subtotal = $subtotal;
            $item->available_stock = $availableStock;
            $item->has_stock_issue = $hasIssue;

            return $item;
        });

        return [
            'items'           => $processedItems,
            'subtotal'        => $totalPrice,
            'total'           => $totalPrice,
            'total_price'     => $totalPrice,
            'jumlah_item'     => $totalQty,
            'total_items'     => $totalQty,
            'item_count'      => $processedItems->count(),
            'has_stock_issue' => count($stockIssues) > 0,
            'stock_issues'    => $stockIssues,
            'masalah_stok'    => $stockIssues,
        ];
    }
}
