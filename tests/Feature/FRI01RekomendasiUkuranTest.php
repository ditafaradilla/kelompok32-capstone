<?php

namespace Tests\Feature;

use App\Models\Baby;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FRI01RekomendasiUkuranTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User tanpa bayi melihat kartu ajakan isi data bayi di detail produk.
     */
    public function test_user_tanpa_bayi_melihat_ajakan_isi_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        // User baru tanpa bayi
        $userTanpaBayi = User::factory()->create([
            'role' => 'user',
        ]);

        $product = Product::first();

        $response = $this->actingAs($userTanpaBayi)->get("/produk/{$product->id}");

        $response->assertStatus(200);
        $response->assertSee('Isi data bayi untuk mendapat rekomendasi ukuran');
        $response->assertSee('Isi Data Bayi Sekarang');
        $response->assertDontSee('Rekomendasi untuk');
    }

    /**
     * User dengan bayi melihat rekomendasi ukuran dan badge 'Disarankan'.
     */
    public function test_user_dengan_bayi_melihat_rekomendasi_dan_badge_disarankan(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Bunda Sarah (punya bayi Adiba usia 4 bulan)
        $bundaSarah = User::where('email', 'bunda.sarah@gmail.com')->first();
        $this->assertNotNull($bundaSarah->baby);

        // Kaos Bayi punya varian 0-3M, 3-6M, 6-9M
        $product = Product::where('sku', 'BC-KOS-100')->first();

        $response = $this->actingAs($bundaSarah)->get("/produk/{$product->id}");

        $response->assertStatus(200);
        $response->assertSee('Rekomendasi untuk Adiba');
        $response->assertSee('3-6M');
        $response->assertSee('Disarankan');
        $response->assertSee('Ubah data bayi');
    }

    /**
     * Redirect eksternal pada profile.update ditolak demi keamanan.
     */
    public function test_redirect_eksternal_pada_update_profil_ditolak(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/dashboard/edit-profil', [
            'name'      => 'Bunda Test',
            'email'     => $user->email,
            'redirect'  => 'https://evil-phishing.com/steal-data',
        ]);

        // Harus dialihkan ke profile.edit lokal, bukan ke domain eksternal
        $response->assertRedirect(route('profile.edit'));
        $this->assertStringNotContainsString('evil-phishing.com', $response->headers->get('Location'));
    }

    /**
     * Redirect internal yang sah pada profile.update diterima.
     */
    public function test_redirect_internal_yang_sah_diterima(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/dashboard/edit-profil', [
            'name'      => 'Bunda Test',
            'email'     => $user->email,
            'redirect'  => '/produk/1',
        ]);

        $response->assertRedirect('/produk/1');
    }

    /**
     * Halaman panduan ukuran menampilkan tabel acuan dan kalkulator cepat rekomendasi ukuran.
     */
    public function test_halaman_panduan_ukuran_menampilkan_tabel_dan_kalkulator(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();

        // 1. Buka halaman panduan ukuran awal
        $response = $this->actingAs($user)->get('/panduan-ukuran');
        $response->assertStatus(200);
        $response->assertSee('Panduan dan Rekomendasi Ukuran Bayi');
        $response->assertSee('Tabel Acuan Standar Ukuran Baju Bayi');
        $response->assertSee('NB');
        $response->assertSee('0-3M');
        $response->assertSee('3-6M');
        $response->assertSee('Kalkulator Cepat Rekomendasi Ukuran');

        // 2. Coba kalkulator cepat via GET query
        $calcResponse = $this->actingAs($user)->get('/panduan-ukuran?usia=4&berat=6.5&tinggi=63');
        $calcResponse->assertStatus(200);
        $calcResponse->assertSee('Hasil Rekomendasi Ukuran');
        $calcResponse->assertSee('3-6M');
    }
}
