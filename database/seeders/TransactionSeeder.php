<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id');

        if (!$adminId) {
            $this->command?->error(
                'User admin belum ada. Jalankan AdminSeeder terlebih dahulu.'
            );

            return;
        }

        /*
         * DATA DUMMY BABY CORNER
         *
         * Periode:
         * 1 Oktober 2025 sampai tanggal hari ini.
         *
         * Hari penjualan:
         * Senin, Jumat, Sabtu, dan Minggu.
         *
         * Pembelian stok:
         * Sekali setiap minggu, pada hari Rabu.
         *
         * Tidak ada transaksi gaji karyawan.
         */

        $today = Carbon::today();
        $startDate = Carbon::create(2025, 10, 1)->startOfDay();

        $transactions = [];

        $paymentMethods = [
            'Tunai',
            'Transfer Bank',
            'QRIS',
            'E-Wallet',
        ];

        /*
         * Nominal penjualan disesuaikan untuk toko kecil.
         * Penjualan bazar dapat lebih tinggi daripada
         * penjualan online atau transaksi langsung biasa.
         */
        $onlineSalesAmounts = [
            125000, 175000, 225000, 275000,
            325000, 375000, 425000, 475000,
        ];

        $offlineSalesAmounts = [
            150000, 200000, 250000, 300000,
            350000, 400000, 450000, 500000,
        ];

        $bazarSalesAmounts = [
            450000, 550000, 650000, 750000,
            850000, 950000, 1100000, 1250000,
        ];

        $stockAmounts = [
            850000, 950000, 1100000, 1250000,
            1400000, 1550000, 1700000, 1300000,
        ];

        $shippingAmounts = [
            15000, 20000, 25000, 30000,
            35000, 40000, 45000,
        ];

        $bazarCosts = [
            50000, 65000, 75000, 85000,
            100000, 115000, 125000,
        ];

        /*
         * Penghitung minggu dimulai dari tanggal awal
         * agar variasi nominal tetap konsisten.
         */
        $weekNumber = 0;
        $previousWeek = null;

        for (
            $date = $startDate->copy();
            $date->lte($today);
            $date->addDay()
        ) {
            $dayOfWeek = (int) $date->dayOfWeek;

            $currentWeek = $date->copy()
                ->startOfWeek(Carbon::MONDAY)
                ->toDateString();

            if ($currentWeek !== $previousWeek) {
                $weekNumber++;
                $previousWeek = $currentWeek;
            }

            /*
             * 1. PEMBELIAN STOK
             *
             * Satu kali setiap minggu pada hari Rabu.
             * Tidak membuat transaksi gaji karyawan.
             */
            if ($dayOfWeek === Carbon::WEDNESDAY) {
                $stockAmount = $stockAmounts[
                    ($weekNumber - 1) % count($stockAmounts)
                ];

                $transactions[] = [
                    $date->toDateString(),
                    'expense',
                    'Pembelian Stok / Barang',
                    $stockAmount,
                    'lunas',
                    'Transfer Bank',
                    null,
                    'Pembelian stok mingguan pakaian bayi dan perlengkapan bayi dari supplier',
                ];

                /*
                 * Operasional toko hanya dicatat
                 * pada minggu tertentu, bukan setiap minggu.
                 */
                if ($weekNumber % 4 === 1) {
                    $transactions[] = [
                        $date->toDateString(),
                        'expense',
                        'Operasional Toko',
                        50000 + (($weekNumber % 4) * 15000),
                        'lunas',
                        'Tunai',
                        null,
                        'Pembelian perlengkapan packing, plastik, label, dan kebutuhan toko',
                    ];
                }
            }

            /*
             * 2. PENJUALAN
             *
             * Hanya Senin, Jumat, Sabtu, dan Minggu.
             */
            $isSalesDay = in_array(
                $dayOfWeek,
                [
                    Carbon::MONDAY,
                    Carbon::FRIDAY,
                    Carbon::SATURDAY,
                    Carbon::SUNDAY,
                ],
                true
            );

            if (!$isSalesDay) {
                continue;
            }

            /*
             * Bazar diadakan sesekali, sekitar satu
             * atau dua kali dalam sebulan.
             */
            $isBazar = (
                in_array(
                    $dayOfWeek,
                    [Carbon::SATURDAY, Carbon::SUNDAY],
                    true
                )
                && (
                    $weekNumber % 3 === 0
                    || (
                        $weekNumber % 5 === 0
                        && $dayOfWeek === Carbon::SUNDAY
                    )
                )
            );

            $event = null;

            /*
             * Penentuan kategori, nominal, metode pembayaran,
             * dan deskripsi transaksi pemasukan.
             */
            if ($isBazar) {
                $amount = $bazarSalesAmounts[
                    ($weekNumber + $dayOfWeek)
                    % count($bazarSalesAmounts)
                ];

                $category = 'Penjualan Bazar';
                $description =
                    'Penjualan pakaian bayi dan perlengkapan bayi di bazar atau pasar malam lingkungan';

                $method = ($weekNumber % 2 === 0)
                    ? 'Tunai'
                    : 'QRIS';

                $event = 'Pasar Malam Warga';
            } elseif ($dayOfWeek === Carbon::MONDAY) {
                $amount = $onlineSalesAmounts[
                    $weekNumber % count($onlineSalesAmounts)
                ];

                $category = 'Penjualan Online';
                $description =
                    'Pesanan pakaian bayi melalui marketplace atau media sosial';

                $method = $paymentMethods[
                    $weekNumber % count($paymentMethods)
                ];
            } elseif ($dayOfWeek === Carbon::FRIDAY) {
                /*
                 * Jumat bergantian antara penjualan online
                 * dan penjualan langsung.
                 */
                if ($weekNumber % 3 === 0) {
                    $amount = $offlineSalesAmounts[
                        ($weekNumber + 2)
                        % count($offlineSalesAmounts)
                    ];

                    $category = 'Penjualan Offline / Langsung';
                    $description =
                        'Penjualan langsung pakaian bayi kepada pelanggan';

                    $method = ($weekNumber % 2 === 0)
                        ? 'Tunai'
                        : 'QRIS';
                } else {
                    $amount = $onlineSalesAmounts[
                        ($weekNumber + 3)
                        % count($onlineSalesAmounts)
                    ];

                    $category = 'Penjualan Online';
                    $description =
                        'Pesanan online pakaian bayi dari pelanggan';

                    $method = $paymentMethods[
                        ($weekNumber + 1)
                        % count($paymentMethods)
                    ];
                }
            } elseif ($dayOfWeek === Carbon::SATURDAY) {
                $amount = $offlineSalesAmounts[
                    ($weekNumber + 3)
                    % count($offlineSalesAmounts)
                ];

                $category = 'Penjualan Offline / Langsung';
                $description =
                    'Penjualan langsung pakaian bayi pada akhir pekan';

                $method = ($weekNumber % 2 === 0)
                    ? 'Tunai'
                    : 'QRIS';
            } else {
                /*
                 * Minggu yang tidak digunakan untuk bazar
                 * dapat menjadi hari penjualan langsung.
                 */
                if ($weekNumber % 2 === 0) {
                    $amount = $offlineSalesAmounts[
                        ($weekNumber + 4)
                        % count($offlineSalesAmounts)
                    ];

                    $category = 'Penjualan Offline / Langsung';
                    $description =
                        'Penjualan langsung pakaian bayi kepada pelanggan pada hari Minggu';

                    $method = 'Tunai';
                } else {
                    $amount = $onlineSalesAmounts[
                        ($weekNumber + 5)
                        % count($onlineSalesAmounts)
                    ];

                    $category = 'Penjualan Online';
                    $description =
                        'Pesanan online pakaian bayi pada hari Minggu';

                    $method = 'Transfer Bank';
                }
            }

            /*
             * Status lunas dan belum lunas.
             * Sebagian kecil transaksi online disimulasikan
             * belum lunas untuk kebutuhan pengujian fitur.
             */
            $status = (
                $category === 'Penjualan Online'
                && $weekNumber % 13 === 0
            ) ? 'belum_lunas' : 'lunas';

            /*
             * SIMPAN TRANSAKSI PEMASUKAN.
             */
            $transactions[] = [
                $date->toDateString(),
                'income',
                $category,
                $amount,
                $status,
                $method,
                $event,
                $description,
            ];

            /*
             * 3. BIAYA BAZAR
             *
             * Hanya muncul pada tanggal yang sama
             * dengan transaksi penjualan bazar.
             */
            if ($isBazar) {
                $transactions[] = [
                    $date->toDateString(),
                    'expense',
                    'Biaya Bazar (Sewa & Operasional)',
                    $bazarCosts[
                        ($weekNumber + $dayOfWeek)
                        % count($bazarCosts)
                    ],
                    'lunas',
                    'Tunai',
                    'Pasar Malam Warga',
                    'Biaya sewa tempat dan operasional mengikuti bazar atau pasar malam',
                ];
            }

            /*
             * 4. ONGKOS KIRIM
             *
             * Hanya ketika kategori penjualan online.
             * Tidak ada ongkos kirim untuk transaksi bazar
             * atau penjualan langsung.
             */
            if ($category === 'Penjualan Online') {
                $transactions[] = [
                    $date->toDateString(),
                    'expense',
                    'Ongkos Kirim',
                    $shippingAmounts[
                        ($weekNumber + $dayOfWeek)
                        % count($shippingAmounts)
                    ],
                    'lunas',
                    'Tunai',
                    null,
                    'Biaya pengiriman pesanan online kepada pelanggan',
                ];
            }

            /*
             * 5. PEMASARAN DAN IKLAN
             *
             * Sesekali, sekitar dua minggu sekali.
             * Tidak ada biaya iklan setiap hari.
             */
            if (
                $dayOfWeek === Carbon::MONDAY
                && $weekNumber % 2 === 0
            ) {
                $transactions[] = [
                    $date->toDateString(),
                    'expense',
                    'Pemasaran & Iklan',
                    50000 + (($weekNumber % 4) * 25000),
                    'lunas',
                    'E-Wallet',
                    null,
                    'Promosi produk melalui media sosial atau marketplace',
                ];
            }
        }

        /*
         * 6. URUTKAN BERDASARKAN TANGGAL.
         */
        usort(
            $transactions,
            static fn (array $a, array $b): int =>
                strcmp($a[0], $b[0])
        );

        /*
         * 7. SIMPAN ATAU PERBARUI DATA DUMMY.
         *
         * Referensi DMY- digunakan untuk transaksi simulasi.
         * Transaksi non-dummy tidak diubah.
         */
        foreach (
            $transactions as $index => [
                $transactionDate,
                $type,
                $category,
                $amount,
                $status,
                $method,
                $event,
                $description,
            ]
        ) {
            $reference = 'DMY-' . str_pad(
                (string) ($index + 1),
                3,
                '0',
                STR_PAD_LEFT
            );

            $transaction = Transaction::withTrashed()
                ->firstOrNew([
                    'reference' => $reference,
                ]);

            if (
                $transaction->exists
                && method_exists($transaction, 'trashed')
                && $transaction->trashed()
            ) {
                $transaction->restore();
            }

            $transaction->fill([
                'transaction_date' => $transactionDate,
                'type'             => $type,
                'category'         => $category,
                'amount'           => $amount,
                'payment_status'   => $status,
                'payment_method'   => $method,
                'event_name'       => $event,
                'description'      => $description,
                'created_by'       => $adminId,
            ]);

            $transaction->save();
        }

        $this->command?->info(
            'Seeder selesai. Data dummy Baby Corner dari '
            . $startDate->toDateString()
            . ' sampai '
            . $today->toDateString()
            . ' telah diperbarui.'
        );
    }
}