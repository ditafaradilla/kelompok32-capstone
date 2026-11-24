<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Dapat dijalankan manual dengan: php artisan db:seed --class=DemoOrderSeeder
     */
    public function run(): void
    {
        // 1. Ambil atau buat akun Bunda Sarah
        $user = User::firstOrCreate(
            ['email' => 'bunda.sarah@gmail.com'],
            [
                'name'     => 'Bunda Sarah',
                'password' => Hash::make('user123'),
                'role'     => 'user',
                'phone'    => '089876543210',
                'address'  => 'Jl. Boulevard Raya No. 12, Jakarta Barat',
            ]
        );

        // Ambil admin untuk catatan riwayat
        $admin = User::where('role', 'admin')->first() ?: User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin Baby Corner',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'phone'    => '081234567890',
            ]
        );

        // 2. Pastikan lokasi toko fisik tersedia
        $location = Location::where('type', 'toko')->first() ?: Location::create([
            'name'    => 'Toko Utama (Etalase)',
            'code'    => 'TKO-DEMO',
            'type'    => 'toko',
            'address' => 'Jl. Boulevard Raya No. 12, Jakarta',
        ]);

        // 3. Pastikan ada varian produk untuk demo
        $variants = ProductVariant::with('product')->take(3)->get();
        if ($variants->count() < 3) {
            $dummyProducts = [
                ['name' => 'Romper Bayi Katun Organik', 'sku' => 'DEMO-RMP-01', 'price' => 65000, 'size' => '3-6M', 'color' => 'Sage Green'],
                ['name' => 'Setelan Piyama Bayi Lembut', 'sku' => 'DEMO-PYM-02', 'price' => 85000, 'size' => '6-12M', 'color' => 'Baby Blue'],
                ['name' => 'Jumper Rajut Bayi Premium', 'sku' => 'DEMO-JMP-03', 'price' => 110000, 'size' => '0-3M', 'color' => 'Soft Cream'],
            ];

            foreach ($dummyProducts as $dp) {
                $p = Product::firstOrCreate(
                    ['sku' => $dp['sku']],
                    [
                        'name'       => $dp['name'],
                        'category'   => 'Pakaian',
                        'price'      => $dp['price'],
                        'cost_price' => $dp['price'] * 0.6,
                        'min_stock'  => 5,
                    ]
                );

                $v = ProductVariant::firstOrCreate(
                    ['sku' => $dp['sku'] . '-VAR'],
                    [
                        'product_id' => $p->id,
                        'ukuran'     => $dp['size'],
                        'warna'      => $dp['color'],
                    ]
                );

                ProductStock::firstOrCreate(
                    ['product_variant_id' => $v->id, 'location_id' => $location->id],
                    ['quantity' => 25]
                );
            }

            $variants = ProductVariant::with('product')->take(3)->get();
        }

        $v1 = $variants[0];
        $v2 = $variants[1] ?? $v1;
        $v3 = $variants[2] ?? $v1;

        // 4. Data pesanan demo dengan variasi status
        $demoOrdersData = [
            [
                'nomor_pesanan'   => 'BC-20261125-0101',
                'status'          => Order::STATUS_MENUNGGU_PEMBAYARAN,
                'tanggal'         => Carbon::now()->subHours(2),
                'metode_bayar'    => Payment::METODE_TRANSFER,
                'status_bayar'    => Payment::STATUS_BELUM_BAYAR,
                'items'           => [
                    ['variant' => $v1, 'qty' => 1, 'harga' => $v1->product->price],
                ],
                'kurir'           => null,
                'resi'            => null,
                'histories'       => [
                    ['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'catatan' => 'Pesanan berhasil dibuat, menunggu pembayaran pelanggan.', 'user' => $user->id, 'time' => Carbon::now()->subHours(2)],
                ],
            ],
            [
                'nomor_pesanan'   => 'BC-20261124-0202',
                'status'          => Order::STATUS_MENUNGGU_VERIFIKASI,
                'tanggal'         => Carbon::now()->subDays(1),
                'metode_bayar'    => Payment::METODE_TRANSFER,
                'status_bayar'    => Payment::STATUS_MENUNGGU_VERIFIKASI,
                'items'           => [
                    ['variant' => $v2, 'qty' => 2, 'harga' => $v2->product->price],
                ],
                'kurir'           => null,
                'resi'            => null,
                'histories'       => [
                    ['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'catatan' => 'Pesanan berhasil dibuat.', 'user' => $user->id, 'time' => Carbon::now()->subDays(1)],
                    ['status' => Order::STATUS_MENUNGGU_VERIFIKASI, 'catatan' => 'Pelanggan telah mengunggah bukti transfer.', 'user' => $user->id, 'time' => Carbon::now()->subHours(18)],
                ],
            ],
            [
                'nomor_pesanan'   => 'BC-20261123-0303',
                'status'          => Order::STATUS_DIPROSES,
                'tanggal'         => Carbon::now()->subDays(2),
                'metode_bayar'    => Payment::METODE_QRIS,
                'status_bayar'    => Payment::STATUS_LUNAS,
                'items'           => [
                    ['variant' => $v1, 'qty' => 1, 'harga' => $v1->product->price],
                    ['variant' => $v2, 'qty' => 1, 'harga' => $v2->product->price],
                ],
                'kurir'           => null,
                'resi'            => null,
                'histories'       => [
                    ['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'catatan' => 'Pesanan berhasil dibuat.', 'user' => $user->id, 'time' => Carbon::now()->subDays(2)],
                    ['status' => Order::STATUS_MENUNGGU_VERIFIKASI, 'catatan' => 'Bukti pembayaran diterima.', 'user' => $user->id, 'time' => Carbon::now()->subDays(2)->addHours(1)],
                    ['status' => Order::STATUS_DIPROSES, 'catatan' => 'Pembayaran lunas terverifikasi, barang sedang dipersiapkan.', 'user' => $admin->id, 'time' => Carbon::now()->subDays(2)->addHours(2)],
                ],
            ],
            [
                'nomor_pesanan'   => 'BC-20261122-0404',
                'status'          => Order::STATUS_DIKIRIM,
                'tanggal'         => Carbon::now()->subDays(3),
                'metode_bayar'    => Payment::METODE_TRANSFER,
                'status_bayar'    => Payment::STATUS_LUNAS,
                'items'           => [
                    ['variant' => $v3, 'qty' => 2, 'harga' => $v3->product->price],
                ],
                'kurir'           => 'JNE Express',
                'resi'            => 'JNE882910291',
                'histories'       => [
                    ['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'catatan' => 'Pesanan berhasil dibuat.', 'user' => $user->id, 'time' => Carbon::now()->subDays(3)],
                    ['status' => Order::STATUS_MENUNGGU_VERIFIKASI, 'catatan' => 'Bukti transfer diunggah.', 'user' => $user->id, 'time' => Carbon::now()->subDays(3)->addHours(1)],
                    ['status' => Order::STATUS_DIPROSES, 'catatan' => 'Pembayaran diverifikasi.', 'user' => $admin->id, 'time' => Carbon::now()->subDays(3)->addHours(2)],
                    ['status' => Order::STATUS_DIKIRIM, 'catatan' => 'Paket diserahkan ke JNE Express.', 'user' => $admin->id, 'time' => Carbon::now()->subDays(3)->addHours(5)],
                ],
            ],
            [
                'nomor_pesanan'   => 'BC-20261120-0505',
                'status'          => Order::STATUS_SELESAI,
                'tanggal'         => Carbon::now()->subDays(5),
                'metode_bayar'    => Payment::METODE_TRANSFER,
                'status_bayar'    => Payment::STATUS_LUNAS,
                'items'           => [
                    ['variant' => $v1, 'qty' => 1, 'harga' => $v1->product->price],
                    ['variant' => $v2, 'qty' => 1, 'harga' => $v2->product->price],
                    ['variant' => $v3, 'qty' => 1, 'harga' => $v3->product->price],
                ],
                'kurir'           => 'SiCepat Reguler',
                'resi'            => 'SCP77182931',
                'histories'       => [
                    ['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'catatan' => 'Pesanan dibuat.', 'user' => $user->id, 'time' => Carbon::now()->subDays(5)],
                    ['status' => Order::STATUS_MENUNGGU_VERIFIKASI, 'catatan' => 'Bukti transfer diunggah.', 'user' => $user->id, 'time' => Carbon::now()->subDays(5)->addHours(1)],
                    ['status' => Order::STATUS_DIPROSES, 'catatan' => 'Pembayaran lunas.', 'user' => $admin->id, 'time' => Carbon::now()->subDays(5)->addHours(2)],
                    ['status' => Order::STATUS_DIKIRIM, 'catatan' => 'Paket dikirimkan.', 'user' => $admin->id, 'time' => Carbon::now()->subDays(5)->addHours(4)],
                    ['status' => Order::STATUS_SELESAI, 'catatan' => 'Pesanan telah diterima oleh pelanggan.', 'user' => $user->id, 'time' => Carbon::now()->subDays(4)],
                ],
            ],
            [
                'nomor_pesanan'   => 'BC-20261118-0606',
                'status'          => Order::STATUS_DIBATALKAN,
                'tanggal'         => Carbon::now()->subDays(7),
                'metode_bayar'    => Payment::METODE_TRANSFER,
                'status_bayar'    => Payment::STATUS_BELUM_BAYAR,
                'items'           => [
                    ['variant' => $v1, 'qty' => 1, 'harga' => $v1->product->price],
                ],
                'kurir'           => null,
                'resi'            => null,
                'histories'       => [
                    ['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'catatan' => 'Pesanan dibuat.', 'user' => $user->id, 'time' => Carbon::now()->subDays(7)],
                    ['status' => Order::STATUS_DIBATALKAN, 'catatan' => 'Pesanan dibatalkan oleh pelanggan.', 'user' => $user->id, 'time' => Carbon::now()->subDays(7)->addHours(3)],
                ],
            ],
        ];

        foreach ($demoOrdersData as $data) {
            $totalHarga = 0;
            foreach ($data['items'] as $it) {
                $totalHarga += ($it['harga'] * $it['qty']);
            }

            $order = Order::updateOrCreate(
                ['nomor_pesanan' => $data['nomor_pesanan']],
                [
                    'user_id'           => $user->id,
                    'tanggal_pesanan'   => $data['tanggal'],
                    'total_harga'       => $totalHarga,
                    'alamat_pengiriman' => $user->address ?: 'Jl. Boulevard Raya No. 12, Jakarta',
                    'nama_penerima'     => $user->name,
                    'telepon_penerima'  => $user->phone ?: '089876543210',
                    'status_pesanan'    => $data['status'],
                    'kurir'             => $data['kurir'],
                    'nomor_resi'        => $data['resi'],
                ]
            );

            // Bersihkan items & payment lama jika re-seed
            $order->items()->delete();
            foreach ($data['items'] as $it) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $it['variant']->id,
                    'product_id'         => $it['variant']->product_id,
                    'jumlah'             => $it['qty'],
                    'harga_satuan'       => $it['harga'],
                    'nama_produk'        => $it['variant']->product->name,
                    'varian_label'       => $it['variant']->variant_label,
                ]);
            }

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'metode_pembayaran' => $data['metode_bayar'],
                    'jumlah_bayar'      => $totalHarga,
                    'status_pembayaran' => $data['status_bayar'],
                    'tanggal_bayar'     => $data['status_bayar'] !== Payment::STATUS_BELUM_BAYAR ? $data['tanggal']->copy()->addHours(1) : null,
                ]
            );

            $order->histories()->delete();
            foreach ($data['histories'] as $h) {
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => $h['status'],
                    'catatan'    => $h['catatan'],
                    'changed_by' => $h['user'],
                    'created_at' => $h['time'],
                    'updated_at' => $h['time'],
                ]);
            }
        }

        // 5. Tambahkan ulasan contoh pada produk pesanan selesai (FRI-07)
        $completedOrder = Order::where('user_id', $user->id)
            ->where('status_pesanan', Order::STATUS_SELESAI)
            ->first();

        if ($completedOrder) {
            $item1 = $completedOrder->items()->first();
            if ($item1) {
                Review::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'product_id' => $item1->product_id,
                    ],
                    [
                        'order_item_id' => $item1->id,
                        'rating'        => 5,
                        'ulasan'        => 'Bahan katun organiknya sangat lembut dan adem saat dipakai si kecil. Jahitan sangat rapi, tidak bikin kulit bayi iritasi!',
                    ]
                );
            }
        }

        // Buat beberapa ulasan pelanggan lain agar distribusi rating terlihat nyata
        $otherCustomers = [
            ['name' => 'Dian Kartika', 'email' => 'dian.kartika@example.com', 'rating' => 5, 'ulasan' => 'Puas sekali belanja di Baby Corner! Bahannya tidak menyusut setelah dicuci berkali-kali.'],
            ['name' => 'Rina Melati', 'email' => 'rina.melati@example.com', 'rating' => 4, 'ulasan' => 'Modelnya lucu dan pas di badan bayi saya. Pengiriman cepat dan packaging aman.'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.nur@example.com', 'rating' => 5, 'ulasan' => 'Sangat rekomendasi untuk kado kelahiran anak teman. Kualitas premium!'],
            ['name' => 'Mega Puspita', 'email' => 'mega.p@example.com', 'rating' => 4, 'ulasan' => 'Warnanya sesuai foto, bahannya lembut dan adem dipakai siang hari.'],
        ];

        foreach ($otherCustomers as $idx => $oc) {
            $cust = User::firstOrCreate(
                ['email' => $oc['email']],
                [
                    'name'     => $oc['name'],
                    'password' => Hash::make('password123'),
                    'role'     => 'user',
                    'phone'    => '0812998877' . $idx,
                ]
            );

            // Buat pesanan selesai untuk pelanggan ini agar memenuhi aturan canBeReviewedBy
            $custOrder = Order::firstOrCreate(
                ['nomor_pesanan' => 'BC-DEMO-CUST-0' . $idx],
                [
                    'user_id'           => $cust->id,
                    'tanggal_pesanan'   => Carbon::now()->subDays(10 + $idx),
                    'total_harga'       => $v1->product->price,
                    'alamat_pengiriman' => 'Jl. Anggrek No. ' . ($idx + 1),
                    'nama_penerima'     => $cust->name,
                    'telepon_penerima'  => $cust->phone,
                    'status_pesanan'    => Order::STATUS_SELESAI,
                ]
            );

            $custItem = OrderItem::firstOrCreate(
                ['order_id' => $custOrder->id, 'product_id' => $v1->product_id],
                [
                    'product_variant_id' => $v1->id,
                    'jumlah'             => 1,
                    'harga_satuan'       => $v1->product->price,
                    'nama_produk'        => $v1->product->name,
                    'varian_label'       => $v1->variant_label,
                ]
            );

            Review::updateOrCreate(
                [
                    'user_id'    => $cust->id,
                    'product_id' => $v1->product_id,
                ],
                [
                    'order_item_id' => $custItem->id,
                    'rating'        => $oc['rating'],
                    'ulasan'        => $oc['ulasan'],
                ]
            );
        }
    }
}

