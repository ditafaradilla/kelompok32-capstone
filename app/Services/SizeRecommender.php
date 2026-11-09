<?php

namespace App\Services;

use App\Models\Baby;
use App\Models\SizeGuide;

class SizeRecommender
{
    /**
     * Berikan rekomendasi ukuran berdasarkan objek data Baby.
     *
     * @param Baby $baby
     * @return array
     */
    public function recommend(Baby $baby): array
    {
        $usia = $baby->usia_bulan;
        $berat = !empty($baby->berat_badan) ? (float) $baby->berat_badan : null;
        $tinggi = !empty($baby->tinggi_badan) ? (float) $baby->tinggi_badan : null;

        return $this->recommendFromMeasurements($usia, $berat, $tinggi);
    }

    /**
     * Berikan rekomendasi ukuran berdasarkan angka usia (bulan), berat (kg), dan tinggi (cm).
     *
     * @param int|null $usiaBulan
     * @param float|null $berat
     * @param float|null $tinggi
     * @return array{
     *     ukuran_utama: string|null,
     *     alternatif: string|null,
     *     alasan: string,
     *     status: string
     * }
     */
    public function recommendFromMeasurements(?int $usiaBulan, ?float $berat = null, ?float $tinggi = null): array
    {
        $usia = $usiaBulan !== null ? max(0, $usiaBulan) : 0;
        $guides = SizeGuide::orderBy('id')->get();

        if ($guides->isEmpty()) {
            return [
                'ukuran_utama' => null,
                'alternatif'   => null,
                'alasan'       => 'Panduan ukuran belum tersedia.',
                'status'       => 'di_luar_rentang',
            ];
        }

        // a & b. Ambil baris size_guides yang rentang usianya memuat usia bayi
        $matchingGuides = $guides->filter(function ($g) use ($usia) {
            return $usia >= $g->usia_min_bulan && $usia <= $g->usia_maks_bulan;
        });

        // d. Jika usia di luar semua rentang
        if ($matchingGuides->isEmpty()) {
            $minAge = $guides->min('usia_min_bulan') ?? 0;
            $maxAge = $guides->max('usia_maks_bulan') ?? 24;

            return [
                'ukuran_utama' => null,
                'alternatif'   => null,
                'alasan'       => "Usia si kecil ({$usia} bulan) berada di luar jangkauan panduan ukuran standar kami ({$minAge}-{$maxAge} bulan). Silakan hubungi kami untuk informasi ukuran khusus.",
                'status'       => 'di_luar_rentang',
            ];
        }

        // b. Beri skor jika terdapat lebih dari satu rentang bertumpuk
        $hasWeight = ($berat !== null && $berat > 0);
        $hasHeight = ($tinggi !== null && $tinggi > 0);

        $scored = $matchingGuides->map(function ($guide) use ($berat, $tinggi, $hasWeight, $hasHeight) {
            $score = 0;
            if ($hasWeight) {
                if ($berat >= (float) $guide->berat_min && $berat <= (float) $guide->berat_maks) {
                    $score += 1;
                }
            }
            if ($hasHeight) {
                if ($tinggi >= (float) $guide->tinggi_min && $tinggi <= (float) $guide->tinggi_maks) {
                    $score += 1;
                }
            }
            return [
                'guide' => $guide,
                'score' => $score,
            ];
        });

        // Pilih skor tertinggi; jika seri, pilih ukuran yang lebih kecil (id lebih kecil)
        $best = $scored->sort(function ($a, $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }
            return $a['guide']->id <=> $b['guide']->id;
        })->first();

        $selectedGuide = $best['guide'];
        $currentIndex = $guides->search(fn ($g) => $g->id === $selectedGuide->id);

        // c. Cek apakah berat ATAU tinggi melebihi batas maksimum baris terpilih
        $exceedsWeight = $hasWeight && ($berat > (float) $selectedGuide->berat_maks);
        $exceedsHeight = $hasHeight && ($tinggi > (float) $selectedGuide->tinggi_maks);

        if ($exceedsWeight || $exceedsHeight) {
            // Naikkan 1 ukuran jika tersedia
            $nextGuide = $guides->get($currentIndex + 1);

            if ($nextGuide) {
                $ukuranUtama = $nextGuide->ukuran;
                $altGuide = $guides->get($currentIndex + 2);
                $alternatif = $altGuide ? $altGuide->ukuran : null;

                if ($exceedsWeight && $exceedsHeight) {
                    $alasan = "Berat badan ({$berat} kg) dan tinggi badan ({$tinggi} cm) melebihi batas maksimum ukuran {$selectedGuide->ukuran}. Disarankan naik ke ukuran {$ukuranUtama} agar pakaian nyaman dan tidak cepat sempit.";
                } elseif ($exceedsWeight) {
                    $alasan = "Berat badan ({$berat} kg) melebihi batas standar ukuran {$selectedGuide->ukuran} (maks. " . (float) $selectedGuide->berat_maks . " kg). Disarankan memilih ukuran {$ukuranUtama} agar si kecil lebih leluasa.";
                } else {
                    $alasan = "Tinggi badan ({$tinggi} cm) melebihi batas standar ukuran {$selectedGuide->ukuran} (maks. " . (float) $selectedGuide->tinggi_maks . " cm). Disarankan memilih ukuran {$ukuranUtama} agar panjang baju pas.";
                }

                return [
                    'ukuran_utama' => $ukuranUtama,
                    'alternatif'   => $alternatif,
                    'alasan'       => $alasan,
                    'status'       => 'batas',
                ];
            }
        }

        // Ukuran pas sesuai baris terpilih
        $ukuranUtama = $selectedGuide->ukuran;
        $altGuide = $guides->get($currentIndex + 1);
        $alternatif = $altGuide ? $altGuide->ukuran : null;

        // e. Jika berat/tinggi kosong
        if (!$hasWeight || !$hasHeight) {
            $alasan = "Ukuran {$ukuranUtama} direkomendasikan berdasarkan usia si kecil ({$usia} bulan). Lengkapi data berat & tinggi badan bayi di profil agar rekomendasi ukuran lebih akurat.";
        } else {
            $alasan = "Ukuran {$ukuranUtama} pas untuk si kecil usia {$usia} bulan dengan berat {$berat} kg dan tinggi {$tinggi} cm.";
        }

        return [
            'ukuran_utama' => $ukuranUtama,
            'alternatif'   => $alternatif,
            'alasan'       => $alasan,
            'status'       => 'pas',
        ];
    }
}

