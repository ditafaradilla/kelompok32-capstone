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
        'variants',
        'size',
        'color',
        'price',
        'cost_price',
        'min_stock',
        'description',
        'photo',
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'product_stocks')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Hitung total stok dari seluruh lokasi (toko & gudang)
     */
    public function getTotalStockAttribute()
    {
        return (int) $this->stocks()->sum('quantity');
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
