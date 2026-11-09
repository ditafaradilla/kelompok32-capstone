<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'ukuran',
        'usia_min_bulan',
        'usia_maks_bulan',
        'berat_min',
        'berat_maks',
        'tinggi_min',
        'tinggi_maks',
    ];

    protected function casts(): array
    {
        return [
            'usia_min_bulan'  => 'integer',
            'usia_maks_bulan' => 'integer',
            'berat_min'       => 'decimal:2',
            'berat_maks'      => 'decimal:2',
            'tinggi_min'      => 'decimal:1',
            'tinggi_maks'     => 'decimal:1',
        ];
    }
}

