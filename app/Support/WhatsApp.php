<?php

namespace App\Support;

use App\Models\Baby;
use App\Models\Order;
use App\Models\Product;

class WhatsApp
{
    /**
     * Mengambil nomor WhatsApp dari config dan melakukan normalisasi.
     * Mengembalikan null jika nomor kosong atau tidak valid.
     */
    public static function number(): ?string
    {
        $raw = config('shop.whatsapp');

        if ($raw === null || $raw === '') {
            return null;
        }

        // Hapus spasi, tanda tambah, strip, titik, dan karakter non-digit lainnya
        $digits = preg_replace('/\D/', '', (string) $raw);

        if (empty($digits)) {
            return null;
        }

        // Ubah awalan '0' menjadi '62' (format internasional Indonesia)
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            // Jika diawali angka 8 (mis. 81234567890), tambahkan kode negara 62
            $digits = '62' . $digits;
        }

        // Validasi panjang nomor wajar (nomor telepon internasional umumnya 9-16 digit)
        if (strlen($digits) < 9 || strlen($digits) > 16) {
            return null;
        }

        return $digits;
    }

    /**
     * Membangun URL tautan https://wa.me/{nomor}?text={pesan}.
     * Mengembalikan null jika nomor tidak valid/belum dikonfigurasi.
     */
    public static function link(?string $message = null): ?string
    {
        $number = self::number();

        if ($number === null) {
            return null;
        }

        if ($message === null || trim($message) === '') {
            return "https://wa.me/{$number}";
        }

        return "https://wa.me/{$number}?text=" . rawurlencode(trim($message));
    }

    /**
     * Template pesan umum.
     */
    public static function generalMessage(): string
    {
        return "Halo Baby Corner, saya ingin bertanya.";
    }

    /**
     * Template pesan tanya produk.
     */
    public static function productMessage(Product|string $product, ?string $url = null): string
    {
        $name = $product instanceof Product ? $product->name : (string) $product;

        if ($url === null && $product instanceof Product) {
            try {
                $url = route('produk.show', $product);
            } catch (\Throwable $e) {
                $url = null;
            }
        }

        if (!empty($url)) {
            return "Halo Baby Corner, saya ingin bertanya tentang produk {$name} ({$url}).";
        }

        return "Halo Baby Corner, saya ingin bertanya tentang produk {$name}.";
    }

    /**
     * Template pesan tanya pesanan.
     */
    public static function orderMessage(Order|string $order): string
    {
        $nomor = $order instanceof Order ? $order->nomor_pesanan : (string) $order;

        return "Halo Baby Corner, saya ingin menanyakan pesanan {$nomor}.";
    }

    /**
     * Template pesan konsultasi ukuran bayi.
     * Hanya menyertakan data bayi yang terisi.
     */
    public static function sizeMessage(Product|string $product, ?Baby $baby = null): string
    {
        $productName = $product instanceof Product ? $product->name : (string) $product;

        $parts = [];
        if ($baby) {
            if (!empty($baby->tanggal_lahir) && $baby->usia_bulan !== null) {
                $parts[] = "usia {$baby->usia_bulan} bulan";
            }
            if ($baby->berat_badan !== null && $baby->berat_badan !== '' && (float) $baby->berat_badan > 0) {
                $weight = (float) $baby->berat_badan;
                $parts[] = "berat {$weight} kg";
            }
            if ($baby->tinggi_badan !== null && $baby->tinggi_badan !== '' && (float) $baby->tinggi_badan > 0) {
                $height = (float) $baby->tinggi_badan;
                $parts[] = "tinggi {$height} cm";
            }
        }

        if (!empty($parts)) {
            return "Halo Baby Corner, bayi saya " . implode(', ', $parts) . ". Ukuran apa yang cocok untuk {$productName}?";
        }

        return "Halo Baby Corner, ukuran apa yang cocok untuk {$productName}?";
    }

    /**
     * Tautan WhatsApp dengan pesan umum.
     */
    public static function generalLink(): ?string
    {
        return self::link(self::generalMessage());
    }

    /**
     * Tautan WhatsApp dengan pesan produk.
     */
    public static function productLink(Product|string $product, ?string $url = null): ?string
    {
        return self::link(self::productMessage($product, $url));
    }

    /**
     * Tautan WhatsApp dengan pesan pesanan.
     */
    public static function orderLink(Order|string $order): ?string
    {
        return self::link(self::orderMessage($order));
    }

    /**
     * Tautan WhatsApp dengan pesan konsultasi ukuran.
     */
    public static function sizeLink(Product|string $product, ?Baby $baby = null): ?string
    {
        return self::link(self::sizeMessage($product, $baby));
    }
}
