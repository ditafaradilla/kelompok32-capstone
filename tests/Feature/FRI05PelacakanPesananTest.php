<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FRI05PelacakanPesananTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithStock(string $name = 'Baju Bayi Test', float $price = 50000, int $stockQty = 10, string $locType = 'toko'): array
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
            'warna'      => 'Kuning Lemon',
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

    private function createOrderForUser(User $user, string $status = Order::STATUS_MENUNGGU_PEMBAYARAN, int $qty = 2): array
    {
        [$product, $variant, $location, $stock] = $this->createProductWithStock('Romper Bayi Lucu', 75000, 10, 'toko');

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => 75000 * $qty,
            'alamat_pengiriman' => 'Jl. Anggrek No. 12, Jakarta Barat',
            'nama_penerima'     => $user->name,
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => $status,
        ]);

        $item = OrderItem::create([
            'order_id'           => $order->id,
            'product_variant_id' => $variant->id,
            'product_id'         => $product->id,
            'jumlah'             => $qty,
            'harga_satuan'       => 75000,
            'nama_produk'        => $product->name,
            'varian_label'       => $variant->variant_label,
        ]);

        $payment = Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 75000 * $qty,
            'status_pembayaran' => Payment::STATUS_BELUM_BAYAR,
        ]);

        $order->recordInitialHistory();

        return [$order, $item, $variant, $stock, $location];
    }

    /**
     * Skenario 1: Setiap transisi status yang valid berjalan sukses dan dicatat di riwayat.
     */
    public function test_skenario_1_setiap_transisi_status_valid_berjalan_sukses(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$order] = $this->createOrderForUser($user, Order::STATUS_MENUNGGU_PEMBAYARAN);

        // 1. Transisi: menunggu_pembayaran -> menunggu_verifikasi
        $order->changeStatus(Order::STATUS_MENUNGGU_VERIFIKASI, 'Bukti transfer diunggah', $user->id);
        $this->assertEquals(Order::STATUS_MENUNGGU_VERIFIKASI, $order->fresh()->status_pesanan);

        // 2. Transisi: menunggu_verifikasi -> diproses
        $order->changeStatus(Order::STATUS_DIPROSES, 'Pembayaran diverifikasi admin', $admin->id);
        $this->assertEquals(Order::STATUS_DIPROSES, $order->fresh()->status_pesanan);

        // 3. Transisi: diproses -> dikirim (dengan kurir dan nomor resi)
        $order->changeStatus(Order::STATUS_DIKIRIM, 'Paket dikirim kurir', $admin->id, 'SiCepat', 'SCP123456789');
        $this->assertEquals(Order::STATUS_DIKIRIM, $order->fresh()->status_pesanan);
        $this->assertEquals('SiCepat', $order->fresh()->kurir);
        $this->assertEquals('SCP123456789', $order->fresh()->nomor_resi);

        // 4. Transisi: dikirim -> selesai
        $order->changeStatus(Order::STATUS_SELESAI, 'Pesanan selesai diterima pelanggan', $user->id);
        $this->assertEquals(Order::STATUS_SELESAI, $order->fresh()->status_pesanan);

        // Riwayat harus mencatat 5 tahapan (1 inisialisasi + 4 transisi)
        $this->assertEquals(5, $order->histories()->count());
    }

    /**
     * Skenario 2: Transisi status yang tidak valid ditolak dengan DomainException.
     */
    public function test_skenario_2_transisi_tidak_valid_ditolak(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$order] = $this->createOrderForUser($user, Order::STATUS_DIPROSES);

        // Transisi tidak sah: diproses -> selesai (harus melalui dikirim terlebih dahulu)
        $this->expectException(\DomainException::class);
        $order->changeStatus(Order::STATUS_SELESAI, 'Langsung selesai', $admin->id);
    }

    /**
     * Skenario 2b: Admin controller menolak transisi tidak sah dengan pesan error.
     */
    public function test_skenario_2b_admin_controller_menolak_transisi_tidak_sah(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$order] = $this->createOrderForUser($user, Order::STATUS_DIPROSES);

        $response = $this->actingAs($admin)->patch(route('admin.pesanan.status', $order), [
            'status' => Order::STATUS_SELESAI, // tidak ada di allowed statuses diproses
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertEquals(Order::STATUS_DIPROSES, $order->fresh()->status_pesanan);
    }

    /**
     * Skenario 3: Riwayat status tercatat secara akurat beserta data pengubahnya (changed_by).
     */
    public function test_skenario_3_riwayat_tercatat_dengan_pengubah(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$order] = $this->createOrderForUser($user, Order::STATUS_DIPROSES);

        // Admin mengirimkan barang
        $this->actingAs($admin)->patch(route('admin.pesanan.status', $order), [
            'status'     => Order::STATUS_DIKIRIM,
            'kurir'      => 'JNE Express',
            'nomor_resi' => 'JNE001928374',
            'catatan'    => 'Paket telah diserahkan ke counter JNE',
        ]);

        $lastHistory = $order->histories()->reorder()->latest('id')->first();
        $this->assertNotNull($lastHistory);
        $this->assertEquals(Order::STATUS_DIKIRIM, $lastHistory->status);
        $this->assertEquals('Paket telah diserahkan ke counter JNE', $lastHistory->catatan);
        $this->assertEquals($admin->id, $lastHistory->changed_by);
        $this->assertEquals($admin->name, $lastHistory->changer->name);
    }

    /**
     * Skenario 4: Perubahan status ke 'dikirim' tanpa kurir atau resi ditolak oleh validasi.
     */
    public function test_skenario_4_dikirim_tanpa_resi_ditolak(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$order] = $this->createOrderForUser($user, Order::STATUS_DIPROSES);

        // Coba ubah ke 'dikirim' tanpa mengisi kurir & nomor_resi
        $response = $this->actingAs($admin)->patch(route('admin.pesanan.status', $order), [
            'status'     => Order::STATUS_DIKIRIM,
            'kurir'      => '',
            'nomor_resi' => '',
        ]);

        $response->assertSessionHasErrors(['kurir', 'nomor_resi']);
        $this->assertEquals(Order::STATUS_DIPROSES, $order->fresh()->status_pesanan);
    }

    /**
     * Skenario 5: Pelanggan tidak dapat mengakses pelacakan pesanan milik pelanggan lain (403 Forbidden).
     */
    public function test_skenario_5_pelanggan_tidak_bisa_mengakses_pelacakan_pesanan_orang_lain(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        [$orderA] = $this->createOrderForUser($userA, Order::STATUS_DIKIRIM);

        // User B mencoba membuka halaman pelacakan pesanan User A
        $this->actingAs($userB)->get(route('pesanan.lacak', $orderA))->assertForbidden();

        // User B mencoba konfirmasi 'pesanan diterima' pesanan User A
        $this->actingAs($userB)->post(route('pesanan.terima', $orderA))->assertForbidden();
    }

    /**
     * Skenario 6: Tombol 'Pesanan Diterima' hanya berfungsi saat status 'dikirim'.
     */
    public function test_skenario_6_tombol_pesanan_diterima_hanya_saat_dikirim(): void
    {
        $user = User::factory()->create();

        // 1. Saat masih status 'diproses', tombol tidak boleh memproses penyelesaian
        [$orderDiproses] = $this->createOrderForUser($user, Order::STATUS_DIPROSES);

        $resDiproses = $this->actingAs($user)->post(route('pesanan.terima', $orderDiproses));
        $resDiproses->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_DIPROSES, $orderDiproses->fresh()->status_pesanan);

        // 2. Saat status 'dikirim', tombol berhasil menyelesaikan pesanan
        [$orderDikirim] = $this->createOrderForUser($user, Order::STATUS_DIKIRIM);

        // Di halaman pelacakan, tombol Pesanan Diterima terlihat
        $pageResponse = $this->actingAs($user)->get(route('pesanan.lacak', $orderDikirim));
        $pageResponse->assertSee('Pesanan Diterima');

        // Klik konfirmasi terima pesanan
        $resDikirim = $this->actingAs($user)->post(route('pesanan.terima', $orderDikirim));
        $resDikirim->assertSessionHas('success');

        $this->assertEquals(Order::STATUS_SELESAI, $orderDikirim->fresh()->status_pesanan);
        $this->assertEquals(Order::STATUS_SELESAI, $orderDikirim->histories()->reorder()->latest('id')->first()->status);
    }

    /**
     * Skenario 7: Pembatalan pesanan mengembalikan stok ke toko tepat sekali saja (idempotent).
     */
    public function test_skenario_7_pembatalan_mengembalikan_stok_tepat_sekali(): void
    {
        $user = User::factory()->create();

        // Buat pesanan dengan qty = 2, stok toko awal = 10 -> dipotong jadi 8
        [$order, $item, $variant, $stock] = $this->createOrderForUser($user, Order::STATUS_MENUNGGU_PEMBAYARAN, 2);

        // Simulasikan stok terpotong saat checkout (10 - 2 = 8)
        $stock->quantity = 8;
        $stock->save();

        // Batalkan pesanan
        $order->changeStatus(Order::STATUS_DIBATALKAN, 'Dibatalkan oleh pelanggan', $user->id);

        $this->assertEquals(Order::STATUS_DIBATALKAN, $order->fresh()->status_pesanan);
        // Stok toko kembali bertambah 2 menjadi 10
        $this->assertEquals(10, $stock->fresh()->quantity);

        // Upaya pembatalan ulang pada pesanan yang sudah dibatalkan ditolak oleh state machine
        try {
            $order->changeStatus(Order::STATUS_DIBATALKAN, 'Batal kedua kali', $user->id);
            $this->fail('Harus melempar DomainException jika membatalkan pesanan yang sudah dibatalkan');
        } catch (\DomainException $e) {
            // Sukses ditolak
        }

        // Stok tetap 10 (tidak bertambah dua kali)
        $this->assertEquals(10, $stock->fresh()->quantity);
    }

    /**
     * Skenario 8: Administrator dapat menyelesaikan pesanan langsung dari status 'dikirim'.
     */
    public function test_skenario_8_admin_dapat_menyelesaikan_pesanan_dari_status_dikirim(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        [$order] = $this->createOrderForUser($user, Order::STATUS_DIKIRIM);

        $response = $this->actingAs($admin)->patch(route('admin.pesanan.status', $order), [
            'status'  => Order::STATUS_SELESAI,
            'catatan' => 'Pesanan dikonfirmasi selesai oleh admin via resi kurir',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(Order::STATUS_SELESAI, $order->fresh()->status_pesanan);
        $this->assertEquals('Pesanan dikonfirmasi selesai oleh admin via resi kurir', $order->histories()->reorder()->latest('id')->first()->catatan);
    }
}
