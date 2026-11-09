<?php

namespace Tests\Unit;

use App\Models\Baby;
use App\Models\SizeGuide;
use App\Services\SizeRecommender;
use Database\Seeders\SizeGuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeRecommenderTest extends TestCase
{
    use RefreshDatabase;

    protected SizeRecommender $recommender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SizeGuideSeeder::class);
        $this->recommender = new SizeRecommender();
    }

    /**
     * Bayi 4 bulan 6.5 kg 63 cm -> 3-6M.
     */
    public function test_bayi_4_bulan_6_5_kg_63_cm_mendapat_rekomendasi_3_6m(): void
    {
        $baby = new Baby([
            'nama_bayi'     => 'Baby Nathan',
            'tanggal_lahir' => now()->subMonths(4)->toDateString(),
            'berat_badan'   => 6.50,
            'tinggi_badan'  => 63.0,
        ]);

        $result = $this->recommender->recommend($baby);

        $this->assertEquals('3-6M', $result['ukuran_utama']);
        $this->assertEquals('6-9M', $result['alternatif']);
        $this->assertEquals('pas', $result['status']);
        $this->assertStringContainsString('3-6M', $result['alasan']);
    }

    /**
     * Bayi 2 bulan 7 kg (berat melebihi rentang 0-3M) -> naik ke 3-6M dengan alasan.
     */
    public function test_bayi_2_bulan_7_kg_naik_ke_3_6m_karena_melebihi_berat_maksimal(): void
    {
        $baby = new Baby([
            'nama_bayi'     => 'Baby Boba',
            'tanggal_lahir' => now()->subMonths(2)->toDateString(),
            'berat_badan'   => 7.00, // batas maks 0-3M adalah 6.00 kg
            'tinggi_badan'  => 56.0,
        ]);

        $result = $this->recommender->recommend($baby);

        $this->assertEquals('3-6M', $result['ukuran_utama']);
        $this->assertEquals('6-9M', $result['alternatif']);
        $this->assertEquals('batas', $result['status']);
        $this->assertStringContainsString('melebihi', $result['alasan']);
    }

    /**
     * Bayi 40 bulan -> di_luar_rentang.
     */
    public function test_bayi_40_bulan_berstatus_di_luar_rentang(): void
    {
        $baby = new Baby([
            'nama_bayi'     => 'Balita Bima',
            'tanggal_lahir' => now()->subMonths(40)->toDateString(),
            'berat_badan'   => 16.0,
            'tinggi_badan'  => 100.0,
        ]);

        $result = $this->recommender->recommend($baby);

        $this->assertEquals('di_luar_rentang', $result['status']);
        $this->assertNull($result['ukuran_utama']);
        $this->assertNull($result['alternatif']);
        $this->assertStringContainsString('luar jangkauan', $result['alasan']);
    }

    /**
     * Berat/tinggi kosong -> tetap ada hasil + catatan.
     */
    public function test_berat_dan_tinggi_kosong_tetap_mendapat_hasil_berdasarkan_usia_dan_ada_catatan(): void
    {
        $baby = new Baby([
            'nama_bayi'     => 'Baby Al',
            'tanggal_lahir' => now()->subMonths(4)->toDateString(),
            'berat_badan'   => null,
            'tinggi_badan'  => null,
        ]);

        $result = $this->recommender->recommend($baby);

        $this->assertEquals('3-6M', $result['ukuran_utama']);
        $this->assertEquals('6-9M', $result['alternatif']);
        $this->assertEquals('pas', $result['status']);
        $this->assertStringContainsString('Lengkapi data berat & tinggi badan', $result['alasan']);
    }
}

