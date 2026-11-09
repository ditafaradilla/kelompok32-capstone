
{{--
    SIDEBAR ADMIN BERSAMA
    Dashboard Penjualan ditampilkan di Beranda Admin.
    Forecasting & Stok memiliki menu tersendiri.
    Analisis Bazar, Pembukuan, dan Laporan Keuangan
    berada di grup Laporan Penjualan.
--}}
@php
    $inLaporan = request()->routeIs(
        'admin.keuangan.*',
        'admin.bi.bazar'
    );

    $inForecast = request()->routeIs('admin.bi.forecast');

    $item   = 'flex items-center gap-3 px-space-md py-3 rounded-xl font-body-md text-body-md transition-all';
    $idle   = 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface';
    $active = 'bg-primary-container text-on-primary-container font-semibold';

    $sub       = 'flex items-center px-space-md py-2 rounded-xl text-sm transition-all';
    $subIdle   = 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface';
    $subActive = 'bg-surface-container text-primary font-semibold';
@endphp

<aside class="fixed left-0 top-0 bottom-0 w-72 bg-surface-container-lowest z-50 flex flex-col justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)]">

    <div class="flex flex-col">

        {{-- LOGO --}}
        <div class="h-20 px-space-lg flex items-center justify-between">
            <div class="flex items-center gap-space-sm">
                <span class="font-headline-sm text-headline-sm font-bold tracking-tight text-primary">
                    Baby Corner
                </span>

                <span class="px-2 py-0.5 rounded-full bg-surface-container text-on-primary-container font-label-sm text-label-sm">
                    Backoffice
                </span>
            </div>
        </div>

        <div class="px-space-md py-space-sm">

            <div class="px-space-sm pb-space-xs font-label-sm text-label-sm uppercase tracking-wider text-outline">
                Menu Navigasi
            </div>

            <nav class="flex flex-col gap-1">

                {{-- 1. DASHBOARD / BERANDA --}}
                <a class="{{ $item }} {{ request()->routeIs('admin.dashboard') ? $active : $idle }}"
                   data-path="dashboard"
                   href="{{ route('admin.dashboard') }}">

                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    <span>Dashboard / Beranda</span>
                </a>

                {{-- 2. KELOLA PRODUK & STOK (Fitur 8) --}}
                <a class="{{ $item }} {{ request()->routeIs('admin.produk.*') ? $active : $idle }}"
                   data-path="kelola-produk"
                   href="{{ route('admin.produk.index') }}">
                    <span class="material-symbols-outlined text-[20px]">styler</span>
                    <span>Kelola Produk &amp; Stok</span>
                </a>

                {{-- LOKASI & STOK TOKO (Fitur 9) --}}
                <a class="{{ $item }} {{ request()->routeIs('admin.lokasi.*') ? $active : $idle }}"
                   data-path="lokasi-stok"
                   href="{{ route('admin.lokasi.index') }}">
                    <span class="material-symbols-outlined text-[20px]">warehouse</span>
                    <span>Lokasi &amp; Stok Toko</span>
                </a>

                {{-- PEMESANAN PEMASOK (Fitur 10) --}}
                <a class="{{ $item }} {{ request()->routeIs('admin.pemesanan.*') ? $active : $idle }}"
                   data-path="pemesanan-pemasok"
                   href="{{ route('admin.pemesanan.index') }}">
                    <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                    <span>Pemesanan Pemasok</span>
                </a>

                {{-- PENERIMAAN BARANG (Fitur 11) --}}
                <a class="{{ $item }} {{ request()->routeIs('admin.penerimaan.*') ? $active : $idle }}"
                   data-path="penerimaan-barang"
                   href="{{ route('admin.penerimaan.index') }}">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                    <span>Penerimaan Barang</span>
                </a>

                {{-- 3. PESANAN (FRI-04) --}}
                <a class="{{ $item }} {{ request()->routeIs('admin.pesanan.*') ? $active : $idle }}"
                   data-path="pesanan"
                   href="{{ route('admin.pesanan.index') }}">

                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    <span>Pesanan</span>
                </a>

                {{-- 4. PELANGGAN --}}
                <a class="{{ $item }} {{ $idle }}"
                   data-path="pelanggan"
                   href="#">

                    <span class="material-symbols-outlined text-[20px]">group</span>
                    <span>Pelanggan</span>
                </a>

                {{-- 5. LAPORAN PENJUALAN --}}
                <details class="group" @if ($inLaporan) open @endif>

                    <summary class="{{ $item }} cursor-pointer list-none {{ $inLaporan ? $active : $idle }}"
                             data-path="laporan-penjualan">

                        <span class="material-symbols-outlined text-[20px]">analytics</span>

                        <span class="flex-1">Laporan Penjualan</span>

                        <span class="material-symbols-outlined text-[18px] transition-transform group-open:rotate-180">
                            expand_more
                        </span>
                    </summary>

                    <div class="mt-1 ml-6 pl-3 border-l border-outline-variant flex flex-col gap-1">

                        {{-- Pembukuan Keuangan --}}
                        <a class="{{ $sub }} {{ request()->routeIs('admin.keuangan.index', 'admin.keuangan.create', 'admin.keuangan.edit') ? $subActive : $subIdle }}"
                           href="{{ route('admin.keuangan.index') }}">
                            Pembukuan Keuangan
                        </a>

                        {{-- Laporan Keuangan --}}
                        <a class="{{ $sub }} {{ request()->routeIs('admin.keuangan.laporan') ? $subActive : $subIdle }}"
                           href="{{ route('admin.keuangan.laporan') }}">
                            Laporan Keuangan
                        </a>

                    </div>
                </details>

                {{-- 6. FORECASTING & STOK --}}
                <a class="{{ $item }} {{ $inForecast ? $active : $idle }}"
                   data-path="forecasting-stok"
                   href="{{ route('admin.bi.forecast') }}">

                    <span class="material-symbols-outlined text-[20px]">query_stats</span>
                    <span>Forecasting &amp; Stok</span>
                </a>

                {{-- 7. PENGATURAN TOKO --}}
                <a class="{{ $item }} {{ $idle }}"
                   data-path="pengaturan-toko"
                   href="#">

                    <span class="material-symbols-outlined text-[20px]">settings</span>
                    <span>Pengaturan Toko</span>
                </a>

            </nav>
        </div>
    </div>

    {{-- LOGOUT --}}
    <div class="p-space-md">

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-error-container text-on-error-container text-xs font-semibold hover:opacity-90 transition-all">

                <span class="material-symbols-outlined text-sm">logout</span>
                <span>Keluar Admin</span>

            </button>
        </form>

    </div>

</aside>
