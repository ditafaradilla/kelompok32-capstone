<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_bayi',
        'tanggal_lahir',
        'jenis_kelamin',
        'berat_badan',
        'tinggi_badan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'berat_badan'   => 'decimal:2',
            'tinggi_badan'  => 'decimal:1',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor usia_bulan: selisih bulan dari tanggal_lahir sampai hari ini, minimal 0.
     */
    public function getUsiaBulanAttribute(): int
    {
        if (!$this->tanggal_lahir) {
            return 0;
        }

        $birthDate = Carbon::parse($this->tanggal_lahir);
        $now = Carbon::now();

        if ($birthDate->greaterThan($now)) {
            return 0;
        }

        return (int) max(0, $birthDate->diffInMonths($now));
    }
}

