<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_id',
        'jumlah',
        'harga_satuan',
        'nama_produk',
        'varian_label',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'       => 'integer',
            'harga_satuan' => 'integer',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getSubtotalAttribute(): int
    {
        return $this->jumlah * $this->harga_satuan;
    }
}

