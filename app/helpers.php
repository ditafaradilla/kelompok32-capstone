<?php

if (!function_exists('rupiah')) {
    /**
     * Format angka ke format mata uang Rupiah.
     * Contoh: rupiah(125000) => "Rp 125.000"
     *
     * @param float|int|string|null $angka
     * @return string
     */
    function rupiah($angka): string
    {
        return 'Rp ' . number_format((float) ($angka ?? 0), 0, ',', '.');
    }
}

if (!function_exists('wa_link')) {
    /**
     * Menghasilkan link tautan WhatsApp Baby Corner.
     * Mengembalikan null jika nomor WhatsApp toko belum dikonfigurasi / tidak valid.
     *
     * @param string|null $message
     * @return string|null
     */
    function wa_link(?string $message = null): ?string
    {
        return \App\Support\WhatsApp::link($message ?? \App\Support\WhatsApp::generalMessage());
    }
}

