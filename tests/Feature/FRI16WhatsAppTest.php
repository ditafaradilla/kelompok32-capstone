<?php

namespace Tests\Feature;

use App\Models\Baby;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FRI16WhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithStock(string $name = 'Baju Bayi Katun'): array
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
            'price'      => 85000,
            'cost_price' => 45000,
            'min_stock'  => 2,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => $product->sku . '-VAR',
            'ukuran'     => '0-3M',
            'warna'      => 'Putih',
        ]);

        $stock = ProductStock::create([
            'product_variant_id' => $variant->id,
            'location_id'        => $location->id,
            'quantity'           => 10,
        ]);

        return [$product, $variant, $location, $stock];
    }

    private function createOrderForUser(User $user, Product $product, ProductVariant $variant): Order
    {
        $order = Order::create([
            'user_id'           => $user->id,
            'nomor_pesanan'     => Order::generateNomorPesanan(),
            'tanggal_pesanan'   => now(),
            'total_harga'       => $product->price,
            'alamat_pengiriman' => 'Jl. Test No. 123',
            'nama_penerima'     => $user->name,
            'telepon_penerima'  => '081234567890',
            'status_pesanan'    => Order::STATUS_DIPROSES,
        ]);

        OrderItem::create([
            'order_id'           => $order->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'nama_produk'        => $product->name,
            'varian_label'       => $variant->variant_label,
            'ukuran'             => $variant->ukuran,
            'warna'              => $variant->warna,
            'harga_satuan'       => $product->price,
            'jumlah'             => 1,
            'subtotal'           => $product->price,
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

    public function test_navbar_and_floating_button_displayed_when_whatsapp_configured(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);

        // Navbar WhatsApp link
        $response->assertSee('aria-label="Hubungi kami lewat WhatsApp"', false);
        $response->assertSee('https://wa.me/6281234567890', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);

        // Floating WhatsApp button
        $response->assertSee('fixed bottom-20 md:bottom-6 right-4 sm:right-6', false);
        $response->assertSee('#25D366', false);
    }

    public function test_footer_whatsapp_links_when_whatsapp_configured(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Bantuan & FAQ', false);
        $response->assertSee('Hubungi Kami');
        $response->assertSee('https://wa.me/6281234567890?text=' . rawurlencode('Halo Baby Corner, saya ingin bertanya.'), false);
    }

    public function test_product_detail_whatsapp_buttons_and_links(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Jumper Katun Bayi');

        $response = $this->actingAs($user)->get(route('produk.show', $product));

        $response->assertStatus(200);

        // Tombol tanya produk via WhatsApp
        $response->assertSee('Tanya produk ini via WhatsApp');
        $response->assertSee('https://wa.me/6281234567890?text=' . rawurlencode("Halo Baby Corner, saya ingin bertanya tentang produk Jumper Katun Bayi (" . route('produk.show', $product) . ")."), false);

        // Tombol konsultasi ukuran (tanpa profil bayi aktif)
        $response->assertSee('Ragu dengan ukuran? Tanya kami');
        $response->assertSee('https://wa.me/6281234567890?text=' . rawurlencode("Halo Baby Corner, ukuran apa yang cocok untuk Jumper Katun Bayi?"), false);
    }

    public function test_product_detail_size_message_incorporates_active_baby_data(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        $user = User::factory()->create();
        $baby = Baby::create([
            'user_id'       => $user->id,
            'nama_bayi'     => 'Baby Rara',
            'tanggal_lahir' => now()->subMonths(6)->toDateString(),
            'berat_badan'   => 7.5,
            'tinggi_badan'  => 68.0,
            'jenis_kelamin' => 'perempuan',
        ]);

        [$product, $variant] = $this->createProductWithStock('Dress Bayi Lucu');

        $expectedMsg = "Halo Baby Corner, bayi saya usia 6 bulan, berat 7.5 kg, tinggi 68 cm. Ukuran apa yang cocok untuk Dress Bayi Lucu?";

        $response = $this->actingAs($user)
            ->withSession(['active_baby_id' => $baby->id])
            ->get(route('produk.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Ragu dengan ukuran? Tanya kami');
        $response->assertSee('https://wa.me/6281234567890?text=' . rawurlencode($expectedMsg), false);
    }

    public function test_order_detail_and_tracking_whatsapp_buttons(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Setelan Bayi');
        $order = $this->createOrderForUser($user, $product, $variant);

        // Detail pesanan
        $responseShow = $this->actingAs($user)->get(route('pesanan.show', $order));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Tanya soal pesanan ini');
        $responseShow->assertSee('https://wa.me/6281234567890?text=' . rawurlencode("Halo Baby Corner, saya ingin menanyakan pesanan {$order->nomor_pesanan}."), false);

        // Pelacakan pesanan
        $responseLacak = $this->actingAs($user)->get(route('pesanan.lacak', $order));
        $responseLacak->assertStatus(200);
        $responseLacak->assertSee('Tanya soal pesanan ini');
        $responseLacak->assertSee('https://wa.me/6281234567890?text=' . rawurlencode("Halo Baby Corner, saya ingin menanyakan pesanan {$order->nomor_pesanan}."), false);
    }

    public function test_whatsapp_elements_hidden_cleanly_when_config_is_null_or_empty(): void
    {
        config(['shop.whatsapp' => null]);

        $user = User::factory()->create();
        [$product, $variant] = $this->createProductWithStock('Baju Bayi Polos');
        $order = $this->createOrderForUser($user, $product, $variant);

        // Dashboard
        $responseHome = $this->actingAs($user)->get(route('dashboard'));
        $responseHome->assertStatus(200);
        $responseHome->assertDontSee('aria-label="Hubungi kami lewat WhatsApp"', false);
        $responseHome->assertDontSee('https://wa.me', false);
        $responseHome->assertDontSee('#25D366', false);

        // Product detail
        $responseProduct = $this->actingAs($user)->get(route('produk.show', $product));
        $responseProduct->assertStatus(200);
        $responseProduct->assertDontSee('Tanya produk ini via WhatsApp');
        $responseProduct->assertDontSee('Ragu dengan ukuran? Tanya kami');
        $responseProduct->assertDontSee('https://wa.me', false);

        // Order detail & tracking
        $responseShow = $this->actingAs($user)->get(route('pesanan.show', $order));
        $responseShow->assertStatus(200);
        $responseShow->assertDontSee('Tanya soal pesanan ini');
        $responseShow->assertDontSee('https://wa.me', false);

        $responseLacak = $this->actingAs($user)->get(route('pesanan.lacak', $order));
        $responseLacak->assertStatus(200);
        $responseLacak->assertDontSee('Tanya soal pesanan ini');
        $responseLacak->assertDontSee('https://wa.me', false);
    }
}
