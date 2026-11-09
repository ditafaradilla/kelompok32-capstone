<?php

namespace Database\Seeders;

use App\Models\SizeGuide;
use Illuminate\Database\Seeder;

class SizeGuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Catatan: Nilai panduan ukuran ini adalah contoh standar pakaian bayi dan
     * harus dicocokkan kembali dengan size chart resmi dari supplier/konveksi Baby Corner.
     */
    public function run(): void
    {
        $guides = [
            [
                'ukuran'          => 'NB',
                'usia_min_bulan'  => 0,
                'usia_maks_bulan' => 1,
                'berat_min'       => 2.50,
                'berat_maks'      => 4.00,
                'tinggi_min'      => 45.0,
                'tinggi_maks'     => 54.0,
            ],
            [
                'ukuran'          => '0-3M',
                'usia_min_bulan'  => 0,
                'usia_maks_bulan' => 3,
                'berat_min'       => 3.50,
                'berat_maks'      => 6.00,
                'tinggi_min'      => 50.0,
                'tinggi_maks'     => 62.0,
            ],
            [
                'ukuran'          => '3-6M',
                'usia_min_bulan'  => 3,
                'usia_maks_bulan' => 6,
                'berat_min'       => 5.50,
                'berat_maks'      => 8.00,
                'tinggi_min'      => 58.0,
                'tinggi_maks'     => 68.0,
            ],
            [
                'ukuran'          => '6-9M',
                'usia_min_bulan'  => 6,
                'usia_maks_bulan' => 9,
                'berat_min'       => 7.00,
                'berat_maks'      => 9.50,
                'tinggi_min'      => 65.0,
                'tinggi_maks'     => 73.0,
            ],
            [
                'ukuran'          => '9-12M',
                'usia_min_bulan'  => 9,
                'usia_maks_bulan' => 12,
                'berat_min'       => 8.50,
                'berat_maks'      => 10.50,
                'tinggi_min'      => 70.0,
                'tinggi_maks'     => 78.0,
            ],
            [
                'ukuran'          => '12-18M',
                'usia_min_bulan'  => 12,
                'usia_maks_bulan' => 18,
                'berat_min'       => 9.50,
                'berat_maks'      => 12.00,
                'tinggi_min'      => 76.0,
                'tinggi_maks'     => 84.0,
            ],
            [
                'ukuran'          => '18-24M',
                'usia_min_bulan'  => 18,
                'usia_maks_bulan' => 24,
                'berat_min'       => 11.00,
                'berat_maks'      => 13.50,
                'tinggi_min'      => 82.0,
                'tinggi_maks'     => 90.0,
            ],
        ];

        foreach ($guides as $guide) {
            SizeGuide::updateOrCreate(
                ['ukuran' => $guide['ukuran']],
                $guide
            );
        }
    }
}

