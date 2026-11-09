<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', // 'toko' atau 'gudang'
        'address',
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_stocks', 'location_id', 'product_variant_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'target_location_id');
    }
}
