<?php

namespace Tests\Feature;

use App\Models\Baby;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersiapanATest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test relasi User hasOne Baby dan Baby belongsTo User.
     */
    public function test_relasi_baby_dan_user(): void
    {
        $user = User::factory()->create();

        $baby = Baby::create([
            'user_id'       => $user->id,
            'nama_bayi'     => 'Baby Nathan',
            'tanggal_lahir' => now()->subMonths(6)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 7.50,
            'tinggi_badan'  => 67.0,
        ]);

        $this->assertNotNull($user->fresh()->baby);
        $this->assertEquals('Baby Nathan', $user->fresh()->baby->nama_bayi);
        $this->assertEquals($user->id, $baby->user->id);
    }

    /**
     * Test perhitungan accessor usia_bulan pada model Baby.
     */
    public function test_perhitungan_usia_bulan(): void
    {
        $user = User::factory()->create();

        // 1. Bayi usia 5 bulan
        $baby5Bulan = Baby::create([
            'user_id'       => $user->id,
            'nama_bayi'     => 'Baby Luna',
            'tanggal_lahir' => Carbon::now()->subMonths(5)->toDateString(),
            'jenis_kelamin' => 'perempuan',
            'berat_badan'   => 7.00,
            'tinggi_badan'  => 65.0,
        ]);
        $this->assertEquals(5, $baby5Bulan->usia_bulan);

        // 2. Bayi baru lahir (0 bulan)
        $baby0Bulan = new Baby([
            'tanggal_lahir' => Carbon::now()->toDateString(),
        ]);
        $this->assertEquals(0, $baby0Bulan->usia_bulan);

        // 3. Tanggal lahir masa depan (fallback minimal 0)
        $babyMasaDepan = new Baby([
            'tanggal_lahir' => Carbon::now()->addMonths(2)->toDateString(),
        ]);
        $this->assertEquals(0, $babyMasaDepan->usia_bulan);
    }

    /**
     * Test stokTersedia() hanya menghitung lokasi dengan tipe 'toko'.
     */
    public function test_stok_tersedia_hanya_menghitung_lokasi_toko(): void
    {
        $product = Product::create([
            'sku'        => 'BC-TST-001',
            'name'       => 'Jumper Uji Coba',
            'category'   => 'Jumper',
            'price'      => 50000,
            'cost_price' => 30000,
            'min_stock'  => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'BC-TST-001-03M',
            'ukuran'     => '0-3M',
            'warna'      => 'Putih',
        ]);

        $toko = Location::create([
            'name' => 'Toko Cabang Utama',
            'code' => 'TKO-TEST',
            'type' => 'toko',
        ]);

        $gudang = Location::create([
            'name' => 'Gudang Pusat Logistik',
            'code' => 'GDG-TEST',
            'type' => 'gudang',
        ]);

        // 10 di toko, 25 di gudang
        ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $toko->id,
            'quantity'           => 10,
        ]);

        ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $gudang->id,
            'quantity'           => 25,
        ]);

        // stok_total dari semua lokasi = 35
        $this->assertEquals(35, $variant->stok_total);

        // stokTersedia() HANYA lokasi toko = 10
        $this->assertEquals(10, $variant->stokTersedia());
    }

    /**
     * Test migrasi data dan kalkulasi stok produk dari seluruh varian.
     */
    public function test_stok_produk_dihitung_dari_seluruh_varian(): void
    {
        $product = Product::create([
            'sku'        => 'BC-TST-002',
            'name'       => 'Piyama Uji Coba Multi Varian',
            'category'   => 'Piyama',
            'price'      => 75000,
            'cost_price' => 45000,
            'min_stock'  => 10,
        ]);

        $v1 = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'BC-TST-002-03M',
            'ukuran'     => '0-3M',
            'warna'      => 'Biru',
        ]);

        $v2 = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'BC-TST-002-36M',
            'ukuran'     => '3-6M',
            'warna'      => 'Kuning',
        ]);

        $loc = Location::create([
            'name' => 'Toko Test',
            'code' => 'TKO-TST2',
            'type' => 'toko',
        ]);

        ProductStock::create(['product_variant_id' => $v1->id, 'location_id' => $loc->id, 'quantity' => 4]);
        ProductStock::create(['product_variant_id' => $v2->id, 'location_id' => $loc->id, 'quantity' => 3]);

        // Total stok produk = 4 + 3 = 7
        $this->assertEquals(7, $product->total_stock);

        // Karena total stok 7 <= min_stock 10 -> is_low_stock true
        $this->assertTrue($product->is_low_stock);
        $this->assertFalse($product->is_out_of_stock);
    }

    /**
     * Test migrasi produk lama ke 1 varian default.
     */
    public function test_migrasi_produk_lama_ke_varian(): void
    {
        $product = Product::create([
            'sku'        => 'BC-OLD-001',
            'name'       => 'Baju Lama Tanpa Varian',
            'category'   => 'Kaos',
            'size'       => '0-3M',
            'color'      => 'Biru Muda',
            'price'      => 40000,
            'cost_price' => 20000,
            'min_stock'  => 5,
        ]);

        // Simulasikan logika migrasi: setiap produk lama dibuatkan 1 varian
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => $product->sku,
            'ukuran'     => $product->size,
            'warna'      => $product->color,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku'        => 'BC-OLD-001',
            'ukuran'     => '0-3M',
            'warna'      => 'Biru Muda',
        ]);

        $this->assertEquals(1, $product->fresh()->variants()->count());
        $this->assertEquals('0-3M / Biru Muda', $product->variants->first()->nama_varian);
    }

    /**
     * Test migrasi data bayi dari kolom lama users ke tabel babies.
     */
    public function test_migrasi_data_bayi_dari_kolom_users(): void
    {
        $user = User::factory()->create([
            'baby_name'   => 'Bayi Sejahtera',
            'baby_age'    => '3 bulan',
            'baby_weight' => '6.2 kg',
            'baby_height' => '62 cm',
            'gender'      => 'laki-laki',
        ]);

        $baby = Baby::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_bayi'     => $user->baby_name,
                'tanggal_lahir' => Carbon::now()->subMonths(3)->toDateString(),
                'jenis_kelamin' => 'laki-laki',
                'berat_badan'   => 6.20,
                'tinggi_badan'  => 62.0,
            ]
        );

        $this->assertDatabaseHas('babies', [
            'user_id'   => $user->id,
            'nama_bayi' => 'Bayi Sejahtera',
        ]);
        $this->assertEquals(3, $baby->usia_bulan);
        // Kolom lama di users tetap dipertahankan
        $this->assertEquals('Bayi Sejahtera', $user->fresh()->baby_name);
    }

    /**
     * Test memastikan Product variants adalah instance Eloquent Collection dan total_stock, is_low_stock, is_out_of_stock bekerja.
     */
    public function test_product_variants_mengembalikan_eloquent_collection_dan_stok_bekerja(): void
    {
        $product = Product::create([
            'sku'        => 'BC-TST-COL',
            'name'       => 'Produk Uji Collection',
            'category'   => 'Kaos',
            'price'      => 45000,
            'cost_price' => 25000,
            'min_stock'  => 5,
        ]);

        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'BC-TST-COL-1',
            'ukuran'     => '0-3M',
            'warna'      => 'Putih',
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'BC-TST-COL-2',
            'ukuran'     => '3-6M',
            'warna'      => 'Kuning',
        ]);

        $loc = Location::create([
            'name' => 'Toko Cabang Test',
            'code' => 'TKO-COL',
            'type' => 'toko',
        ]);

        ProductStock::create(['product_variant_id' => $variant1->id, 'location_id' => $loc->id, 'quantity' => 2]);
        ProductStock::create(['product_variant_id' => $variant2->id, 'location_id' => $loc->id, 'quantity' => 2]);

        $fetched = Product::where('id', $product->id)->first();

        // 1. Pastikan $fetched->variants adalah instance Illuminate\Database\Eloquent\Collection, bukan string
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $fetched->variants);
        $this->assertCount(2, $fetched->variants);

        // 2. Pastikan accessor variant_label bekerja
        $this->assertEquals('0-3M / Putih', $fetched->variants->first()->variant_label);

        // 3. Pastikan total_stock, is_low_stock, dan is_out_of_stock bekerja
        // Total = 4, min_stock = 5 -> is_low_stock = true, is_out_of_stock = false
        $this->assertEquals(4, $fetched->total_stock);
        $this->assertTrue($fetched->is_low_stock);
        $this->assertFalse($fetched->is_out_of_stock);

        // 4. Test saat eager loaded dengan stocks (menghindari error every() on string)
        $eagerLoaded = Product::with(['variants.stocks'])->where('id', $product->id)->first();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $eagerLoaded->variants);
        $this->assertEquals(4, $eagerLoaded->total_stock);
        $this->assertTrue($eagerLoaded->is_low_stock);
        $this->assertFalse($eagerLoaded->is_out_of_stock);
    }

    /**
     * Test memastikan seluruh halaman admin yang berkaitan dengan varian dapat dibuka tanpa error.
     */
    public function test_halaman_admin_dapat_dibuka_tanpa_error(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $admin = User::where('role', 'admin')->first();

        // 1. /admin/dashboard
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        // 2. /admin/produk
        $response = $this->actingAs($admin)->get('/admin/produk');
        $response->assertStatus(200);

        // 3. /admin/lokasi-stok
        $response = $this->actingAs($admin)->get('/admin/lokasi-stok');
        $response->assertStatus(200);

        // 4. /admin/pemesanan-barang/buat
        $response = $this->actingAs($admin)->get('/admin/pemesanan-barang/buat');
        $response->assertStatus(200);

        // 5. /admin/penerimaan-barang
        $response = $this->actingAs($admin)->get('/admin/penerimaan-barang');
        $response->assertStatus(200);
    }

    /**
     * Test helper rupiah.
     */
    public function test_helper_rupiah_memformat_dengan_benar(): void
    {
        $this->assertEquals('Rp 125.000', rupiah(125000));
        $this->assertEquals('Rp 0', rupiah(0));
        $this->assertEquals('Rp 1.500.000', rupiah(1500000));
    }
}

