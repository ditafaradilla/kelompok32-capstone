<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Baby;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Panduan Ukuran Standar (Size Guide)
        $this->call(SizeGuideSeeder::class);

        // 2. Akun Admin & Pelanggan
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Baby Corner',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '081234567890',
            ]
        );

        User::updateOrCreate(
            ['email' => 'alica@gmail.com'],
            [
                'name' => 'alica',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $bundaSarah = User::updateOrCreate(
            ['email' => 'bunda.sarah@gmail.com'],
            [
                'name' => 'Bunda Sarah',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'phone' => '089876543210',
                'address' => 'Jl. Boulevard Raya No. 12, Jakarta',
                'baby_name' => 'Adiba',
                'gender' => 'perempuan',
                'baby_age' => '4 Bulan',
                'baby_weight' => '6.5',
                'baby_height' => '63',
            ]
        );

        // Data Bayi untuk Bunda Sarah (Sesuai Kriteria Persiapan A)
        Baby::updateOrCreate(
            ['user_id' => $bundaSarah->id],
            [
                'nama_bayi'     => 'Adiba',
                'tanggal_lahir' => Carbon::now()->subMonths(4)->toDateString(),
                'jenis_kelamin' => 'perempuan',
                'berat_badan'   => 6.50,
                'tinggi_badan'  => 63.0,
            ]
        );

        // 3. Data Titik Lokasi: Toko Fisik vs Gudang (Fitur 9)
        $tokoUtama = Location::updateOrCreate(
            ['code' => 'TKO-01'],
            [
                'name' => 'Toko Utama (Etalase)',
                'type' => 'toko',
                'address' => 'Jl. Boulevard Raya No. 12, Jakarta',
            ]
        );

        $gudangPusat = Location::updateOrCreate(
            ['code' => 'GDG-01'],
            [
                'name' => 'Gudang Pusat Sentral',
                'type' => 'gudang',
                'address' => 'Kawasan Industri Pergudangan Blok B3, Tangerang',
            ]
        );

        $gudangCadangan = Location::updateOrCreate(
            ['code' => 'GDG-02'],
            [
                'name' => 'Gudang Cadangan Timur',
                'type' => 'gudang',
                'address' => 'Jl. Logistik Timur No. 45, Bekasi',
            ]
        );

        // 4. Data Pemasok / Supplier (Fitur 10)
        $supplier1 = Supplier::updateOrCreate(
            ['name' => 'CV Berkah Konveksi Bayi'],
            [
                'contact_person' => 'Ibu Ratna',
                'phone' => '081234567890',
                'email' => 'berkah.konveksi@gmail.com',
                'address' => 'Sentra Rajut & Katun, Bandung',
            ]
        );

        $supplier2 = Supplier::updateOrCreate(
            ['name' => 'PT Surya Tekstil Kids'],
            [
                'contact_person' => 'Pak Bambang',
                'phone' => '081398765432',
                'email' => 'suryatekstil@kidsapparel.id',
                'address' => 'Kawasan Garment No. 88, Solo',
            ]
        );

        // 5. Data Produk Pakaian Bayi (Minimal 6 Kategori: Kaos, Jumper, Setelan, Bedong, Topi, Sarung Tangan)
        $productsCatalog = [
            // 1. Kaos
            [
                'sku'         => 'BC-KOS-100',
                'name'        => 'Kaos Bayi Katun Combed Lembut',
                'category'    => 'Kaos',
                'price'       => 45000,
                'cost_price'  => 26000,
                'min_stock'   => 10,
                'description' => 'Kaos lengan pendek dari bahan 100% katun combed 30s ramah kulit bayi, adem dan tidak menyebabkan biang keringat.',
                'variants'    => [
                    ['sku' => 'BC-KOS-101', 'ukuran' => '0-3M', 'warna' => 'Putih Polos', 'toko' => 12, 'gudang' => 20],
                    ['sku' => 'BC-KOS-102', 'ukuran' => '3-6M', 'warna' => 'Sage Green',  'toko' => 15, 'gudang' => 25],
                    ['sku' => 'BC-KOS-103', 'ukuran' => '6-9M', 'warna' => 'Pastel Blue', 'toko' => 10, 'gudang' => 15],
                ],
            ],
            // 2. Jumper
            [
                'sku'         => 'BC-JMP-200',
                'name'        => 'Jumper Katun Organik Premium',
                'category'    => 'Jumper',
                'price'       => 69000,
                'cost_price'  => 42000,
                'min_stock'   => 8,
                'description' => 'Jumper bersertifikasi OEKO-TEX dengan kancing snap anti-karat di bagian selangkangan untuk kemudahan ganti popok.',
                'variants'    => [
                    ['sku' => 'BC-JMP-201', 'ukuran' => '0-3M', 'warna' => 'Soft Cream',  'toko' => 10, 'gudang' => 20],
                    ['sku' => 'BC-JMP-202', 'ukuran' => '3-6M', 'warna' => 'Sage Green',  'toko' => 8,  'gudang' => 18],
                    ['sku' => 'BC-JMP-203', 'ukuran' => '6-9M', 'warna' => 'Sky Blue',    'toko' => 5,  'gudang' => 12],
                    ['sku' => 'BC-JMP-204', 'ukuran' => '9-12M','warna' => 'Dusty Pink',  'toko' => 2,  'gudang' => 3], // alert menipis
                ],
            ],
            // 3. Setelan
            [
                'sku'         => 'BC-STL-300',
                'name'        => 'Setelan Baju & Celana Bayi Harian',
                'category'    => 'Setelan',
                'price'       => 79000,
                'cost_price'  => 48000,
                'min_stock'   => 8,
                'description' => 'Setelan baju kancing depan lengkap dengan celana panjang berkaret lembut anti-begah.',
                'variants'    => [
                    ['sku' => 'BC-STL-301', 'ukuran' => '3-6M',  'warna' => 'Lemon Yellow','toko' => 6, 'gudang' => 14],
                    ['sku' => 'BC-STL-302', 'ukuran' => '6-9M',  'warna' => 'Soft Pink',   'toko' => 8, 'gudang' => 15],
                    ['sku' => 'BC-STL-303', 'ukuran' => '9-12M', 'warna' => 'Lilac Grey',  'toko' => 0, 'gudang' => 0], // habis
                ],
            ],
            // 4. Bedong
            [
                'sku'         => 'BC-BDG-400',
                'name'        => 'Kain Bedong Bayi Katun Bambu Lembut',
                'category'    => 'Bedong',
                'price'       => 55000,
                'cost_price'  => 32000,
                'min_stock'   => 6,
                'description' => 'Bedong bayi multifungsi berukuran 110x90 cm, bisa digunakan sebagai selimut atau penutup menyusui.',
                'variants'    => [
                    ['sku' => 'BC-BDG-401', 'ukuran' => 'NB',   'warna' => 'Motif Awan', 'toko' => 14, 'gudang' => 30],
                    ['sku' => 'BC-BDG-402', 'ukuran' => '0-3M', 'warna' => 'Abu Polos',  'toko' => 10, 'gudang' => 20],
                ],
            ],
            // 5. Topi
            [
                'sku'         => 'BC-TPI-500',
                'name'        => 'Topi Bayi Rajut Lembut Newborn',
                'category'    => 'Aksesoris & Topi',
                'price'       => 29000,
                'cost_price'  => 15000,
                'min_stock'   => 5,
                'description' => 'Kupluk bayi berbahan rajut katun elastis, menghangatkan kepala bayi baru lahir tanpa menekan telinga.',
                'variants'    => [
                    ['sku' => 'BC-TPI-501', 'ukuran' => 'NB',   'warna' => 'Off White',   'toko' => 20, 'gudang' => 40],
                    ['sku' => 'BC-TPI-502', 'ukuran' => '0-3M', 'warna' => 'Baby Peach',  'toko' => 15, 'gudang' => 25],
                ],
            ],
            // 6. Sarung Tangan
            [
                'sku'         => 'BC-STG-600',
                'name'        => 'Sarung Tangan & Kaos Kaki Bayi Anti-Cakar',
                'category'    => 'Aksesoris & Topi',
                'price'       => 25000,
                'cost_price'  => 12000,
                'min_stock'   => 5,
                'description' => '1 pasang sarung tangan dan kaos kaki bergelang lembut elastis agar bayi tidak mencakar wajahnya sendiri.',
                'variants'    => [
                    ['sku' => 'BC-STG-601', 'ukuran' => 'NB',   'warna' => 'Putih Polos', 'toko' => 25, 'gudang' => 50],
                    ['sku' => 'BC-STG-602', 'ukuran' => '0-3M', 'warna' => 'Soft Mint',   'toko' => 18, 'gudang' => 35],
                ],
            ],
        ];

        $firstVariantSample = null;
        $secondVariantSample = null;

        foreach ($productsCatalog as $pData) {
            $product = Product::updateOrCreate(
                ['sku' => $pData['sku']],
                [
                    'name'        => $pData['name'],
                    'category'    => $pData['category'],
                    'size'        => $pData['variants'][0]['ukuran'] ?? null,
                    'color'       => $pData['variants'][0]['warna'] ?? null,
                    'price'       => $pData['price'],
                    'cost_price'  => $pData['cost_price'],
                    'min_stock'   => $pData['min_stock'],
                    'description' => $pData['description'],
                    'photo'       => null,
                ]
            );

            foreach ($pData['variants'] as $vData) {
                $variant = ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'ukuran'     => $vData['ukuran'],
                        'warna'      => $vData['warna'],
                    ],
                    [
                        'sku'        => $vData['sku'],
                    ]
                );

                if (!$firstVariantSample) {
                    $firstVariantSample = $variant;
                } elseif (!$secondVariantSample && $variant->id !== $firstVariantSample->id) {
                    $secondVariantSample = $variant;
                }

                // Alokasikan stok ke Toko dan Gudang
                if (isset($vData['toko'])) {
                    ProductStock::updateOrCreate(
                        ['product_variant_id' => $variant->id, 'location_id' => $tokoUtama->id],
                        ['quantity' => $vData['toko']]
                    );
                }

                if (isset($vData['gudang'])) {
                    ProductStock::updateOrCreate(
                        ['product_variant_id' => $variant->id, 'location_id' => $gudangPusat->id],
                        ['quantity' => $vData['gudang']]
                    );
                }
            }
        }

        // 6. Data Pemesanan Barang ke Pemasok (PO) untuk Uji Coba Penerimaan (Fitur 10 & 11)
        if ($firstVariantSample && $secondVariantSample) {
            $po1 = PurchaseOrder::updateOrCreate(
                ['po_number' => 'PO-202611-001'],
                [
                    'supplier_id'        => $supplier1->id,
                    'target_location_id' => $gudangPusat->id,
                    'order_date'         => now()->subDays(3)->toDateString(),
                    'status'             => 'pending',
                    'notes'              => 'Restock pesanan katun combed dan jumper bayi',
                    'total_amount'       => 1680000,
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                [
                    'purchase_order_id'  => $po1->id,
                    'product_variant_id' => $firstVariantSample->id,
                ],
                [
                    'product_id'        => $firstVariantSample->product_id,
                    'quantity_ordered'  => 20,
                    'quantity_received' => 0,
                    'unit_cost'         => $firstVariantSample->product->cost_price ?? 26000,
                    'subtotal'          => 20 * ($firstVariantSample->product->cost_price ?? 26000),
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                [
                    'purchase_order_id'  => $po1->id,
                    'product_variant_id' => $secondVariantSample->id,
                ],
                [
                    'product_id'        => $secondVariantSample->product_id,
                    'quantity_ordered'  => 25,
                    'quantity_received' => 0,
                    'unit_cost'         => $secondVariantSample->product->cost_price ?? 42000,
                    'subtotal'          => 25 * ($secondVariantSample->product->cost_price ?? 42000),
                ]
            );
        }
    }
}
