<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FRI06RiwayatPembelianTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithStock(string $name = 'Baju Bayi Test', float $price = 50000): array
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
            'warna'      => 'Kuning Lemon',
        ]);

        $stock = ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $location->id,
            'quantity'           => 10,
        ]);

        return [$product, $variant, $location, $stock];
    }

    private function createOrderForUser(User $user, string $status = Order::STATUS_MENUNGGU_PEMBAYARAN, int $qty = 1, ?string $nomor = null, ?Carbon $date = null): Order
    {
        [$product, $variant] = $this->createProductWithStock('Romper Bayi Katun', 75000);

        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => $nomor ?: Order::generateNomorPesanan(),
            'tanggal_pesanan'   => $date ?: now(),
            'total_harga'       => 75000 * $qty,
            'alamat_pengiriman' => 'Jl. Mawar No. 10, Jakarta',
            'nama_penerima'     => $user->name,
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => $status,
        ]);

        OrderItem::create([
            'order_id'           => $order->id,
            'product_variant_id' => $variant->id,
            'product_id'         => $product->id,
            'jumlah'             => $qty,
            'harga_satuan'       => 75000,
            'nama_produk'        => $product->name,
            'varian_label'       => $variant->variant_label,
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'metode_pembayaran' => Payment::METODE_TRANSFER,
            'jumlah_bayar'      => 75000 * $qty,
            'status_pembayaran' => in_array($status, [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM, Order::STATUS_SELESAI])
                ? Payment::STATUS_LUNAS
                : Payment::STATUS_BELUM_BAYAR,
        ]);

        $order->recordInitialHistory();

        return $order;
    }

    /**
     * Skenario 1: Tamu (guest) yang mengakses riwayat pembelian diarahkan ke login.
     */
    public function test_skenario_1_tamu_diarahkan_ke_login(): void
    {
        $response = $this->get(route('riwayat.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Skenario 2: User hanya melihat daftar pesanannya sendiri (tidak melihat pesanan user lain).
     */
    public function test_skenario_2_user_hanya_melihat_pesanannya_sendiri(): void
    {
        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);

        $orderA1 = $this->createOrderForUser($userA, Order::STATUS_MENUNGGU_PEMBAYARAN, 1, 'BC-ORD-A001');
        $orderA2 = $this->createOrderForUser($userA, Order::STATUS_DIPROSES, 2, 'BC-ORD-A002');
        $orderB  = $this->createOrderForUser($userB, Order::STATUS_DIPROSES, 1, 'BC-ORD-B001');

        $response = $this->actingAs($userA)->get(route('riwayat.index'));
        $response->assertOk();

        // Pesanan milik User A harus terlihat
        $response->assertSee('BC-ORD-A001');
        $response->assertSee('BC-ORD-A002');

        // Pesanan milik User B TIDAK boleh terlihat
        $response->assertDontSee('BC-ORD-B001');
    }

    /**
     * Skenario 3: Filter status pesanan menyaring daftar pesanan sesuai pilihan status.
     */
    public function test_skenario_3_filter_status_pesanan(): void
    {
        $user = User::factory()->create();

        $this->createOrderForUser($user, Order::STATUS_MENUNGGU_PEMBAYARAN, 1, 'BC-FLT-BAYAR');
        $this->createOrderForUser($user, Order::STATUS_DIPROSES, 1, 'BC-FLT-PROSES');
        $this->createOrderForUser($user, Order::STATUS_SELESAI, 1, 'BC-FLT-SELESAI');

        // Filter status 'diproses'
        $responseProses = $this->actingAs($user)->get(route('riwayat.index', ['status' => Order::STATUS_DIPROSES]));
        $responseProses->assertOk();
        $responseProses->assertSee('BC-FLT-PROSES');
        $responseProses->assertDontSee('BC-FLT-BAYAR');
        $responseProses->assertDontSee('BC-FLT-SELESAI');

        // Filter status 'selesai'
        $responseSelesai = $this->actingAs($user)->get(route('riwayat.index', ['status' => Order::STATUS_SELESAI]));
        $responseSelesai->assertOk();
        $responseSelesai->assertSee('BC-FLT-SELESAI');
        $responseSelesai->assertDontSee('BC-FLT-BAYAR');
        $responseSelesai->assertDontSee('BC-FLT-PROSES');
    }

    /**
     * Skenario 4: Pencarian nomor pesanan (q / search) menampilkan hasil yang cocok.
     */
    public function test_skenario_4_pencarian_nomor_pesanan(): void
    {
        $user = User::factory()->create();

        $this->createOrderForUser($user, Order::STATUS_DIPROSES, 1, 'BC-SEARCH-TARGET');
        $this->createOrderForUser($user, Order::STATUS_DIPROSES, 1, 'BC-OTHER-ORDER');

        $response = $this->actingAs($user)->get(route('riwayat.index', ['q' => 'TARGET']));
        $response->assertOk();
        $response->assertSee('BC-SEARCH-TARGET');
        $response->assertDontSee('BC-OTHER-ORDER');
    }

    /**
     * Skenario 5: Filter rentang tanggal menyaring pesanan sesuai tanggal pesanan.
     */
    public function test_skenario_5_filter_rentang_tanggal(): void
    {
        $user = User::factory()->create();

        $orderOld = $this->createOrderForUser(
            $user,
            Order::STATUS_SELESAI,
            1,
            'BC-DATE-OLD',
            Carbon::now()->subDays(10)
        );

        $orderNew = $this->createOrderForUser(
            $user,
            Order::STATUS_DIPROSES,
            1,
            'BC-DATE-NEW',
            Carbon::now()->subDays(1)
        );

        // Filter hanya dalam 3 hari terakhir
        $response = $this->actingAs($user)->get(route('riwayat.index', [
            'tanggal_mulai' => Carbon::now()->subDays(3)->toDateString(),
            'tanggal_akhir' => Carbon::now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('BC-DATE-NEW');
        $response->assertDontSee('BC-DATE-OLD');
    }

    /**
     * Skenario 6: Pagination bekerja 10 pesanan per halaman dan mempertahankan parameter query string.
     */
    public function test_skenario_6_pagination_dan_preserve_query_string(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 15; $i++) {
            $nomor = sprintf('BC-PAG-%03d', $i);
            $this->createOrderForUser($user, Order::STATUS_DIPROSES, 1, $nomor, Carbon::now()->subMinutes(16 - $i));
        }

        // Halaman 1: default 10 pesanan terbaru (15 down to 6)
        $response1 = $this->actingAs($user)->get(route('riwayat.index', ['status' => Order::STATUS_DIPROSES]));
        $response1->assertOk();
        $response1->assertSee('BC-PAG-015');
        $response1->assertSee('BC-PAG-006');
        $response1->assertDontSee('BC-PAG-005');

        // Parameter status harus dipreservasi di link pagination
        $response1->assertSee('status=diproses');

        // Halaman 2: 5 pesanan sisanya (005 down to 001)
        $response2 = $this->actingAs($user)->get(route('riwayat.index', ['status' => Order::STATUS_DIPROSES, 'page' => 2]));
        $response2->assertOk();
        $response2->assertSee('BC-PAG-005');
        $response2->assertSee('BC-PAG-001');
    }

    /**
     * Skenario 7: User lain mengakses detail pesanan ditolak dengan 403 Forbidden.
     */
    public function test_skenario_7_user_lain_mengakses_detail_pesanan_ditolak_403(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderA = $this->createOrderForUser($userA, Order::STATUS_DIPROSES);

        // User B mencoba membuka show order A
        $this->actingAs($userB)->get(route('pesanan.show', $orderA))->assertForbidden();

        // User A membuka show order miliknya sendiri -> 200 OK
        $this->actingAs($userA)->get(route('pesanan.show', $orderA))->assertOk();
    }

    /**
     * Skenario 8: Total belanja pada ringkasan HANYA menghitung pesanan diproses, dikirim, dan selesai (tidak menghitung dibatalkan atau belum bayar).
     */
    public function test_skenario_8_total_belanja_tidak_menghitung_pesanan_dibatalkan_atau_belum_bayar(): void
    {
        $user = User::factory()->create();

        // 1 order selesai = 100.000
        $this->createOrderForUser($user, Order::STATUS_SELESAI, 2); // 75.000 * 2 = 150.000
        // 1 order diproses = 75.000
        $this->createOrderForUser($user, Order::STATUS_DIPROSES, 1); // 75.000 * 1 = 75.000
        // 1 order dibatalkan = 75.000 * 3 = 225.000 (TIDAK BOLEH MASUK)
        $this->createOrderForUser($user, Order::STATUS_DIBATALKAN, 3);
        // 1 order menunggu pembayaran = 75.000 * 2 = 150.000 (TIDAK BOLEH MASUK)
        $this->createOrderForUser($user, Order::STATUS_MENUNGGU_PEMBAYARAN, 2);

        $response = $this->actingAs($user)->get(route('riwayat.index'));
        $response->assertOk();

        // Total Belanja = 150.000 + 75.000 = 225.000
        $response->assertViewHas('totalBelanja', 225000);
        $response->assertSee(rupiah(225000));

        // Total Pesanan = 4
        $response->assertViewHas('totalPesanan', 4);
    }

    /**
     * Skenario 9: Kartu pesanan 'menunggu_pembayaran' menampilkan tombol 'Bayar Sekarang'.
     */
    public function test_skenario_9_kartu_pesanan_menunggu_pembayaran_menampilkan_tombol_bayar_sekarang(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderForUser($user, Order::STATUS_MENUNGGU_PEMBAYARAN);

        $response = $this->actingAs($user)->get(route('riwayat.index'));
        $response->assertOk();
        $response->assertSee('Bayar Sekarang');
        $response->assertSee(route('pesanan.pembayaran', $order));
    }

    /**
     * Skenario 10: Halaman detail pesanan selesai menyediakan tautan 'Beri Ulasan' per item.
     */
    public function test_skenario_10_halaman_detail_pesanan_selesai_menyediakan_tautan_beri_ulasan(): void
    {
        $user = User::factory()->create();

        // 1. Pesanan selesai
        $orderSelesai = $this->createOrderForUser($user, Order::STATUS_SELESAI);
        $resSelesai = $this->actingAs($user)->get(route('pesanan.show', $orderSelesai));
        $resSelesai->assertOk();
        $resSelesai->assertSee('Beri Ulasan');

        // 2. Pesanan belum selesai (diproses) -> tidak ada tautan beri ulasan
        $orderDiproses = $this->createOrderForUser($user, Order::STATUS_DIPROSES);
        $resDiproses = $this->actingAs($user)->get(route('pesanan.show', $orderDiproses));
        $resDiproses->assertOk();
        $resDiproses->assertDontSee('Beri Ulasan');
    }

    /**
     * Skenario 11: State kosong tampil saat pelanggan belum memiliki riwayat pembelian.
     */
    public function test_skenario_11_state_kosong_ditampilkan_saat_belum_ada_pembelian(): void
    {
        $newUser = User::factory()->create();

        $response = $this->actingAs($newUser)->get(route('riwayat.index'));
        $response->assertOk();
        $response->assertSee('Belum ada pembelian');
        $response->assertSee(route('produk.index'));
    }

    /**
     * Skenario 12: Menu Riwayat Pembelian muncul di dropdown navbar dan breadcrumb terhubung ke riwayat.index.
     */
    public function test_skenario_12_breadcrumb_dan_menu_navbar_mengarah_ke_riwayat(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderForUser($user, Order::STATUS_DIPROSES);

        // Halaman dashboard memuat menu Riwayat Pembelian di navbar dropdown
        $resNavbar = $this->actingAs($user)->get(route('dashboard'));
        $resNavbar->assertOk();
        $resNavbar->assertSee('Riwayat Pembelian');
        $resNavbar->assertSee(route('riwayat.index'));

        // Halaman detail pesanan memiliki breadcrumb yang menuju riwayat.index
        $resShow = $this->actingAs($user)->get(route('pesanan.show', $order));
        $resShow->assertOk();
        $resShow->assertSee('Riwayat Pembelian');
        $resShow->assertSee(route('riwayat.index'));

        // Halaman pelacakan pesanan memiliki breadcrumb yang menuju riwayat.index
        $resLacak = $this->actingAs($user)->get(route('pesanan.lacak', $order));
        $resLacak->assertOk();
        $resLacak->assertSee('Riwayat Pembelian');
        $resLacak->assertSee(route('riwayat.index'));
    }
}

