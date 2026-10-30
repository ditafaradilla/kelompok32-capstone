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
}
