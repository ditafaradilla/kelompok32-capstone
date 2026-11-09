<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pengaturan Toko & Informasi Pembayaran Manual
    |--------------------------------------------------------------------------
    | Digunakan pada alur checkout dan petunjuk pembayaran pelanggan (FRI-04).
    */

    'bank_name'    => env('SHOP_BANK_NAME', 'BCA'),
    'bank_account' => env('SHOP_BANK_ACCOUNT', '1234567890'),
    'bank_holder'  => env('SHOP_BANK_HOLDER', 'Baby Corner Official'),
    'whatsapp'     => env('SHOP_WHATSAPP', '081234567890'),
];

