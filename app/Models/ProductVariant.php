<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'ukuran',
        'warna',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'product_variant_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_variant_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_variant_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'product_stocks', 'product_variant_id', 'location_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Accessor stok_total dari semua lokasi
     */
    public function getStokTotalAttribute(): int
    {
        return (int) $this->stocks()->sum('quantity');
    }

    /**
     * Mengembalikan total stok di lokasi bertipe 'toko'
     * Ini definisi stok yang bisa dibeli pelanggan online.
     */
    public function stokTersedia(): int
    {
        return (int) $this->stocks()
            ->whereHas('location', function ($query) {
                $query->where('type', 'toko');
            })
            ->sum('quantity');
    }

    /**
     * Label representasi varian (contoh: "0-3M / Sage Green")
     */
    public function getNamaVarianAttribute(): string
    {
        $parts = array_filter([$this->ukuran, $this->warna]);
        return !empty($parts) ? implode(' / ', $parts) : ($this->ukuran ?? $this->warna ?? '-');
    }

    /**
     * Alias variant_label untuk accessor nama_varian
     */
    public function getVariantLabelAttribute(): string
    {
        return $this->nama_varian;
    }

    /**
     * Label lengkap: "Nama Produk — Ukuran/Warna"
     */
    public function getNamaLengkapAttribute(): string
    {
        $productName = $this->product->name ?? 'Produk';
        $varian = $this->nama_varian;
        return ($varian !== '-') ? "{$productName} — {$varian}" : $productName;
    }
}

