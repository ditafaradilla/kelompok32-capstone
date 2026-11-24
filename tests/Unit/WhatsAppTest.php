<?php

namespace Tests\Unit;

use App\Models\Baby;
use App\Models\Order;
use App\Models\Product;
use App\Support\WhatsApp;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['shop.whatsapp' => '081234567890']);
    }

    public function test_number_normalization(): void
    {
        config(['shop.whatsapp' => '081234567890']);
        $this->assertEquals('6281234567890', WhatsApp::number());

        config(['shop.whatsapp' => '+62 812-3456-7890']);
        $this->assertEquals('6281234567890', WhatsApp::number());

        config(['shop.whatsapp' => '6281234567890']);
        $this->assertEquals('6281234567890', WhatsApp::number());

        config(['shop.whatsapp' => '81234567890']);
        $this->assertEquals('6281234567890', WhatsApp::number());

        config(['shop.whatsapp' => '+6281234567890']);
        $this->assertEquals('6281234567890', WhatsApp::number());

        config(['shop.whatsapp' => '0812-3456-789']);
        $this->assertEquals('628123456789', WhatsApp::number());

        // Format tidak valid / kosong
        config(['shop.whatsapp' => '']);
        $this->assertNull(WhatsApp::number());

        config(['shop.whatsapp' => null]);
        $this->assertNull(WhatsApp::number());

        config(['shop.whatsapp' => '   ']);
        $this->assertNull(WhatsApp::number());

        config(['shop.whatsapp' => '123']);
        $this->assertNull(WhatsApp::number());

        config(['shop.whatsapp' => '081234567890123456789']); // Terlalu panjang (>16 digit)
        $this->assertNull(WhatsApp::number());

        config(['shop.whatsapp' => 'hanya-huruf']);
        $this->assertNull(WhatsApp::number());
    }

    public function test_link_generation(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        // Tanpa pesan
        $this->assertEquals('https://wa.me/6281234567890', WhatsApp::link());
        $this->assertEquals('https://wa.me/6281234567890', WhatsApp::link(''));
        $this->assertEquals('https://wa.me/6281234567890', WhatsApp::link('   '));

        // Dengan pesan (di-encode menggunakan rawurlencode)
        $message = "Halo Baby Corner! Apa kabar?";
        $expected = 'https://wa.me/6281234567890?text=' . rawurlencode($message);
        $this->assertEquals($expected, WhatsApp::link($message));

        // Jika nomor null
        config(['shop.whatsapp' => null]);
        $this->assertNull(WhatsApp::link());
        $this->assertNull(WhatsApp::link($message));
    }

    public function test_general_message_and_link(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        $msg = WhatsApp::generalMessage();
        $this->assertEquals('Halo Baby Corner, saya ingin bertanya.', $msg);

        $link = WhatsApp::generalLink();
        $this->assertEquals('https://wa.me/6281234567890?text=' . rawurlencode($msg), $link);
    }

    public function test_product_message_and_link(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        // Dengan string
        $msg = WhatsApp::productMessage('Baju Tidur Organik', 'https://example.com/produk/1');
        $this->assertEquals('Halo Baby Corner, saya ingin bertanya tentang produk Baju Tidur Organik (https://example.com/produk/1).', $msg);

        $msgWithoutUrl = WhatsApp::productMessage('Baju Tidur Organik');
        $this->assertEquals('Halo Baby Corner, saya ingin bertanya tentang produk Baju Tidur Organik.', $msgWithoutUrl);

        // Dengan model Product
        $product = new Product(['name' => 'Jumper Katun Murni']);
        $product->id = 99;
        $msgModel = WhatsApp::productMessage($product);
        $this->assertStringContainsString('Halo Baby Corner, saya ingin bertanya tentang produk Jumper Katun Murni', $msgModel);
    }

    public function test_order_message_and_link(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        // Dengan string nomor pesanan
        $msg = WhatsApp::orderMessage('ORD-20261125-0001');
        $this->assertEquals('Halo Baby Corner, saya ingin menanyakan pesanan ORD-20261125-0001.', $msg);

        // Dengan model Order
        $order = new Order(['nomor_pesanan' => 'ORD-TEST-999']);
        $msgOrder = WhatsApp::orderMessage($order);
        $this->assertEquals('Halo Baby Corner, saya ingin menanyakan pesanan ORD-TEST-999.', $msgOrder);

        $link = WhatsApp::orderLink($order);
        $this->assertEquals('https://wa.me/6281234567890?text=' . rawurlencode($msgOrder), $link);
    }

    public function test_size_message_with_various_baby_data(): void
    {
        // 1. Bayi lengkap (usia, berat, tinggi)
        $babyFull = new Baby([
            'nama_bayi'     => 'Nathan',
            'tanggal_lahir' => now()->subMonths(4)->toDateString(),
            'berat_badan'   => 6.5,
            'tinggi_badan'  => 63.0,
        ]);
        $msgFull = WhatsApp::sizeMessage('Jumper Bayi', $babyFull);
        $this->assertEquals('Halo Baby Corner, bayi saya usia 4 bulan, berat 6.5 kg, tinggi 63 cm. Ukuran apa yang cocok untuk Jumper Bayi?', $msgFull);

        // 2. Hanya berat badan
        $babyWeight = new Baby([
            'berat_badan' => 7.0,
        ]);
        $msgWeight = WhatsApp::sizeMessage('Jumper Bayi', $babyWeight);
        $this->assertEquals('Halo Baby Corner, bayi saya berat 7 kg. Ukuran apa yang cocok untuk Jumper Bayi?', $msgWeight);

        // 3. Hanya tinggi badan
        $babyHeight = new Baby([
            'tinggi_badan' => 65.0,
        ]);
        $msgHeight = WhatsApp::sizeMessage('Jumper Bayi', $babyHeight);
        $this->assertEquals('Halo Baby Corner, bayi saya tinggi 65 cm. Ukuran apa yang cocok untuk Jumper Bayi?', $msgHeight);

        // 4. Hanya usia
        $babyAge = new Baby([
            'tanggal_lahir' => now()->subMonths(5)->toDateString(),
        ]);
        $msgAge = WhatsApp::sizeMessage('Jumper Bayi', $babyAge);
        $this->assertEquals('Halo Baby Corner, bayi saya usia 5 bulan. Ukuran apa yang cocok untuk Jumper Bayi?', $msgAge);

        // 5. Tanpa data bayi / null
        $msgNoBaby = WhatsApp::sizeMessage('Jumper Bayi', null);
        $this->assertEquals('Halo Baby Corner, ukuran apa yang cocok untuk Jumper Bayi?', $msgNoBaby);
    }

    public function test_blade_helper_wa_link(): void
    {
        config(['shop.whatsapp' => '081234567890']);

        // Tanpa argumen menghasilkan tautan dengan pesan umum
        $this->assertEquals(WhatsApp::generalLink(), wa_link());
        $this->assertEquals('https://wa.me/6281234567890?text=Halo', wa_link('Halo'));

        // Kosong eksplisit menghasilkan link tanpa teks query
        $this->assertEquals('https://wa.me/6281234567890', wa_link(''));

        config(['shop.whatsapp' => null]);
        $this->assertNull(wa_link());
        $this->assertNull(wa_link('Halo'));
    }
}
