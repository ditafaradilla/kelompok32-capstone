{{-- Kartu "data belum tersedia". Variabel: $title, (opsional) $extra, (opsional) $error --}}
<div class="bg-surface-container-low border border-dashed border-outline-variant rounded-xl p-5">
    <div class="flex items-center gap-2 text-title-md">
        <span class="material-symbols-outlined text-outline">database</span> {{ $title }}
    </div>

    @if (! empty($error))
        <p class="mt-2 text-on-error-container bg-error-container rounded-lg px-3 py-2 text-label-md">{{ $error }}</p>
    @else
        <p class="mt-2 text-on-surface-variant">Bagian ini belum aktif karena membutuhkan data pesanan, item pesanan, dan produk dari anggota lain. Data tidak diisi dengan angka karangan.</p>
    @endif

    <div class="mt-3 text-body-md">
        <div class="font-semibold">Data minimal yang dibutuhkan:</div>
        <ul class="list-disc pl-5 text-on-surface-variant">
            <li><b>Pesanan</b>: id, tanggal pesanan, status pesanan</li>
            <li><b>Item pesanan</b>: id pesanan, id produk, jumlah (qty), harga satuan</li>
            <li><b>Produk</b>: id, nama, stok saat ini</li>
            @isset($extra)<li>{{ $extra }}</li>@endisset
        </ul>
        <div class="font-semibold mt-3">Cara mengaktifkan:</div>
        <ol class="list-decimal pl-5 text-on-surface-variant">
            <li>Sesuaikan nama tabel dan kolom di <code>config/bi.php</code> dengan migration kelompok.</li>
            <li>Tambahkan <code>BI_PRODUCT_SALES=true</code> di file <code>.env</code>.</li>
            <li>Jalankan <code>php artisan config:clear</code>, lalu muat ulang halaman ini.</li>
        </ol>
    </div>
</div>