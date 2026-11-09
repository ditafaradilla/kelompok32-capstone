<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FRI03KeranjangBelanjaTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithStock(string $name = 'Baju Bayi Test', float $price = 50000, int $stockQty = 10, string $locType = 'toko'): array
    {
        $location = Location::create([
            'name' => 'Toko ' . uniqid(),
            'code' => 'TKO-' . rand(100, 999),
            'type' => $locType,
        ]);

        $product = Product::create([
            'name'       => $name,
            'sku'        => 'BC-TEST-' . rand(1000, 9999),
            'category'   => 'Pakaian',
            'price'      => $price,
            'cost_price' => $price * 0.5,
            'min_stock'  => 2,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => $product->sku . '-VAR',
            'ukuran'     => '0-3M',
            'warna'      => 'Biru',
        ]);

        $stock = ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $location->id,
            'quantity'           => $stockQty,
        ]);

        return [$product, $variant, $location, $stock];
    }

    /**
     * Skenario 1: Tambah varian baru ke keranjang belanja.
     */
    public function test_skenario_1_tambah_varian_baru(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Kaos Bayi', 50000, 10);

        $response = $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('carts', [
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);
    }

    /**
     * Skenario 2: Tambah varian yang sama -> jumlah bertambah (tidak membuat baris duplikat).
     */
    public function test_skenario_2_tambah_varian_yang_sama_jumlah_bertambah(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Kaos Bayi', 50000, 10);

        // Tambah pertama kali: 2 pcs
        $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);

        // Tambah kedua kali: 3 pcs
        $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 3,
        ]);

        // Hanya 1 baris di tabel carts, dan jumlahnya menjadi 5
        $this->assertEquals(1, CartItem::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('carts', [
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 5,
        ]);
    }

    /**
     * Skenario 3: Tambah melebihi stok tersedia ditolak dengan pesan sisa stok.
     */
    public function test_skenario_3_tambah_melebihi_stok_ditolak(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Kaos Bayi', 50000, 5);

        // Stok tersedia 5, user mencoba menambahkan 6 pcs
        $response = $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 6,
        ]);

        $response->assertSessionHasErrors('jumlah');
        $this->assertEquals(0, CartItem::where('user_id', $user->id)->count());

        // Jika sudah ada 4 di keranjang, tambah 2 lagi (total 6 > 5) juga ditolak
        CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 4,
        ]);

        $response2 = $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);
        $response2->assertSessionHasErrors('jumlah');
        $this->assertEquals(4, CartItem::where('user_id', $user->id)->first()->jumlah);
    }

    /**
     * Skenario 4: Varian dengan stok 0 tidak bisa ditambahkan ke keranjang.
     */
    public function test_skenario_4_stok_0_ditolak(): void
    {
        $user = User::factory()->create();
        // Buat produk dengan stok 0 di lokasi toko
        [$product, $variant] = $this->createProductWithStock('Produk Habis', 50000, 0);

        $response = $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 1,
        ]);

        $response->assertSessionHasErrors('jumlah');
        $this->assertDatabaseMissing('carts', [
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        // Cek juga jika stok hanya ada di gudang (bukan toko) -> stokTersedia() tetap 0
        [$productGudang, $variantGudang] = $this->createProductWithStock('Produk Gudang', 50000, 10, 'gudang');
        $responseGudang = $this->actingAs($user)->post(route('keranjang.store'), [
            'product_variant_id' => $variantGudang->id,
            'jumlah'             => 1,
        ]);
        $responseGudang->assertSessionHasErrors('jumlah');
    }

    /**
     * Skenario 5: Ubah jumlah item di keranjang (kontrol +/-).
     */
    public function test_skenario_5_ubah_jumlah_item(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Kaos Bayi', 50000, 10);

        $item = CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);

        // Ubah jumlah menjadi 5
        $response = $this->actingAs($user)->patch(route('keranjang.update', $item), [
            'jumlah' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(5, $item->fresh()->jumlah);

        // Ubah melebihi stok (15 > 10) -> ditolak
        $responseExceed = $this->actingAs($user)->patch(route('keranjang.update', $item), [
            'jumlah' => 15,
        ]);
        $responseExceed->assertSessionHasErrors('jumlah');
        $this->assertEquals(5, $item->fresh()->jumlah);
    }

    /**
     * Skenario 6: Hapus satu item dari keranjang.
     */
    public function test_skenario_6_hapus_satu_item(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Kaos Bayi', 50000, 10);

        $item = CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);

        $response = $this->actingAs($user)->delete(route('keranjang.destroy', $item));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('carts', ['id' => $item->id]);
    }

    /**
     * Skenario 7: Kosongkan seluruh keranjang belanja (clear cart).
     */
    public function test_skenario_7_kosongkan_keranjang(): void
    {
        $user = User::factory()->create();
        [$p1, $v1] = $this->createProductWithStock('Produk 1', 50000, 10);
        [$p2, $v2] = $this->createProductWithStock('Produk 2', 75000, 10);

        CartItem::create(['user_id' => $user->id, 'product_variant_id' => $v1->id, 'jumlah' => 2]);
        CartItem::create(['user_id' => $user->id, 'product_variant_id' => $v2->id, 'jumlah' => 1]);

        $this->assertEquals(2, CartItem::where('user_id', $user->id)->count());

        $response = $this->actingAs($user)->delete(route('keranjang.clear'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(0, CartItem::where('user_id', $user->id)->count());
    }

    /**
     * Skenario 8: Pengguna lain tidak bisa mengubah atau menghapus item keranjang milikku (403 Forbidden).
     */
    public function test_skenario_8_user_lain_tidak_bisa_mengubah_atau_menghapus_item(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Kaos Bayi', 50000, 10);

        $itemA = CartItem::create([
            'user_id'            => $userA->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);

        // User B mencoba update item milik User A
        $responseUpdate = $this->actingAs($userB)->patch(route('keranjang.update', $itemA), [
            'jumlah' => 4,
        ]);
        $responseUpdate->assertStatus(403);
        $this->assertEquals(2, $itemA->fresh()->jumlah);

        // User B mencoba menghapus item milik User A
        $responseDelete = $this->actingAs($userB)->delete(route('keranjang.destroy', $itemA));
        $responseDelete->assertStatus(403);
        $this->assertDatabaseHas('carts', ['id' => $itemA->id]);
    }

    /**
     * Skenario 9: Total belanja dan subtotal per item dihitung dengan benar dari products.price.
     */
    public function test_skenario_9_total_harga_dan_subtotal_benar(): void
    {
        $user = User::factory()->create();
        [$p1, $v1] = $this->createProductWithStock('Produk 1', 50000, 10);
        [$p2, $v2] = $this->createProductWithStock('Produk 2', 75000, 10);

        CartItem::create(['user_id' => $user->id, 'product_variant_id' => $v1->id, 'jumlah' => 2]); // 100.000
        CartItem::create(['user_id' => $user->id, 'product_variant_id' => $v2->id, 'jumlah' => 1]); // 75.000
        // Total = 175.000

        $response = $this->actingAs($user)->get(route('keranjang.index'));

        $response->assertStatus(200);
        $response->assertSee('Rp 100.000');
        $response->assertSee('Rp 75.000');
        $response->assertSee('Rp 175.000');
        $response->assertSee('3 pcs');
    }

    /**
     * Skenario 10: Pengunjung yang belum login (tamu/guest) diarahkan ke halaman login.
     */
    public function test_skenario_10_guest_diarahkan_ke_login(): void
    {
        [$product, $variant] = $this->createProductWithStock('Produk Tamu', 50000, 10);

        $this->get(route('keranjang.index'))->assertRedirect(route('login'));
        $this->post(route('keranjang.store'), [
            'product_variant_id' => $variant->id,
            'jumlah'             => 1,
        ])->assertRedirect(route('login'));
    }

    /**
     * Skenario Tambahan: View Composer badge navbar menampilkan jumlah item secara akurat.
     */
    public function test_badge_keranjang_di_navbar_menampilkan_jumlah_item(): void
    {
        $user = User::factory()->create();
        [$p1, $v1] = $this->createProductWithStock('Produk 1', 50000, 10);

        // Sebelum ada item
        $resEmpty = $this->actingAs($user)->get(route('dashboard'));
        $resEmpty->assertDontSee('id="nav-cart-badge"', false);

        // Tambah 3 pcs
        CartItem::create(['user_id' => $user->id, 'product_variant_id' => $v1->id, 'jumlah' => 3]);

        $resWithItems = $this->actingAs($user)->get(route('dashboard'));
        $resWithItems->assertSee('id="nav-cart-badge"', false);
        $resWithItems->assertSee('3');
    }

    /**
     * Skenario Tambahan: Peringatan stok jika stok toko berkurang lebih kecil dari jumlah di keranjang.
     */
    public function test_peringatan_stok_jika_stok_toko_berkurang_dan_checkout_nonaktif(): void
    {
        $user = User::factory()->create();
        [$product, $variant, $location, $stock] = $this->createProductWithStock('Baju Langka', 50000, 5);

        // User menambahkan 5 pcs ke keranjang saat stok 5
        $item = CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 5,
        ]);

        // Stok toko berkurang menjadi 2 (misal dibeli pelanggan offline / admin edit stok)
        $stock->update(['quantity' => 2]);

        $response = $this->actingAs($user)->get(route('keranjang.index'));

        $response->assertStatus(200);
        $response->assertSee('Sisa 2 pcs');
        $response->assertSee('Checkout Tidak Tersedia');
        $response->assertDontSee('Lanjut ke Checkout');
    }
}

