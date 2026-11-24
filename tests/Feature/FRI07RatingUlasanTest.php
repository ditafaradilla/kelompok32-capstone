<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FRI07RatingUlasanTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithStock(string $name = 'Baju Bayi Katun', float $price = 75000): array
    {
        $location = Location::create([
            'name' => 'Toko Utama ' . uniqid(),
            'code' => 'TKO-' . rand(100, 999),
            'type' => 'toko',
        ]);

        $product = Product::create([
            'name'       => $name,
            'sku'        => 'BC-TEST-' . uniqid(),
            'category'   => 'Pakaian',
            'price'      => $price,
            'cost_price' => $price * 0.5,
            'min_stock'  => 2,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => $product->sku . '-VAR',
            'ukuran'     => '0-3M',
            'warna'      => 'Putih Bersih',
        ]);

        $stock = ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $location->id,
            'quantity'           => 10,
        ]);

        return [$product, $variant, $location, $stock];
    }

    private function createOrderForUser(User $user, Product $product, ProductVariant $variant, string $status = Order::STATUS_SELESAI): Order
    {
        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => $product->price,
            'alamat_pengiriman' => 'Jl. Mawar No. 10, Jakarta',
            'nama_penerima'     => $user->name,
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => $status,
        ]);

        OrderItem::create([
            'order_id'           => $order->id,
            'product_variant_id' => $variant->id,
            'product_id'         => $product->id,
            'jumlah'             => 1,
            'harga_satuan'       => $product->price,
            'nama_produk'        => $product->name,
            'varian_label'       => $variant->variant_label,
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => $product->price,
            'status_pembayaran' => Payment::STATUS_LUNAS,
        ]);

        $order->recordInitialHistory();

        return $order;
    }

    /**
     * Skenario 1: User tanpa pesanan selesai tidak bisa mengulas produk (403 Forbidden).
     */
    public function test_skenario_1_user_tanpa_pesanan_selesai_tidak_bisa_mengulas(): void
    {
        $user = User::factory()->create();
        [$product] = $this->createProductWithStock();

        // 1. Membuka form create ulasan ditolak 403
        $this->actingAs($user)->get(route('ulasan.create', $product))->assertForbidden();

        // 2. Submit POST ulasan ditolak 403
        $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 5,
            'ulasan' => 'Mencoba mengulas tanpa membeli.',
        ])->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Skenario 2: User dengan pesanan yang belum selesai (mis. 'diproses' / 'dikirim') tidak bisa mengulas.
     */
    public function test_skenario_2_user_dengan_pesanan_belum_selesai_tidak_bisa_mengulas(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock();

        // Buat pesanan status 'diproses'
        $this->createOrderForUser($user, $product, $variant, Order::STATUS_DIPROSES);

        $this->actingAs($user)->get(route('ulasan.create', $product))->assertForbidden();
        $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 5,
            'ulasan' => 'Barang belum sampai.',
        ])->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Skenario 3: User dengan pesanan selesai berhasil membuat ulasan dan rating.
     */
    public function test_skenario_3_user_dengan_pesanan_selesai_berhasil_membuat_ulasan(): void
    {
        $user = User::factory()->create(['name' => 'Bunda Sarah']);
        [$product, $variant] = $this->createProductWithStock('Romper Bayi Lucu');

        $this->createOrderForUser($user, $product, $variant, Order::STATUS_SELESAI);

        // Akses halaman form ulasan sukses 200
        $resForm = $this->actingAs($user)->get(route('ulasan.create', $product));
        $resForm->assertOk();
        $resForm->assertSee('Romper Bayi Lucu');

        // Submit ulasan
        $response = $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 5,
            'ulasan' => 'Bahan sangat adem dan nyaman untuk bayi baru lahir.',
        ]);

        $response->assertRedirect(route('produk.show', $product));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'ulasan'     => 'Bahan sangat adem dan nyaman untuk bayi baru lahir.',
        ]);
    }

    /**
     * Skenario 4: Ulasan kedua pada produk yang sama ditolak / diarahkan ke form ubah ulasan.
     */
    public function test_skenario_4_ulasan_kedua_pada_produk_yang_sama_ditolak_atau_diarahkan_ke_ubah(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock();

        $this->createOrderForUser($user, $product, $variant, Order::STATUS_SELESAI);

        // Ulasan pertama
        $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 4,
            'ulasan' => 'Ulasan pertama.',
        ]);

        $existingReview = Review::where('user_id', $user->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($existingReview);

        // Akses create lagi -> diarahkan ke edit
        $resCreateAgain = $this->actingAs($user)->get(route('ulasan.create', $product));
        $resCreateAgain->assertRedirect(route('ulasan.edit', $existingReview));

        // Submit store lagi -> diarahkan ke edit
        $resStoreAgain = $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 5,
            'ulasan' => 'Ulasan kedua.',
        ]);
        $resStoreAgain->assertRedirect(route('ulasan.edit', $existingReview));

        // Jumlah ulasan tetap tepat 1
        $this->assertEquals(1, Review::where('user_id', $user->id)->where('product_id', $product->id)->count());
    }

    /**
     * Skenario 5: Rating di luar 1-5 ditolak oleh validasi server.
     */
    public function test_skenario_5_rating_di_luar_1_sampai_5_ditolak(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock();

        $this->createOrderForUser($user, $product, $variant, Order::STATUS_SELESAI);

        // Rating = 0
        $resZero = $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 0,
        ]);
        $resZero->assertSessionHasErrors(['rating']);

        // Rating = 6
        $resSix = $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => 6,
        ]);
        $resSix->assertSessionHasErrors(['rating']);

        // Rating kosong
        $resNull = $this->actingAs($user)->post(route('ulasan.store', $product), [
            'rating' => '',
        ]);
        $resNull->assertSessionHasErrors(['rating']);
    }

    /**
     * Skenario 6: Ubah dan hapus ulasan hanya dapat dilakukan oleh pemilik ulasan.
     */
    public function test_skenario_6_ubah_dan_hapus_hanya_oleh_pemilik(): void
    {
        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);
        [$product, $variant] = $this->createProductWithStock();

        $this->createOrderForUser($userA, $product, $variant, Order::STATUS_SELESAI);
        $this->createOrderForUser($userB, $product, $variant, Order::STATUS_SELESAI);

        // User A membuat ulasan
        $reviewA = Review::create([
            'user_id'    => $userA->id,
            'product_id' => $product->id,
            'rating'     => 4,
            'ulasan'     => 'Ulasan original dari user A.',
        ]);

        // User B mencoba membuka form edit ulasan User A -> 403 Forbidden
        $this->actingAs($userB)->get(route('ulasan.edit', $reviewA))->assertForbidden();

        // User B mencoba update ulasan User A -> 403 Forbidden
        $this->actingAs($userB)->put(route('ulasan.update', $reviewA), [
            'rating' => 1,
            'ulasan' => 'Diubah oleh pembajak!',
        ])->assertForbidden();

        // User B mencoba hapus ulasan User A -> 403 Forbidden
        $this->actingAs($userB)->delete(route('ulasan.destroy', $reviewA))->assertForbidden();

        // Ulasan User A tetap utuh
        $this->assertEquals(4, $reviewA->fresh()->rating);

        // User A mengubah ulasannya sendiri -> Sukses
        $resUpdate = $this->actingAs($userA)->put(route('ulasan.update', $reviewA), [
            'rating' => 5,
            'ulasan' => 'Diperbarui jadi bintang 5 karena pelayanan bagus!',
        ]);
        $resUpdate->assertRedirect(route('produk.show', $product));
        $this->assertEquals(5, $reviewA->fresh()->rating);
        $this->assertEquals('Diperbarui jadi bintang 5 karena pelayanan bagus!', $reviewA->fresh()->ulasan);

        // User A menghapus ulasannya sendiri -> Sukses
        $resDelete = $this->actingAs($userA)->delete(route('ulasan.destroy', $reviewA));
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $reviewA->id]);
    }

    /**
     * Skenario 7: Rata-rata dan jumlah ulasan di halaman detail produk dan katalog dihitung dengan benar.
     */
    public function test_skenario_7_rata_rata_dan_jumlah_di_detail_dan_katalog_benar(): void
    {
        $user1 = User::factory()->create(['name' => 'Sarah Wijaya']);
        $user2 = User::factory()->create(['name' => 'Dian Kartika']);
        $user3 = User::factory()->create(['name' => 'Rina Melati']);

        [$product, $variant] = $this->createProductWithStock('Setelan Piyama Bayi');

        // Buat ulasan: rating 5, 4, 5 -> rata-rata = (5 + 4 + 5) / 3 = 4.666... dibulatkan 4.7
        Review::create(['user_id' => $user1->id, 'product_id' => $product->id, 'rating' => 5, 'ulasan' => 'Bagus sekali']);
        Review::create(['user_id' => $user2->id, 'product_id' => $product->id, 'rating' => 4, 'ulasan' => 'Bahan oke']);
        Review::create(['user_id' => $user3->id, 'product_id' => $product->id, 'rating' => 5, 'ulasan' => 'Puas']);

        // 1. Cek di halaman detail produk
        $resDetail = $this->actingAs($user1)->get(route('produk.show', $product));
        $resDetail->assertOk();
        $resDetail->assertSee('4.7');
        $resDetail->assertSee('3 Ulasan Pembeli');

        // 2. Cek di katalog
        $resKatalog = $this->actingAs($user1)->get(route('produk.index'));
        $resKatalog->assertOk();
        $resKatalog->assertSee('4.7');
        $resKatalog->assertSee('(3)');

        // 3. Cek pengurutan rating tertinggi
        [$productLow] = $this->createProductWithStock('Produk Rating Rendah');
        Review::create(['user_id' => $user1->id, 'product_id' => $productLow->id, 'rating' => 2, 'ulasan' => 'Kurang']);

        $resSort = $this->actingAs($user1)->get(route('produk.index', ['sort' => 'rating_tertinggi']));
        $resSort->assertOk();
        // Produk dengan rating 4.7 harus muncul sebelum produk dengan rating 2.0
        $content = $resSort->getContent();
        $posHigh = strpos($content, 'Setelan Piyama Bayi');
        $posLow  = strpos($content, 'Produk Rating Rendah');
        $this->assertTrue($posHigh < $posLow, 'Produk rating tertinggi harus muncul lebih dahulu');
    }

    /**
     * Skenario 8: Penghapusan produk oleh admin menghapus ulasan secara cascade tanpa error.
     */
    public function test_skenario_8_penghapusan_produk_cascade_menghapus_ulasan(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock();

        $review = Review::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'ulasan'     => 'Ulasan produk yang akan dihapus.',
        ]);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);

        // Hapus produk
        $product->delete();

        // Ulasan harus terhapus cascade
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * Skenario 9: Nama pelanggan disamarkan pada tampilan ulasan (mis. 'Sarah W.').
     */
    public function test_skenario_9_nama_pelanggan_disamarkan(): void
    {
        $author = User::factory()->create(['name' => 'Sarah Wijaya']);
        $viewer = User::factory()->create(['name' => 'Bunda Penonton']);
        [$product] = $this->createProductWithStock();

        Review::create([
            'user_id'    => $author->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'ulasan'     => 'Kualitas kain katun sangat lembut.',
        ]);

        $response = $this->actingAs($viewer)->get(route('produk.show', $product));
        $response->assertOk();

        // Harus melihat 'Sarah W.'
        $response->assertSee('Sarah W.');
        // Tidak menampilkan nama lengkap 'Sarah Wijaya' pada kartu ulasan
        $response->assertDontSee('Sarah Wijaya');
    }

    /**
     * Skenario 10: Tombol 'Beri Ulasan' pada detail pesanan selesai berubah menjadi 'Ubah Ulasan' jika sudah diulas.
     */
    public function test_skenario_10_tombol_ulasan_di_pesanan_selesai_berubah_saat_sudah_diulas(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock();

        $order = $this->createOrderForUser($user, $product, $variant, Order::STATUS_SELESAI);

        // Sebelum mengulas: tampil 'Beri Ulasan'
        $resBefore = $this->actingAs($user)->get(route('pesanan.show', $order));
        $resBefore->assertOk();
        $resBefore->assertSee('Beri Ulasan');
        $resBefore->assertSee(route('ulasan.create', $product));

        // Beri ulasan
        $review = Review::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'ulasan'     => 'Sudah diulas.',
        ]);

        // Sesudah mengulas: tampil 'Ubah Ulasan'
        $resAfter = $this->actingAs($user)->get(route('pesanan.show', $order));
        $resAfter->assertOk();
        $resAfter->assertSee('Ubah Ulasan');
        $resAfter->assertSee(route('ulasan.edit', $review));
    }
}
