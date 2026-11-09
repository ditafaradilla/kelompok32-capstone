<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'location_id',
        'quantity',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Kompatibilitas mundur: akses produk via varian
     */
    public function getProductAttribute()
    {
        return $this->variant?->product;
    }
}
