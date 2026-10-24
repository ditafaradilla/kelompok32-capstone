<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    public const TYPE_INCOME  = 'income';
    public const TYPE_EXPENSE = 'expense';

    public const STATUS_PAID   = 'lunas';
    public const STATUS_UNPAID = 'belum_lunas';

    public const INCOME_CATEGORIES = [
        'Penjualan Online',
        'Penjualan Bazar',
        'Penjualan Offline / Langsung',
        'Pemasukan Lainnya',
    ];

    public const EXPENSE_CATEGORIES = [
        'Pembelian Stok / Barang',
        'Ongkos Kirim',
        'Biaya Bazar (Sewa & Operasional)',
        'Gaji & Upah',
        'Pemasaran & Iklan',
        'Operasional Toko',
        'Pengeluaran Lainnya',
    ];

    public const PAYMENT_METHODS = ['Tunai', 'Transfer Bank', 'QRIS', 'E-Wallet', 'Lainnya'];

    protected $fillable = [
        'transaction_date',
        'type',
        'category',
        'amount',
        'payment_status',
        'payment_method',
        'reference',
        'event_name',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        // transaction_date sengaja TIDAK di-cast supaya tersimpan murni 'Y-m-d'
        // (cast date bisa menambahkan jam di SQLite dan merusak filter tanggal).
        return [
            'amount' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Daftar kategori sesuai jenis transaksi. */
    public static function categoriesFor(string $type): array
    {
        return match ($type) {
            self::TYPE_INCOME  => self::INCOME_CATEGORIES,
            self::TYPE_EXPENSE => self::EXPENSE_CATEGORIES,
            default            => [],
        };
    }

    /**
     * SATU-SATUNYA tempat menghitung ringkasan keuangan (dipakai halaman pembukuan & laporan).
     * - income / expense : hanya transaksi berstatus LUNAS (uang benar-benar masuk/keluar)
     * - receivable       : pemasukan BELUM LUNAS (piutang)
     * - payable          : pengeluaran BELUM LUNAS (utang)
     * - balance          : income - expense
     */
    public static function summarize(Builder $query): array
    {
        $row = (clone $query)->toBase()->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'income'  AND payment_status = 'lunas'       THEN amount ELSE 0 END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' AND payment_status = 'lunas'       THEN amount ELSE 0 END), 0) AS expense,
            COALESCE(SUM(CASE WHEN type = 'income'  AND payment_status = 'belum_lunas' THEN amount ELSE 0 END), 0) AS receivable,
            COALESCE(SUM(CASE WHEN type = 'expense' AND payment_status = 'belum_lunas' THEN amount ELSE 0 END), 0) AS payable
        ")->first();

        $income  = (int) $row->income;
        $expense = (int) $row->expense;

        return [
            'income'     => $income,
            'expense'    => $expense,
            'balance'    => $income - $expense,
            'receivable' => (int) $row->receivable,
            'payable'    => (int) $row->payable,
        ];
    }

    public static function rupiah(int|float|null $number): string
    {
        $number = (int) $number;

        return ($number < 0 ? '-' : '') . 'Rp ' . number_format(abs($number), 0, ',', '.');
    }
}
