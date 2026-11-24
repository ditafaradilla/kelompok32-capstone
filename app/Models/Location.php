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

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_stocks')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'target_location_id');
    }
}
