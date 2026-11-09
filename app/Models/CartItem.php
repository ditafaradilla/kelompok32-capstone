<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_variant_id',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariant::class,
            'id',                 // Foreign key on product_variants table...
            'id',                 // Foreign key on products table...
            'product_variant_id', // Local key on carts table...
            'product_id'          // Local key on product_variants table...
        );
    }

    /**
     * Hitung subtotal dinamis berdasarkan harga terkini produk.
     */
    public function getSubtotalAttribute(): float
    {
        $price = (float) ($this->variant?->product?->price ?? 0);
        return $price * $this->jumlah;
    }
}
