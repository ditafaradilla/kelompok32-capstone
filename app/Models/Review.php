<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_item_id',
        'rating',
        'ulasan',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Nama pelanggan yang disamarkan demi privasi (misal: "Bunda Sarah" -> "Bunda S.", "Sarah Wijaya" -> "Sarah W.").
     */
    public function getMaskedUserNameAttribute(): string
    {
        $name = trim($this->user?->name ?? 'Pelanggan');
        $words = preg_split('/\s+/', $name);

        if (count($words) >= 2) {
            $lastWord = end($words);
            $initial = strtoupper(mb_substr($lastWord, 0, 1));
            return $words[0] . ' ' . $initial . '.';
        }

        return $name;
    }
}

