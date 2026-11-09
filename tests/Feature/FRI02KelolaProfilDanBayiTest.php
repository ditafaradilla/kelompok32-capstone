<?php

namespace Tests\Feature;

use App\Models\Baby;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeGuide;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FRI02KelolaProfilDanBayiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pelanggan dapat melihat profil dan kartu data bayi.
     */
    public function test_pelanggan_dapat_melihat_halaman_profil_dan_kartu_data_bayi(): void
    {
        $user = User::factory()->create([
            'name'  => 'Bunda Sarah',
            'email' => 'bunda.sarah@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Profil & Data Bayi');
        $response->assertSee('Data Akun & Profil');
        $response->assertSee('Ubah Kata Sandi');
        $response->assertSee('Data Bayi');
        $response->assertSee('Belum Diisi');

        // Tambah data bayi lalu lihat kembali
        Baby::create([
            'user_id'       => $user->id,
            'nama_bayi'     => 'Adiba',
            'tanggal_lahir' => Carbon::now()->subMonths(4)->toDateString(),
            'jenis_kelamin' => 'perempuan',
            'berat_badan'   => 6.5,
            'tinggi_badan'  => 63.0,
        ]);

        $responseWithBaby = $this->actingAs($user)->get(route('profile.edit'));
        $responseWithBaby->assertStatus(200);
        $responseWithBaby->assertSee('Adiba');
        $responseWithBaby->assertSee('Perempuan');
        $responseWithBaby->assertSee('4 Bulan');
        $responseWithBaby->assertSee('Terdaftar');
        $responseWithBaby->assertSee('Hapus data bayi');
    }

    /**
     * Tambah data bayi valid -> tersimpan di babies, redirect internal dipatuhi.
     */
    public function test_tambah_data_bayi_valid_tersimpan_dan_redirect_internal_dipatuhi(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Rayyan',
            'tanggal_lahir' => Carbon::now()->subMonths(6)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 7.8,
            'tinggi_badan'  => 67.5,
            'redirect'      => '/produk/1',
        ]);

        $response->assertRedirect('/produk/1');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('babies', [
            'user_id'       => $user->id,
            'nama_bayi'     => 'Rayyan',
            'jenis_kelamin' => 'laki-laki',
        ]);

        $baby = Baby::where('user_id', $user->id)->first();
        $this->assertNotNull($baby);
        $this->assertEquals(6, $baby->usia_bulan);
    }

    /**
     * Satu user hanya satu bayi: menambah lagi tidak membuat duplikat.
     */
    public function test_satu_user_hanya_satu_bayi_tidak_membuat_duplikat(): void
    {
        $user = User::factory()->create();

        // Tambah pertama kali
        $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Pertama',
            'tanggal_lahir' => Carbon::now()->subMonths(3)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 5.5,
            'tinggi_badan'  => 58.0,
        ]);

        $this->assertEquals(1, Baby::where('user_id', $user->id)->count());

        // Tambah lagi melalui store -> memperbarui data yang ada, tidak membuat baris duplikat
        $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Terbarukan',
            'tanggal_lahir' => Carbon::now()->subMonths(5)->toDateString(),
            'jenis_kelamin' => 'perempuan',
            'berat_badan'   => 6.8,
            'tinggi_badan'  => 64.0,
        ]);

        $this->assertEquals(1, Baby::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('babies', [
            'user_id'   => $user->id,
            'nama_bayi' => 'Bayi Terbarukan',
        ]);
        $this->assertDatabaseMissing('babies', [
            'nama_bayi' => 'Bayi Pertama',
        ]);
    }

    /**
     * Validasi data bayi: tanggal lahir di masa depan, > 5 tahun lalu, berat & tinggi di luar batas.
     */
    public function test_validasi_data_bayi_menolak_input_tidak_valid(): void
    {
        $user = User::factory()->create();

        // 1. Tanggal lahir di masa depan
        $resFuture = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Masa Depan',
            'tanggal_lahir' => Carbon::tomorrow()->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 6.0,
            'tinggi_badan'  => 60.0,
        ]);
        $resFuture->assertSessionHasErrors('tanggal_lahir');

        // 2. Tanggal lahir lebih dari 5 tahun yang lalu
        $resTooOld = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Anak 6 Tahun',
            'tanggal_lahir' => Carbon::now()->subYears(6)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 15.0,
            'tinggi_badan'  => 100.0,
        ]);
        $resTooOld->assertSessionHasErrors('tanggal_lahir');

        // 3. Berat badan kurang dari 0.5 kg atau negatif
        $resUnderweight = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Kecil',
            'tanggal_lahir' => Carbon::now()->subMonths(2)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 0.2,
            'tinggi_badan'  => 50.0,
        ]);
        $resUnderweight->assertSessionHasErrors('berat_badan');

        // 4. Berat badan melebihi 30 kg
        $resOverweight = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Berat',
            'tanggal_lahir' => Carbon::now()->subMonths(2)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 35.0,
            'tinggi_badan'  => 50.0,
        ]);
        $resOverweight->assertSessionHasErrors('berat_badan');

        // 5. Tinggi badan kurang dari 30 cm
        $resShort = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Pendek',
            'tanggal_lahir' => Carbon::now()->subMonths(2)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 5.0,
            'tinggi_badan'  => 25.0,
        ]);
        $resShort->assertSessionHasErrors('tinggi_badan');

        // 6. Tinggi badan lebih dari 130 cm
        $resTall = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Tinggi',
            'tanggal_lahir' => Carbon::now()->subMonths(2)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 5.0,
            'tinggi_badan'  => 140.0,
        ]);
        $resTall->assertSessionHasErrors('tinggi_badan');
    }

    /**
     * Pengguna A tidak dapat mengubah atau menghapus data bayi pengguna B (403 Forbidden).
     */
    public function test_pengguna_a_tidak_dapat_mengubah_atau_menghapus_data_bayi_pengguna_b(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $babyA = Baby::create([
            'user_id'       => $userA->id,
            'nama_bayi'     => 'Bayi A',
            'tanggal_lahir' => Carbon::now()->subMonths(4)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 6.0,
            'tinggi_badan'  => 62.0,
        ]);

        // User B mencoba update bayi milik User A
        $responseUpdate = $this->actingAs($userB)->put(route('bayi.update', $babyA), [
            'nama_bayi'     => 'Bayi Diubah B',
            'tanggal_lahir' => Carbon::now()->subMonths(4)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 6.0,
            'tinggi_badan'  => 62.0,
        ]);
        $responseUpdate->assertStatus(403);

        // Pastikan nama tidak berubah
        $this->assertEquals('Bayi A', $babyA->fresh()->nama_bayi);

        // User B mencoba menghapus bayi milik User A
        $responseDelete = $this->actingAs($userB)->delete(route('bayi.destroy', $babyA));
        $responseDelete->assertStatus(403);

        // Pastikan bayi A masih ada
        $this->assertDatabaseHas('babies', ['id' => $babyA->id]);
    }

    /**
     * Hapus data bayi berhasil -> data di babies hilang, pesan sukses muncul, halaman produk kembali ke ajakan isi data.
     */
    public function test_hapus_data_bayi_berhasil_dan_halaman_produk_kembali_mengajak_isi_data(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::factory()->create();
        $baby = Baby::create([
            'user_id'       => $user->id,
            'nama_bayi'     => 'Bayi Adiba',
            'tanggal_lahir' => Carbon::now()->subMonths(4)->toDateString(),
            'jenis_kelamin' => 'perempuan',
            'berat_badan'   => 6.5,
            'tinggi_badan'  => 63.0,
        ]);

        $product = Product::first();

        // Hapus data bayi
        $response = $this->actingAs($user)->delete(route('bayi.destroy', $baby));
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('babies', ['id' => $baby->id]);

        // Kunjungi halaman produk -> ajakan mengisi data si kecil muncul kembali (FRI-01)
        $productResponse = $this->actingAs($user)->get(route('produk.show', $product));
        $productResponse->assertStatus(200);
        $productResponse->assertSee('Isi Data Bayi Sekarang');
    }

    /**
     * Ubah profil: ubah nama, email, telepon, alamat, upload foto baru (foto lama terhapus dari disk), hapus foto.
     */
    public function test_ubah_profil_dan_kelola_foto(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name'  => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '0811111111',
        ]);

        // 1. Upload foto profil pertama
        $photo1 = UploadedFile::fake()->create('foto1.jpg', 100, 'image/jpeg');
        $res1 = $this->actingAs($user)->post(route('profile.update'), [
            'name'    => 'New Name',
            'email'   => 'new@example.com',
            'phone'   => '0822222222',
            'address' => 'Jl. Mawar No. 10',
            'photo'   => $photo1,
        ]);
        $res1->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('0822222222', $user->phone);
        $this->assertEquals('Jl. Mawar No. 10', $user->address);
        $this->assertNotNull($user->photo);
        $oldPhotoPath = $user->photo;
        Storage::disk('public')->assertExists($oldPhotoPath);

        // 2. Ganti dengan foto profil baru (foto lama harus terhapus)
        $photo2 = UploadedFile::fake()->create('foto2.png', 100, 'image/png');
        $res2 = $this->actingAs($user)->post(route('profile.update'), [
            'name'  => $user->name,
            'email' => $user->email,
            'photo' => $photo2,
        ]);
        $res2->assertRedirect(route('profile.edit'));

        $user->refresh();
        $newPhotoPath = $user->photo;
        $this->assertNotEquals($oldPhotoPath, $newPhotoPath);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($newPhotoPath);

        // 3. Hapus foto profil dengan remove_photo = 1
        $res3 = $this->actingAs($user)->post(route('profile.update'), [
            'name'         => $user->name,
            'email'        => $user->email,
            'remove_photo' => '1',
        ]);
        $res3->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertNull($user->photo);
        Storage::disk('public')->assertMissing($newPhotoPath);
    }

    /**
     * Ubah kata sandi: kata sandi lama benar -> berhasil; kata sandi lama salah -> validasi gagal; konfirmasi tidak cocok -> gagal.
     */
    public function test_ubah_kata_sandi_pengguna(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_lama123'),
        ]);

        // 1. Kata sandi lama salah
        $resWrongCurrent = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'kata_sandi_salah',
            'password'              => 'password_baru123',
            'password_confirmation' => 'password_baru123',
        ]);
        $resWrongCurrent->assertSessionHasErrors('current_password');

        // 2. Konfirmasi kata sandi tidak cocok
        $resMismatch = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'password_lama123',
            'password'              => 'password_baru123',
            'password_confirmation' => 'password_beda456',
        ]);
        $resMismatch->assertSessionHasErrors('password');

        // 3. Kata sandi baru kurang dari 8 karakter
        $resShort = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'password_lama123',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);
        $resShort->assertSessionHasErrors('password');

        // 4. Kata sandi lama benar & baru valid
        $resSuccess = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'password_lama123',
            'password'              => 'password_baru123',
            'password_confirmation' => 'password_baru123',
        ]);
        $resSuccess->assertRedirect(route('profile.edit'));
        $resSuccess->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('password_baru123', $user->password));
    }

    /**
     * Keamanan redirect: URL eksternal atau protocol-relative ditolak, hanya URL internal yang sah yang diterima.
     */
    public function test_keamanan_redirect_menolak_url_eksternal_pada_baby_dan_profile(): void
    {
        $user = User::factory()->create();

        // 1. Redirect eksternal pada bayi.store dialihkan ke profile.edit
        $resBabyEvil = $this->actingAs($user)->post(route('bayi.store'), [
            'nama_bayi'     => 'Bayi Ceria',
            'tanggal_lahir' => Carbon::now()->subMonths(2)->toDateString(),
            'jenis_kelamin' => 'laki-laki',
            'berat_badan'   => 5.2,
            'tinggi_badan'  => 56.0,
            'redirect'      => 'https://evil-attacker.com/steal',
        ]);
        $resBabyEvil->assertRedirect(route('profile.edit'));

        // 2. Protocol-relative URL //evil.com dialihkan ke profile.edit
        $resProto = $this->actingAs($user)->post(route('profile.update'), [
            'name'     => $user->name,
            'email'    => $user->email,
            'redirect' => '//evil-site.com',
        ]);
        $resProto->assertRedirect(route('profile.edit'));

        // 3. Internal path yang sah diterima
        $resValid = $this->actingAs($user)->post(route('profile.update'), [
            'name'     => $user->name,
            'email'    => $user->email,
            'redirect' => '/katalog',
        ]);
        $resValid->assertRedirect('/katalog');
    }
}
