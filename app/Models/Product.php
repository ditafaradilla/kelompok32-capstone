<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'size',
        'color',
        'price',
        'cost_price',
        'min_stock',
        'description',
        'photo',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stocks()
    {
        return $this->hasManyThrough(ProductStock::class, ProductVariant::class, 'product_id', 'product_variant_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Memeriksa apakah pengguna memiliki pesanan berstatus 'selesai' untuk produk ini (FRI-07).
     */
    public function canBeReviewedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return OrderItem::where('product_id', $this->id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status_pesanan', Order::STATUS_SELESAI);
            })
            ->exists();
    }

    /**
     * Mengambil item pesanan selesai terakhir milik pengguna untuk produk ini (FRI-07).
     */
    public function getCompletedOrderItemFor(?User $user): ?OrderItem
    {
        if (!$user) {
            return null;
        }

        return OrderItem::where('product_id', $this->id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status_pesanan', Order::STATUS_SELESAI);
            })
            ->latest('id')
            ->first();
    }

    /**
     * Hitung total stok dari seluruh varian dan lokasi (toko & gudang)
     */
    public function getTotalStockAttribute()
    {
        if ($this->relationLoaded('variants') && $this->variants->every(fn ($v) => $v->relationLoaded('stocks'))) {
            return (int) $this->variants->sum(fn ($v) => $v->stocks->sum('quantity'));
        }

        return (int) ProductStock::whereIn(
            'product_variant_id',
            $this->variants()->pluck('id')
        )->sum('quantity');
    }

    /**
     * Cek apakah stok habis (0)
     */
    public function getIsOutOfStockAttribute()
    {
        return $this->total_stock <= 0;
    }

    /**
     * Cek apakah stok menipis (<= batas minimum)
     */
    public function getIsLowStockAttribute()
    {
        return $this->total_stock > 0 && $this->total_stock <= $this->min_stock;
    }
}
