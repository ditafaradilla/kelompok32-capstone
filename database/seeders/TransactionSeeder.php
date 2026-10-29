<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id');

        // [hari_lalu, jenis, kategori, nominal, status, metode, nama_bazar, keterangan]
        $rows = [
            [1, 'income', 'Penjualan Online', 1250000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 5 set jumper bayi katun newborn'],
            [2, 'income', 'Penjualan Online', 865000, 'lunas', 'QRIS', null, 'Pesanan online: 10 setelan piyama bayi motif'],
            [2, 'expense', 'Pembelian Stok / Barang', 6500000, 'lunas', 'Transfer Bank', null, 'Restok 100 setelan bayi dari supplier konveksi'],
            [3, 'income', 'Penjualan Online', 2340000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 2 lusin romper bayi 0-6 bulan'],
            [4, 'income', 'Penjualan Offline / Langsung', 4800000, 'lunas', 'Transfer Bank', null, 'Pesanan grosir reseller: 8 lusin setelan bayi campur'],
            [5, 'expense', 'Ongkos Kirim', 485000, 'lunas', 'Tunai', null, 'Ongkir paket pesanan online pekan ini'],
            [6, 'income', 'Penjualan Online', 675000, 'lunas', 'E-Wallet', null, 'Pesanan online: 3 set baju bayi + topi'],
            [7, 'income', 'Penjualan Offline / Langsung', 3600000, 'belum_lunas', 'Transfer Bank', null, 'Grosir reseller, pembayaran tempo 14 hari'],
            [8, 'expense', 'Pembelian Stok / Barang', 4200000, 'belum_lunas', 'Transfer Bank', null, 'Restok romper dan jumper dari supplier, tempo 30 hari'],
            [9, 'income', 'Penjualan Online', 1580000, 'lunas', 'QRIS', null, 'Pesanan online: 7 setelan bayi lengan panjang'],
            [10, 'expense', 'Operasional Toko', 180000, 'lunas', 'Transfer Bank', null, 'Pembelian perlengkapan operasional toko'],
            [11, 'income', 'Penjualan Online', 990000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 4 set baju bayi perempuan'],
            [12, 'expense', 'Pemasaran & Iklan', 750000, 'lunas', 'E-Wallet', null, 'Iklan Instagram dan marketplace'],
            [14, 'income', 'Penjualan Online', 1760000, 'lunas', 'QRIS', null, 'Pesanan online: 8 setelan bayi katun'],
            [15, 'expense', 'Operasional Toko', 420000, 'lunas', 'Tunai', null, 'Plastik packing, lakban, dan label'],
            [17, 'income', 'Penjualan Online', 2125000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 1 lusin jumper + 6 setelan'],
            [19, 'expense', 'Operasional Toko', 350000, 'lunas', 'Transfer Bank', null, 'Listrik dan internet toko'],
            [20, 'income', 'Penjualan Offline / Langsung', 2900000, 'lunas', 'Tunai', null, 'Pembelian langsung di gudang: 5 lusin setelan bayi'],
            [21, 'expense', 'Ongkos Kirim', 390000, 'lunas', 'Tunai', null, 'Ongkir paket pesanan online'],
            [22, 'income', 'Pemasukan Lainnya', 150000, 'lunas', 'Tunai', null, 'Penjualan kardus bekas'],
            [26, 'expense', 'Pembelian Stok / Barang', 7800000, 'lunas', 'Transfer Bank', null, 'Restok setelan bayi dan sarung tangan kaki dari supplier'],
            [27, 'income', 'Penjualan Online', 1430000, 'lunas', 'QRIS', null, 'Pesanan online: 6 setelan bayi motif'],
            [28, 'income', 'Penjualan Offline / Langsung', 5400000, 'lunas', 'Transfer Bank', null, 'Pesanan grosir reseller: 9 lusin setelan bayi'],
            [29, 'expense', 'Ongkos Kirim', 520000, 'lunas', 'Tunai', null, 'Ongkir paket pesanan online'],
            [30, 'income', 'Penjualan Online', 2210000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 2 lusin romper bayi'],
            [33, 'income', 'Penjualan Online', 980000, 'lunas', 'E-Wallet', null, 'Pesanan online: 4 jumper bayi katun'],
            [35, 'expense', 'Operasional Toko', 160000, 'lunas', 'Transfer Bank', null, 'Pembelian perlengkapan dan kebutuhan toko'],
            [36, 'income', 'Penjualan Online', 1875000, 'lunas', 'QRIS', null, 'Pesanan online: 8 setelan bayi lengan pendek'],
            [37, 'expense', 'Pemasaran & Iklan', 900000, 'lunas', 'E-Wallet', null, 'Iklan Instagram dan marketplace'],
            [38, 'income', 'Penjualan Online', 2640000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 3 lusin piyama bayi'],
            [40, 'income', 'Penjualan Bazar', 5200000, 'lunas', 'Tunai', 'Bazar Bunda & Baby Fair', 'Penjualan hari pertama bazar'],
            [41, 'income', 'Penjualan Bazar', 3850000, 'lunas', 'QRIS', 'Bazar Bunda & Baby Fair', 'Penjualan hari kedua bazar'],
            [41, 'expense', 'Biaya Bazar (Sewa & Operasional)', 350000, 'lunas', 'Tunai', 'Bazar Bunda & Baby Fair', 'Transport dan konsumsi bazar'],
            [44, 'expense', 'Biaya Bazar (Sewa & Operasional)', 1500000, 'lunas', 'Transfer Bank', 'Bazar Bunda & Baby Fair', 'Sewa booth bazar 2 hari'],
            [45, 'expense', 'Operasional Toko', 410000, 'lunas', 'Transfer Bank', null, 'Listrik dan internet toko'],
            [47, 'income', 'Penjualan Online', 1120000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 5 setelan bayi'],
            [49, 'expense', 'Ongkos Kirim', 460000, 'lunas', 'Tunai', null, 'Ongkir paket pesanan online'],
            [50, 'income', 'Penjualan Online', 1690000, 'lunas', 'QRIS', null, 'Pesanan online: 7 romper bayi'],
            [52, 'income', 'Penjualan Offline / Langsung', 3100000, 'lunas', 'Transfer Bank', null, 'Pesanan grosir reseller: 5 lusin setelan bayi'],
            [53, 'expense', 'Pembelian Stok / Barang', 3600000, 'lunas', 'Transfer Bank', null, 'Restok baju bayi newborn dari supplier'],
            [57, 'expense', 'Pembelian Stok / Barang', 6200000, 'lunas', 'Transfer Bank', null, 'Restok setelan bayi dari supplier konveksi'],
            [58, 'income', 'Penjualan Online', 1320000, 'lunas', 'QRIS', null, 'Pesanan online: 6 setelan bayi'],
            [60, 'income', 'Penjualan Offline / Langsung', 4500000, 'lunas', 'Transfer Bank', null, 'Pesanan grosir reseller: 8 lusin setelan bayi'],
            [62, 'income', 'Penjualan Online', 1980000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 9 jumper bayi'],
            [64, 'expense', 'Operasional Toko', 175000, 'lunas', 'Transfer Bank', null, 'Biaya kebutuhan operasional toko'],
            [66, 'income', 'Penjualan Online', 2450000, 'lunas', 'E-Wallet', null, 'Pesanan online: 2 lusin setelan bayi'],
            [67, 'expense', 'Pemasaran & Iklan', 650000, 'lunas', 'E-Wallet', null, 'Iklan Instagram'],
            [68, 'income', 'Penjualan Bazar', 3200000, 'lunas', 'Tunai', 'Bazar Akhir Pekan Sekolah Bunda', 'Penjualan bazar sekolah'],
            [69, 'expense', 'Biaya Bazar (Sewa & Operasional)', 1000000, 'lunas', 'Transfer Bank', 'Bazar Akhir Pekan Sekolah Bunda', 'Sewa booth bazar sekolah'],
            [70, 'expense', 'Ongkos Kirim', 430000, 'lunas', 'Tunai', null, 'Ongkir paket pesanan online'],
            [71, 'income', 'Penjualan Online', 1100000, 'lunas', 'QRIS', null, 'Pesanan online: 5 romper bayi'],
            [73, 'income', 'Penjualan Offline / Langsung', 2800000, 'lunas', 'Transfer Bank', null, 'Pesanan grosir reseller: 5 lusin setelan bayi'],
            [75, 'expense', 'Operasional Toko', 380000, 'lunas', 'Tunai', null, 'Plastik packing dan listrik'],
            [76, 'income', 'Penjualan Online', 1560000, 'lunas', 'Transfer Bank', null, 'Pesanan online: 7 setelan bayi'],
            [79, 'expense', 'Pembelian Stok / Barang', 5100000, 'lunas', 'Transfer Bank', null, 'Restok jumper dan romper dari supplier'],
        ];

        foreach ($rows as $i => [$daysAgo, $type, $category, $amount, $status, $method, $event, $description]) {
            $reference = 'DMY-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

            if (Transaction::withTrashed()->where('reference', $reference)->exists()) {
                continue;
            }

            Transaction::create([
                'transaction_date' => now()->subDays($daysAgo)->toDateString(),
                'type'             => $type,
                'category'         => $category,
                'amount'           => $amount,
                'payment_status'   => $status,
                'payment_method'   => $method,
                'reference'        => $reference,
                'event_name'       => $event,
                'description'      => $description,
                'created_by'       => $adminId,
            ]);
        }

        // Data dummy tambahan Baby Corner.
        $additionalRows = [
            [3, 'income', 'Penjualan Online', 850000, 'lunas', 'QRIS', null, 'Pesanan baju bayi melalui marketplace'],
            [5, 'expense', 'Pembelian Stok / Barang', 1850000, 'lunas', 'Transfer Bank', null, 'Restok setelan bayi dari supplier'],
            [7, 'income', 'Penjualan Offline / Langsung', 650000, 'lunas', 'Tunai', null, 'Penjualan langsung kepada pelanggan'],
            [9, 'expense', 'Ongkos Kirim', 125000, 'lunas', 'Tunai', null, 'Biaya pengiriman pesanan pelanggan'],
            [11, 'income', 'Penjualan Online', 1125000, 'lunas', 'Transfer Bank', null, 'Pesanan grosir kecil pakaian bayi'],
            [13, 'expense', 'Operasional Toko', 95000, 'lunas', 'Tunai', null, 'Pembelian plastik dan perlengkapan packing'],
            [15, 'income', 'Penjualan Bazar', 750000, 'lunas', 'Tunai', 'Pasar Malam Minggu', 'Penjualan baju bayi di pasar malam'],
            [15, 'expense', 'Biaya Bazar (Sewa & Operasional)', 100000, 'lunas', 'Tunai', 'Pasar Malam Minggu', 'Sewa tempat jualan bazar'],
            [16, 'income', 'Penjualan Bazar', 1100000, 'lunas', 'QRIS', 'Pasar Malam Minggu', 'Penjualan hari kedua pasar malam'],
            [16, 'expense', 'Biaya Bazar (Sewa & Operasional)', 50000, 'lunas', 'Tunai', 'Pasar Malam Minggu', 'Biaya transportasi dan kebutuhan bazar'],
            [18, 'income', 'Penjualan Online', 925000, 'lunas', 'E-Wallet', null, 'Penjualan romper dan setelan bayi'],
            [20, 'expense', 'Pemasaran & Iklan', 150000, 'lunas', 'E-Wallet', null, 'Promosi produk melalui media sosial'],
            [22, 'income', 'Penjualan Offline / Langsung', 950000, 'lunas', 'Tunai', null, 'Penjualan langsung pakaian bayi'],
            [24, 'expense', 'Pembelian Stok / Barang', 2250000, 'belum_lunas', 'Transfer Bank', null, 'Restok pakaian bayi dengan pembayaran tempo'],
            [26, 'income', 'Penjualan Online', 1350000, 'lunas', 'QRIS', null, 'Pesanan setelan bayi dan jumper'],
            [28, 'expense', 'Ongkos Kirim', 145000, 'lunas', 'Tunai', null, 'Biaya pengiriman pesanan online'],
            [30, 'income', 'Pemasukan Lainnya', 100000, 'lunas', 'Tunai', null, 'Pemasukan tambahan dari penjualan barang sisa'],
            [32, 'expense', 'Pengeluaran Lainnya', 85000, 'lunas', 'Lainnya', null, 'Pengeluaran kecil untuk kebutuhan toko'],
            [35, 'income', 'Penjualan Online', 975000, 'lunas', 'Transfer Bank', null, 'Pesanan baju bayi dari pelanggan'],
            [37, 'expense', 'Operasional Toko', 120000, 'lunas', 'Tunai', null, 'Pembelian label harga dan alat packing'],
            [39, 'income', 'Penjualan Bazar', 650000, 'lunas', 'Tunai', 'Pasar Malam Ceria', 'Penjualan baju bayi di bazar lingkungan'],
            [39, 'expense', 'Biaya Bazar (Sewa & Operasional)', 75000, 'lunas', 'Tunai', 'Pasar Malam Ceria', 'Sewa tempat bazar satu hari'],
            [40, 'income', 'Penjualan Bazar', 950000, 'lunas', 'QRIS', 'Pasar Malam Ceria', 'Penjualan bazar pada hari berikutnya'],
            [40, 'expense', 'Biaya Bazar (Sewa & Operasional)', 50000, 'lunas', 'Tunai', 'Pasar Malam Ceria', 'Biaya transportasi bazar'],
            [42, 'income', 'Penjualan Offline / Langsung', 1250000, 'lunas', 'Transfer Bank', null, 'Pesanan pakaian bayi dari reseller'],
            [44, 'expense', 'Pembelian Stok / Barang', 1650000, 'lunas', 'Transfer Bank', null, 'Pembelian stok jumper dan romper'],
            [46, 'income', 'Penjualan Online', 780000, 'belum_lunas', 'Transfer Bank', null, 'Pesanan pelanggan menunggu pelunasan'],
            [48, 'expense', 'Pemasaran & Iklan', 100000, 'lunas', 'E-Wallet', null, 'Promosi produk di marketplace'],
            [50, 'income', 'Penjualan Online', 1150000, 'lunas', 'QRIS', null, 'Penjualan setelan bayi katun'],
            [52, 'expense', 'Ongkos Kirim', 110000, 'lunas', 'Tunai', null, 'Biaya pengiriman pesanan pelanggan'],
            [54, 'income', 'Penjualan Bazar', 850000, 'lunas', 'Tunai', 'Pasar Malam Akhir Pekan', 'Penjualan produk bayi di pasar malam'],
            [54, 'expense', 'Biaya Bazar (Sewa & Operasional)', 125000, 'lunas', 'Tunai', 'Pasar Malam Akhir Pekan', 'Sewa booth kecil dan biaya operasional'],
            [55, 'income', 'Penjualan Bazar', 1250000, 'lunas', 'QRIS', 'Pasar Malam Akhir Pekan', 'Penjualan bazar hari kedua'],
            [55, 'expense', 'Biaya Bazar (Sewa & Operasional)', 50000, 'lunas', 'Tunai', 'Pasar Malam Akhir Pekan', 'Biaya transportasi bazar'],
            [57, 'income', 'Penjualan Offline / Langsung', 875000, 'lunas', 'Tunai', null, 'Penjualan langsung di toko'],
            [59, 'expense', 'Pembelian Stok / Barang', 1950000, 'lunas', 'Transfer Bank', null, 'Restok baju bayi dari supplier'],
            [62, 'income', 'Penjualan Online', 1450000, 'lunas', 'E-Wallet', null, 'Pesanan beberapa setelan bayi'],
            [64, 'expense', 'Operasional Toko', 135000, 'lunas', 'Tunai', null, 'Pembelian bahan kemasan pesanan'],
            [67, 'income', 'Pemasukan Lainnya', 125000, 'lunas', 'Lainnya', null, 'Pemasukan tambahan usaha'],
            [69, 'expense', 'Pengeluaran Lainnya', 95000, 'lunas', 'Tunai', null, 'Kebutuhan kecil operasional toko'],
            [72, 'income', 'Penjualan Offline / Langsung', 1450000, 'lunas', 'Transfer Bank', null, 'Pesanan pakaian bayi dari reseller'],
            [74, 'expense', 'Pembelian Stok / Barang', 2350000, 'lunas', 'Transfer Bank', null, 'Restok pakaian bayi dan perlengkapan'],
            [77, 'income', 'Penjualan Online', 1050000, 'lunas', 'QRIS', null, 'Penjualan produk bayi melalui marketplace'],
            [79, 'expense', 'Ongkos Kirim', 135000, 'lunas', 'Tunai', null, 'Biaya pengiriman pesanan'],
            [82, 'income', 'Penjualan Bazar', 700000, 'lunas', 'Tunai', 'Pasar Malam Warga', 'Penjualan baju bayi di bazar warga'],
            [82, 'expense', 'Biaya Bazar (Sewa & Operasional)', 100000, 'lunas', 'Tunai', 'Pasar Malam Warga', 'Sewa tempat jualan satu hari'],
            [84, 'income', 'Penjualan Online', 1250000, 'lunas', 'Transfer Bank', null, 'Pesanan pakaian bayi'],
            [87, 'expense', 'Pemasaran & Iklan', 125000, 'lunas', 'E-Wallet', null, 'Biaya promosi online'],
        ];

        foreach ($additionalRows as $i => [$daysAgo, $type, $category, $amount, $status, $method, $event, $description]) {
            $reference = 'DMY-' . str_pad((string) ($i + 61), 3, '0', STR_PAD_LEFT);

            if (Transaction::withTrashed()->where('reference', $reference)->exists()) {
                continue;
            }

            Transaction::create([
                'transaction_date' => now()->subDays($daysAgo)->toDateString(),
                'type'             => $type,
                'category'         => $category,
                'amount'           => $amount,
                'payment_status'   => $status,
                'payment_method'   => $method,
                'reference'        => $reference,
                'event_name'       => $event,
                'description'      => $description,
                'created_by'       => $adminId,
            ]);
        }
    }
}