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
