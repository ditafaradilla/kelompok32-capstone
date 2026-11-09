<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Status Pembayaran Konstanta
    public const STATUS_BELUM_BAYAR         = 'belum_bayar';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_LUNAS               = 'lunas';
    public const STATUS_DITOLAK             = 'ditolak';

    // Metode Pembayaran Konstanta
    public const METODE_TRANSFER = 'transfer_bank';
    public const METODE_QRIS     = 'qris';

    public const STATUS_LABELS = [
        self::STATUS_BELUM_BAYAR         => 'Belum Bayar',
        self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
        self::STATUS_LUNAS               => 'Lunas',
        self::STATUS_DITOLAK             => 'Ditolak',
    ];

    public const METODE_LABELS = [
        self::METODE_TRANSFER => 'Transfer Bank',
        self::METODE_QRIS     => 'QRIS',
    ];

    protected $fillable = [
        'order_id',
        'metode_pembayaran',
        'jumlah_bayar',
        'status_pembayaran',
        'tanggal_bayar',
        'bukti_bayar',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_bayar'  => 'integer',
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status_pembayaran] ?? $this->status_pembayaran;
    }

    public function getMetodeLabelAttribute(): string
    {
        return self::METODE_LABELS[$this->metode_pembayaran] ?? $this->metode_pembayaran;
    }
}

