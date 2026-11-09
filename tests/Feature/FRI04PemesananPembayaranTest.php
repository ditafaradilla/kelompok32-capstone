<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FRI04PemesananPembayaranTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithStock(string $name = 'Setelan Bayi Test', float $price = 60000, int $stockQty = 10, string $locType = 'toko'): array
    {
        $location = Location::create([
            'name' => 'Toko Cabang ' . uniqid(),
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
            'warna'      => 'Biru Polos',
        ]);

        $stock = ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $location->id,
            'quantity'           => $stockQty,
        ]);

        return [$product, $variant, $location, $stock];
    }

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Skenario 1: Checkout sukses mengurangi stok toko, mencatat order & payment, dan mengosongkan keranjang.
     */
    public function test_skenario_1_checkout_sukses_mengurangi_stok_dan_mengosongkan_keranjang(): void
    {
        $user = User::factory()->create();
        [$product, $variant, $location, $stock] = $this->createProductWithStock('Baju Bayi Katun', 50000, 10, 'toko');

        // Masukkan 2 pcs ke keranjang
        CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 2,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'alamat_pengiriman' => 'Jl. Boulevard Kelapa Gading No. 10, Jakarta Utara',
            'catatan'           => 'Mohon bungkus kado',
            'metode_pembayaran' => Payment::METODE_TRANSFER,
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('pesanan.pembayaran', $order));

        // Cek data order
        $this->assertEquals(100000, $order->total_harga);
        $this->assertEquals(Order::STATUS_MENUNGGU_PEMBAYARAN, $order->status_pesanan);
        $this->assertEquals('Bunda Sarah', $order->nama_penerima);

        // Cek item order
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals($variant->id, $item->product_variant_id);
        $this->assertEquals(2, $item->jumlah);
        $this->assertEquals(50000, $item->harga_satuan);
        $this->assertEquals(100000, $item->subtotal);

        // Cek payment record
        $this->assertNotNull($order->payment);
        $this->assertEquals(Payment::METODE_TRANSFER, $order->payment->metode_pembayaran);
        $this->assertEquals(100000, $order->payment->jumlah_bayar);
        $this->assertEquals(Payment::STATUS_BELUM_BAYAR, $order->payment->status_pembayaran);

        // Stok toko berkurang dari 10 menjadi 8
        $this->assertEquals(8, $stock->fresh()->quantity);

        // Keranjang belanja user menjadi kosong
        $this->assertEquals(0, CartItem::where('user_id', $user->id)->count());
    }

    /**
     * Skenario 2: Stok tidak cukup membatalkan seluruh proses checkout (rollback DB transaction).
     */
    public function test_skenario_2_stok_tidak_cukup_membatalkan_semuanya(): void
    {
        $user = User::factory()->create();
        [$product, $variant, $location, $stock] = $this->createProductWithStock('Jumper Bayi', 70000, 3, 'toko');

        // Keranjang meminta 5 pcs padahal stok toko hanya 3
        CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 5,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'alamat_pengiriman' => 'Jl. Kenanga No. 5',
            'metode_pembayaran' => Payment::METODE_TRANSFER,
        ]);

        $response->assertSessionHas('error');

        // Order dan Payment tidak boleh terbentuk
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, Payment::count());

        // Stok tidak berubah
        $this->assertEquals(3, $stock->fresh()->quantity);

        // Keranjang tetap utuh
        $this->assertEquals(1, CartItem::where('user_id', $user->id)->count());
    }

    /**
     * Skenario 3: Manipulasi harga dari request/browser diabaikan dan dihitung ulang dari database.
     */
    public function test_skenario_3_harga_dari_browser_diabaikan_dihitung_ulang_dari_database(): void
    {
        $user = User::factory()->create();
        [$product, $variant, $location, $stock] = $this->createProductWithStock('Bedong Bayi', 80000, 10, 'toko');

        CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 2, // Total seharusnya 160.000
        ]);

        // Manipulator mencoba mengirim total_harga atau harga_satuan Rp 1.000
        $this->actingAs($user)->post(route('checkout.store'), [
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'alamat_pengiriman' => 'Jl. Melati No. 8',
            'metode_pembayaran' => Payment::METODE_QRIS,
            'total_harga'       => 1000,
            'harga_satuan'      => 500,
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        // Total harga harus tetap 160.000 (2 * 80.000)
        $this->assertEquals(160000, $order->total_harga);
        $this->assertEquals(80000, $order->items->first()->harga_satuan);
        $this->assertEquals(160000, $order->payment->jumlah_bayar);
    }

    /**
     * Skenario 4: Validasi form checkout mewajibkan nama, nomor telepon, alamat, dan metode pembayaran valid.
     */
    public function test_skenario_4_validasi_form_checkout(): void
    {
        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Topi Bayi', 25000, 10, 'toko');

        CartItem::create([
            'user_id'            => $user->id,
            'product_variant_id' => $variant->id,
            'jumlah'             => 1,
        ]);

        // Kirim form kosong
        $response = $this->actingAs($user)->post(route('checkout.store'), []);
        $response->assertSessionHasErrors(['nama_penerima', 'telepon_penerima', 'alamat_pengiriman', 'metode_pembayaran']);

        // Kirim metode pembayaran tidak valid
        $resInvalidMethod = $this->actingAs($user)->post(route('checkout.store'), [
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'alamat_pengiriman' => 'Jl. Flamboyan No. 1',
            'metode_pembayaran' => 'crypto_currency',
        ]);
        $resInvalidMethod->assertSessionHasErrors(['metode_pembayaran']);
    }

    /**
     * Skenario 5: Pelanggan mengunggah file bukti pembayaran, mengubah status menjadi menunggu verifikasi.
     */
    public function test_skenario_5_unggah_bukti_pembayaran(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Sarung Tangan Bayi', 20000, 10, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => 20000,
            'alamat_pengiriman' => 'Alamat Uji',
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        $payment = Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 20000,
            'status_pembayaran' => Payment::STATUS_BELUM_BAYAR,
        ]);

        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('pesanan.uploadBukti', $order), [
            'bukti_bayar' => $file,
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $payment->refresh();

        $this->assertEquals(Order::STATUS_MENUNGGU_VERIFIKASI, $order->status_pesanan);
        $this->assertEquals(Payment::STATUS_MENUNGGU_VERIFIKASI, $payment->status_pembayaran);
        $this->assertNotNull($payment->bukti_bayar);
        $this->assertNotNull($payment->tanggal_bayar);
        Storage::disk('public')->assertExists($payment->bukti_bayar);
    }

    /**
     * Skenario 6: Pembatalan pesanan saat menunggu pembayaran mengembalikan stok ke toko.
     */
    public function test_skenario_6_pembatalan_pesanan_mengembalikan_stok_ke_toko(): void
    {
        $user = User::factory()->create();
        [$product, $variant, $location, $stock] = $this->createProductWithStock('Baju Hangat Bayi', 65000, 8, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => 130000,
            'alamat_pengiriman' => 'Alamat Uji',
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        OrderItem::create([
            'order_id'           => $order->id,
            'product_variant_id' => $variant->id,
            'product_id'         => $product->id,
            'jumlah'             => 2,
            'harga_satuan'       => 65000,
            'nama_produk'        => $product->name,
            'varian_label'       => $variant->variant_label,
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 130000,
            'status_pembayaran' => Payment::STATUS_BELUM_BAYAR,
        ]);

        // Batalkan pesanan
        $response = $this->actingAs($user)->post(route('pesanan.cancel', $order));
        $response->assertSessionHas('success');

        // Status pesanan menjadi dibatalkan
        $this->assertEquals(Order::STATUS_DIBATALKAN, $order->fresh()->status_pesanan);
        $this->assertEquals(Payment::STATUS_DITOLAK, $order->fresh()->payment->status_pembayaran);

        // Stok toko bertambah kembali dari 8 menjadi 10
        $this->assertEquals(10, $stock->fresh()->quantity);

        // Pesanan yang sudah diproses tidak boleh dibatalkan oleh pelanggan
        $order->status_pesanan = Order::STATUS_DIPROSES;
        $order->save();

        $resCannotCancel = $this->actingAs($user)->post(route('pesanan.cancel', $order));
        $resCannotCancel->assertSessionHas('error');
        $this->assertEquals(10, $stock->fresh()->quantity);
    }

    /**
     * Skenario 7: User lain tidak bisa melihat atau mengelola pesanan milik user lain (403 Forbidden).
     */
    public function test_skenario_7_user_lain_tidak_bisa_melihat_atau_mengelola_pesanan(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        [$product, $variant] = $this->createProductWithStock('Romper Bayi', 45000, 10, 'toko');

        $orderA = Order::create([
            'user_id'           => $userA->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => 45000,
            'alamat_pengiriman' => 'Alamat A',
            'nama_penerima'     => 'User A',
            'telepon_penerima'  => '08111111111',
            'status_pesanan'    => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        Payment::create([
            'order_id'          => $orderA->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 45000,
            'status_pembayaran' => Payment::STATUS_BELUM_BAYAR,
        ]);

        // User B mencoba membuka show order A
        $this->actingAs($userB)->get(route('pesanan.show', $orderA))->assertForbidden();

        // User B mencoba membuka halaman pembayaran order A
        $this->actingAs($userB)->get(route('pesanan.pembayaran', $orderA))->assertForbidden();

        // User B mencoba upload bukti bayar order A
        $this->actingAs($userB)->post(route('pesanan.uploadBukti', $orderA), [
            'bukti_bayar' => UploadedFile::fake()->create('fake.jpg', 100, 'image/jpeg'),
        ])->assertForbidden();

        // User B mencoba membatalkan order A
        $this->actingAs($userB)->post(route('pesanan.cancel', $orderA))->assertForbidden();
    }

    /**
     * Skenario 8: Verifikasi admin mengubah status ke diproses dan mencatat transaksi pemasukan.
     */
    public function test_skenario_8_verifikasi_admin_membuat_tepat_satu_transaksi_income(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$product, $variant] = $this->createProductWithStock('Jaket Bayi Lucu', 120000, 10, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => 'BC-20261110-0099',
            'tanggal_pesanan'   => now(),
            'total_harga'       => 120000,
            'alamat_pengiriman' => 'Jl. Cemara No. 12',
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_QRIS,
            'jumlah_bayar'      => 120000,
            'status_pembayaran' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'bukti_bayar'       => 'payment-proofs/sample.jpg',
            'tanggal_bayar'     => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pesanan.verify', $order));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_DIPROSES, $order->status_pesanan);
        $this->assertEquals(Payment::STATUS_LUNAS, $order->payment->status_pembayaran);

        // Cek pencatatan Transaksi Pembukuan
        $transaction = Transaction::where('reference', $order->nomor_pesanan)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(Transaction::TYPE_INCOME, $transaction->type);
        $this->assertEquals('Penjualan Online', $transaction->category);
        $this->assertEquals(120000, $transaction->amount);
        $this->assertEquals('QRIS', $transaction->payment_method);
        $this->assertEquals(Transaction::STATUS_PAID, $transaction->payment_status);
    }

    /**
     * Skenario 9: Idempotensi verifikasi admin - memanggil verifikasi berulang kali tidak menggandakan transaksi.
     */
    public function test_skenario_9_verifikasi_dua_kali_tidak_menggandakan_transaksi_idempotent(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$product, $variant] = $this->createProductWithStock('Sepatu Rajut Bayi', 55000, 10, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => 'BC-20261110-0088',
            'tanggal_pesanan'   => now(),
            'total_harga'       => 55000,
            'alamat_pengiriman' => 'Alamat Tes',
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 55000,
            'status_pembayaran' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'bukti_bayar'       => 'payment-proofs/sample.jpg',
            'tanggal_bayar'     => now(),
        ]);

        // Verifikasi pertama
        $this->actingAs($admin)->post(route('admin.pesanan.verify', $order));
        $this->assertEquals(1, Transaction::where('reference', $order->nomor_pesanan)->count());

        // Simulasi jika dipanggil lagi
        $order->payment->status_pembayaran = Payment::STATUS_MENUNGGU_VERIFIKASI;
        $order->payment->save();
        $this->actingAs($admin)->post(route('admin.pesanan.verify', $order));

        // Transaksi tetap hanya 1 (tidak duplikat)
        $this->assertEquals(1, Transaction::where('reference', $order->nomor_pesanan)->count());
    }

    /**
     * Skenario 10: Non-admin tidak memiliki akses ke rute admin pesanan.
     */
    public function test_skenario_10_non_admin_tidak_bisa_membuka_rute_admin_pesanan(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        [$product, $variant] = $this->createProductWithStock('Kaos Kaki Bayi', 15000, 10, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => 15000,
            'alamat_pengiriman' => 'Alamat Tes',
            'nama_penerima'     => 'User Reguler',
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        $this->actingAs($user)->get(route('admin.pesanan.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.pesanan.show', $order))->assertForbidden();
        $this->actingAs($user)->post(route('admin.pesanan.verify', $order))->assertForbidden();
        $this->actingAs($user)->post(route('admin.pesanan.reject', $order))->assertForbidden();
    }

    /**
     * Skenario 11: Admin menolak bukti pembayaran dan memberikan catatan penolakan.
     */
    public function test_skenario_11_admin_menolak_bukti_pembayaran(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$product, $variant] = $this->createProductWithStock('Celemek Bayi', 18000, 10, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => 18000,
            'alamat_pengiriman' => 'Alamat Tes',
            'nama_penerima'     => 'Bunda Sarah',
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 18000,
            'status_pembayaran' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'bukti_bayar'       => 'payment-proofs/sample.jpg',
            'tanggal_bayar'     => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pesanan.reject', $order), [
            'catatan_admin' => 'Foto bukti transfer buram dan nominal tidak terlihat jelas.',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_MENUNGGU_PEMBAYARAN, $order->status_pesanan);
        $this->assertEquals(Payment::STATUS_DITOLAK, $order->payment->status_pembayaran);
        $this->assertEquals('Foto bukti transfer buram dan nominal tidak terlihat jelas.', $order->payment->catatan_admin);
    }
}
