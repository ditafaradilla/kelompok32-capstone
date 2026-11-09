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

