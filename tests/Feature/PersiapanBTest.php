<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Location;
use App\Models\ProductStock;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersiapanBTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tamu yang belum login harus diarahkan ke halaman login.
     */
    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/produk')->assertRedirect('/login');
        $this->get('/produk/1')->assertRedirect('/login');
    }

    /**
     * Pengguna dan admin yang login dapat membuka beranda dan katalog tanpa error.
     */
    public function test_pengguna_dan_admin_dapat_mengakses_katalog(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('role', 'user')->first();
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
        $this->actingAs($user)->get('/produk')->assertStatus(200);

        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/produk')->assertStatus(200);
    }

    /**
     * Katalog menampilkan produk-produk hasil seeding.
     */
    public function test_katalog_menampilkan_produk_seed(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('role', 'user')->first();
        $response = $this->actingAs($user)->get('/produk');

        $response->assertStatus(200);
        $response->assertSee('Kaos Bayi Katun Combed Lembut');
        $response->assertSee('Jumper Katun Organik Premium');
        $response->assertSee('Kain Bedong Bayi Katun Bambu Lembut');
    }

    /**
     * Fitur pencarian berdasarkan nama produk bekerja.
     */
    public function test_pencarian_katalog_berdasarkan_nama(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('role', 'user')->first();

        // Cari "Kaos"
        $response = $this->actingAs($user)->get('/produk?q=Kaos');
        $response->assertStatus(200);
        $response->assertSee('Kaos Bayi Katun Combed Lembut');
        $response->assertDontSee('Jumper Katun Organik Premium');
    }

    /**
     * Filter kategori pada katalog bekerja.
     */
    public function test_filter_katalog_berdasarkan_kategori(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('role', 'user')->first();

        // Filter kategori "Jumper"
        $response = $this->actingAs($user)->get('/produk?category=Jumper');
        $response->assertStatus(200);
        $response->assertSee('Jumper Katun Organik Premium');
        $response->assertDontSee('Kain Bedong Bayi');
    }

    /**
     * Filter ukuran varian pada katalog bekerja.
     */
    public function test_filter_katalog_berdasarkan_ukuran(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('role', 'user')->first();

        // Filter ukuran "NB" (Newborn)
        $response = $this->actingAs($user)->get('/produk?size=NB');
        $response->assertStatus(200);
        $response->assertSee('Kain Bedong Bayi Katun Bambu Lembut');
        $response->assertDontSee('Kaos Bayi Katun Combed Lembut');
    }

    /**
     * Halaman detail produk menampilkan info, varian, dan tombol tambah ke keranjang nonaktif.
     */
    public function test_detail_produk_berhasil_ditampilkan(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('role', 'user')->first();
        $product = Product::first();

        $response = $this->actingAs($user)->get("/produk/{$product->id}");

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('Tambah ke Keranjang (Segera hadir)');
        $response->assertSee('Pilih Ukuran');
    }

    /**
     * Detail produk mengembalikan 404 jika ID produk tidak ditemukan.
     */
    public function test_detail_produk_404_jika_tidak_ditemukan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/produk/999999')->assertStatus(404);
    }
}

