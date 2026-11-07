<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Business Intelligence (Anggota 3)
|--------------------------------------------------------------------------
| Dashboard, analisis bazar, dan forecasting memakai tabel `transactions`
| (Pembukuan) sebagai SATU-SATUNYA sumber nilai uang.
|
| Data PRODUK TERLARIS, JUMLAH PESANAN, dan REKOMENDASI STOK butuh tabel
| pesanan, item pesanan, dan produk milik anggota lain. Karena nama tabel dan
| kolomnya belum diketahui, fitur itu MATI secara bawaan.
|
| Cara menyalakan:
|  1. Sesuaikan nama tabel/kolom di bawah dengan migration kelompok.
|  2. Tambahkan BI_PRODUCT_SALES=true di file .env, lalu jalankan:
|     php artisan config:clear
*/

return [
    'product_sales' => [
        'enabled' => (bool) env('BI_PRODUCT_SALES', false),

        // Tabel pesanan
        'orders_table'  => 'orders',
        'orders_pk'     => 'id',
        'order_date'    => 'created_at',
        'order_status'  => 'status',
        // Status pesanan yang dianggap terjual, mis. ['dibayar', 'dikirim', 'selesai'].
        // Kosongkan ([]) bila semua pesanan dihitung.
        'paid_statuses' => [],

        // Tabel item pesanan
        'items_table'     => 'order_items',
        'item_order_fk'   => 'order_id',
        'item_product_fk' => 'product_id',
        'item_qty'        => 'quantity',
        'item_price'      => 'price',      // harga satuan saat dibeli

        // Tabel produk
        'products_table' => 'products',
        'products_pk'    => 'id',
        'product_name'   => 'name',
        'product_stock'  => 'stock',       // isi null jika tidak ada kolom stok
    ],
];