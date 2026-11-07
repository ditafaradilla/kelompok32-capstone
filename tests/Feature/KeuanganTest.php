<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeuanganTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/keuangan')->assertRedirect('/login');
    }

    public function test_user_biasa_ditolak(): void
    {
        $this->actingAs($this->makeUser('user'))->get('/admin/keuangan')->assertForbidden();
    }

    public function test_admin_bisa_membuka_halaman(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/keuangan')->assertOk();
        $this->actingAs($this->makeUser('admin'))->get('/admin/keuangan/laporan')->assertOk();
    }

    public function test_admin_bisa_mencatat_transaksi_dan_nominal_dibersihkan(): void
    {
        $this->actingAs($this->makeUser('admin'))->post('/admin/keuangan', [
            'transaction_date' => now()->toDateString(),
            'type'             => 'income',
            'category'         => 'Penjualan Online',
            'amount'           => '1.500.000',
            'payment_status'   => 'lunas',
        ])->assertRedirect('/admin/keuangan');

        $this->assertDatabaseHas('transactions', ['amount' => 1500000, 'type' => 'income']);
    }

    public function test_kategori_harus_sesuai_jenis(): void
    {
        $this->actingAs($this->makeUser('admin'))->post('/admin/keuangan', [
            'transaction_date' => now()->toDateString(),
            'type'             => 'income',
            'category'         => 'Ongkos Kirim', // kategori pengeluaran
            'amount'           => '1000',
            'payment_status'   => 'lunas',
        ])->assertSessionHasErrors('category');
    }

    public function test_ringkasan_hanya_menghitung_lunas(): void
    {
        $d = now()->toDateString();
        Transaction::create(['transaction_date' => $d, 'type' => 'income',  'category' => 'Penjualan Online', 'amount' => 1000000, 'payment_status' => 'lunas']);
        Transaction::create(['transaction_date' => $d, 'type' => 'income',  'category' => 'Penjualan Online', 'amount' => 500000,  'payment_status' => 'belum_lunas']);
        Transaction::create(['transaction_date' => $d, 'type' => 'expense', 'category' => 'Ongkos Kirim',     'amount' => 300000,  'payment_status' => 'lunas']);
        Transaction::create(['transaction_date' => $d, 'type' => 'expense', 'category' => 'Ongkos Kirim',     'amount' => 200000,  'payment_status' => 'belum_lunas']);

        $s = Transaction::summarize(Transaction::query());

        $this->assertSame(1000000, $s['income']);
        $this->assertSame(300000, $s['expense']);
        $this->assertSame(700000, $s['balance']);
        $this->assertSame(500000, $s['receivable']);
        $this->assertSame(200000, $s['payable']);
    }

    public function test_ekspor_csv_semua_data_dan_per_periode(): void
    {
        $admin = $this->makeUser('admin');
        Transaction::create(['transaction_date' => '2026-09-10', 'type' => 'income', 'category' => 'Penjualan Online',
            'amount' => 1000000, 'payment_status' => 'lunas', 'description' => '=HYPERLINK("x")']);
        Transaction::create(['transaction_date' => '2026-10-10', 'type' => 'expense', 'category' => 'Ongkos Kirim',
            'amount' => 300000, 'payment_status' => 'lunas']);

        $all = $this->actingAs($admin)->get('/admin/keuangan/export?scope=all');
        $all->assertOk();
        $csv = $all->streamedContent();
        $this->assertStringContainsString('Penjualan Online', $csv);
        $this->assertStringContainsString('Ongkos Kirim', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv); // rumus dinetralkan

        $period = $this->actingAs($admin)
            ->get('/admin/keuangan/export?scope=period&export_from=2026-10-01&export_to=2026-10-31')
            ->streamedContent();
        $this->assertStringContainsString('Ongkos Kirim', $period);
        $this->assertStringNotContainsString('Penjualan Online', $period);
    }

    public function test_ekspor_ditolak_untuk_user_biasa(): void
    {
        $this->actingAs($this->makeUser('user'))->get('/admin/keuangan/export?scope=all')->assertForbidden();
    }
}