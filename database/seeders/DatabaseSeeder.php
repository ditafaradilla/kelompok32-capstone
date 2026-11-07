<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin & User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Baby Corner',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
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

        User::updateOrCreate(
            ['email' => 'bunda.sarah@gmail.com'],
            [
                'name' => 'Bunda Sarah',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ]
        );

        // 2. Data Lokasi Toko & Gudang (Fitur 9)
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

        // 3. Data Pemasok / Supplier (Fitur 10)
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

        // 4. Data Produk Baju Bayi (Fitur 8 & 11)
        $p1 = Product::updateOrCreate(
            ['sku' => 'BC-JMP-001'],
            [
                'name' => 'Jumper Katun Organik Premium',
                'category' => 'Jumper',
                'variants' => '0-6 Bulan / Sage Green',
                'size' => '0-6 Bulan',
                'color' => 'Sage Green',
                'price' => 69000,
                'cost_price' => 42000,
                'min_stock' => 10,
                'description' => 'Bahan 100% katun combed organik bersertifikasi OEKO-TEX, sangat adem dan hipoalergenik untuk kulit sensitif bayi baru lahir.',
                'photo' => null,
            ]
        );

        $p2 = Product::updateOrCreate(
            ['sku' => 'BC-PIY-002'],
            [
                'name' => 'Piyama Bayi Lengan Panjang Pastel',
                'category' => 'Piyama',
                'variants' => '6-12 Bulan / Soft Pink',
                'size' => '6-12 Bulan',
                'color' => 'Soft Pink',
                'price' => 85000,
                'cost_price' => 52000,
                'min_stock' => 8,
                'description' => 'Piyama tidur bayi dengan kancing snap depan yang praktis untuk mempermudah ganti popok di malam hari.',
                'photo' => null,
            ]
        );

        $p3 = Product::updateOrCreate(
            ['sku' => 'BC-RMP-003'],
            [
                'name' => 'Romper Motif Hewan Lucu',
                'category' => 'Romper',
                'variants' => '3-6 Bulan / Sky Blue',
                'size' => '3-6 Bulan',
                'color' => 'Sky Blue',
                'price' => 75000,
                'cost_price' => 45000,
                'min_stock' => 10,
                'description' => 'Romper berkerah lucu bermotif safari animal dengan serat bambu alami yang anti-bakteri.',
                'photo' => null,
            ]
        );

        $p4 = Product::updateOrCreate(
            ['sku' => 'BC-STL-004'],
            [
                'name' => 'Setelan Baju Kutung Santai Bayi',
                'category' => 'Setelan',
                'variants' => '0-3 Bulan / Lemon Yellow',
                'size' => '0-3 Bulan',
                'color' => 'Lemon Yellow',
                'price' => 49000,
                'cost_price' => 28000,
                'min_stock' => 5,
                'description' => 'Setelan tanpa lengan cocok untuk cuaca tropis di siang hari.',
                'photo' => null,
            ]
        );

        // 5. Pemisahan Stok Produk di Toko vs Gudang (Fitur 8 & 9)
        // p1: Stok sehat (15 toko, 30 gudang) = total 45
        ProductStock::updateOrCreate(
            ['product_id' => $p1->id, 'location_id' => $tokoUtama->id],
            ['quantity' => 15]
        );
        ProductStock::updateOrCreate(
            ['product_id' => $p1->id, 'location_id' => $gudangPusat->id],
            ['quantity' => 30]
        );

        // p2: Stok Menipis (Fitur 11 Alert Testing) -> 4 di toko, 2 di gudang = total 6 (min_stock = 8)
        ProductStock::updateOrCreate(
            ['product_id' => $p2->id, 'location_id' => $tokoUtama->id],
            ['quantity' => 4]
        );
        ProductStock::updateOrCreate(
            ['product_id' => $p2->id, 'location_id' => $gudangPusat->id],
            ['quantity' => 2]
        );

        // p3: Stok Habis (Fitur 11 Alert Testing) -> 0 di toko, 0 di gudang = total 0
        ProductStock::updateOrCreate(
            ['product_id' => $p3->id, 'location_id' => $tokoUtama->id],
            ['quantity' => 0]
        );
        ProductStock::updateOrCreate(
            ['product_id' => $p3->id, 'location_id' => $gudangPusat->id],
            ['quantity' => 0]
        );

        // p4: Stok 12 (5 di toko, 7 di gudang cadangan)
        ProductStock::updateOrCreate(
            ['product_id' => $p4->id, 'location_id' => $tokoUtama->id],
            ['quantity' => 5]
        );
        ProductStock::updateOrCreate(
            ['product_id' => $p4->id, 'location_id' => $gudangCadangan->id],
            ['quantity' => 7]
        );

        // 6. Data Pemesanan Barang ke Pemasok (PO) untuk Uji Coba Penerimaan (Fitur 10 & 11)
        $po1 = PurchaseOrder::updateOrCreate(
            ['po_number' => 'PO-202611-001'],
            [
                'supplier_id' => $supplier1->id,
                'target_location_id' => $gudangPusat->id,
                'order_date' => now()->subDays(3)->toDateString(),
                'status' => 'pending', // Menunggu barang datang untuk diuji di Fitur 11
                'notes' => 'Restock pesanan bahan katun organik warna sage & romper',
                'total_amount' => 1840000,
            ]
        );

        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po1->id, 'product_id' => $p1->id],
            [
                'quantity_ordered' => 20,
                'quantity_received' => 0,
                'unit_cost' => 42000,
                'subtotal' => 840000,
            ]
        );

        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po1->id, 'product_id' => $p3->id],
            [
                'quantity_ordered' => 22,
                'quantity_received' => 0,
                'unit_cost' => 45000,
                'subtotal' => 1000000,
            ]
        );
    }
}
