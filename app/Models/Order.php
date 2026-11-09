<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Status Pesanan Konstanta
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_DIPROSES             = 'diproses';
    public const STATUS_DIKIRIM              = 'dikirim';
    public const STATUS_SELESAI              = 'selesai';
    public const STATUS_DIBATALKAN           = 'dibatalkan';

    public const STATUS_LABELS = [
        self::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
        self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
        self::STATUS_DIPROSES             => 'Diproses',
        self::STATUS_DIKIRIM              => 'Dikirim',
        self::STATUS_SELESAI              => 'Selesai',
        self::STATUS_DIBATALKAN           => 'Dibatalkan',
    ];

    protected $fillable = [
        'user_id',
        'nomor_pesanan',
        'tanggal_pesanan',
        'total_harga',
        'alamat_pengiriman',
        'nama_penerima',
        'telepon_penerima',
        'catatan',
        'ongkir',
        'status_pesanan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pesanan' => 'datetime',
            'total_harga'     => 'integer',
            'ongkir'          => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status_pesanan] ?? $this->status_pesanan;
    }

    public function canBeCancelled(): bool
    {
        return $this->status_pesanan === self::STATUS_MENUNGGU_PEMBAYARAN;
    }

    /**
     * Buat nomor pesanan unik berformat BC-YYYYMMDD-XXXX secara urut harian.
     */
    public static function generateNomorPesanan(): string
    {
        $datePart = date('Ymd');
        $prefix = "BC-{$datePart}-";
        $todayCount = self::whereDate('tanggal_pesanan', date('Y-m-d'))->count();
        $nextSeq = $todayCount + 1;

        do {
            $nomor = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $nextSeq++;
        } while (self::where('nomor_pesanan', $nomor)->exists());

        return $nomor;
    }
}

