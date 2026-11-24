<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Aturan transisi status yang sah (State Machine FRI-05).
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_MENUNGGU_PEMBAYARAN => [
            self::STATUS_MENUNGGU_VERIFIKASI,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_MENUNGGU_VERIFIKASI => [
            self::STATUS_DIPROSES,
            self::STATUS_MENUNGGU_PEMBAYARAN,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_DIPROSES => [
            self::STATUS_DIKIRIM,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_DIKIRIM => [
            self::STATUS_SELESAI,
        ],
        self::STATUS_SELESAI => [],
        self::STATUS_DIBATALKAN => [],
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
        'kurir',
        'nomor_resi',
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

    public function histories()
    {
        return $this->hasMany(OrderStatusHistory::class)->oldest();
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
     * Dapatkan daftar status berikutnya yang sah dari status saat ini.
     */
    public function getNextAllowedStatuses(): array
    {
        return self::ALLOWED_TRANSITIONS[$this->status_pesanan] ?? [];
    }

    /**
     * Catat riwayat awal saat pesanan pertama kali dibuat.
     */
    public function recordInitialHistory(?string $catatan = null, ?int $userId = null): OrderStatusHistory
    {
        return $this->histories()->create([
            'status'     => $this->status_pesanan,
            'catatan'    => $catatan ?? 'Pesanan berhasil dibuat, menunggu pembayaran pelanggan.',
            'changed_by' => $userId ?? Auth::id() ?? $this->user_id,
        ]);
    }

    /**
     * Kembalikan stok item pesanan ke lokasi bertipe toko.
     */
    public function restoreStock(): void
    {
        $this->loadMissing('items');

        foreach ($this->items as $item) {
            $stockRecord = ProductStock::where('product_variant_id', $item->product_variant_id)
                ->whereHas('location', fn($q) => $q->where('type', 'toko'))
                ->orderByDesc('quantity')
                ->lockForUpdate()
                ->first();

            if ($stockRecord) {
                $stockRecord->quantity += $item->jumlah;
                $stockRecord->save();
            } else {
                $storeLocation = Location::where('type', 'toko')->first();
                if ($storeLocation) {
                    ProductStock::create([
                        'product_variant_id' => $item->product_variant_id,
                        'location_id'        => $storeLocation->id,
                        'quantity'           => $item->jumlah,
                    ]);
                }
            }
        }
    }

    /**
     * Ubah status pesanan secara terpusat dengan validasi transisi,
     * pengembalian stok jika dibatalkan, dan pencatatan riwayat (FRI-05).
     */
    public function changeStatus(
        string $status,
        ?string $catatan = null,
        ?int $userId = null,
        ?string $kurir = null,
        ?string $nomorResi = null
    ): void {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status_pesanan] ?? [];
        if (!in_array($status, $allowed, true)) {
            $currentLabel = $this->status_label;
            $targetLabel = self::STATUS_LABELS[$status] ?? $status;
            throw new \DomainException("Perubahan status dari '{$currentLabel}' ke '{$targetLabel}' tidak diizinkan.");
        }

        $userId = $userId ?? Auth::id();

        DB::transaction(function () use ($status, $catatan, $userId, $kurir, $nomorResi) {
            if ($status === self::STATUS_DIBATALKAN) {
                $this->restoreStock();

                if ($this->payment) {
                    $this->payment->status_pembayaran = Payment::STATUS_DITOLAK;
                    $this->payment->save();
                }
            }

            if ($kurir !== null) {
                $this->kurir = $kurir;
            }
            if ($nomorResi !== null) {
                $this->nomor_resi = $nomorResi;
            }

            $this->status_pesanan = $status;
            $this->save();

            $this->histories()->create([
                'status'     => $status,
                'catatan'    => $catatan,
                'changed_by' => $userId,
            ]);
        });
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
