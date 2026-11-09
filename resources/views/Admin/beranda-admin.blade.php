@extends('Admin.layout')

@section('title', 'Dashboard Backoffice')
@section('page-title', 'Dashboard Utama Inventaris')
@section('page-subtitle', 'Pantau stok pakaian bayi, peringatan batas minimum, dan alur pemesanan pemasok')

@section('content')
<div class="flex flex-col w-full gap-8">

    <!-- Welcome Banner Card -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-surface-container">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
                <span class="text-[11px] font-bold text-secondary uppercase tracking-wider">Sistem Inventaris Aktif</span>
            </div>
            <h1 class="text-2xl font-bold text-on-surface tracking-tight">Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}!</h1>
            <p class="text-xs text-on-surface-variant max-w-2xl">Kelola produk baju bayi katun organik, alokasikan stok toko & gudang, serta proses penerimaan barang dari pemasok.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-surface-container-low text-on-surface text-xs font-semibold">
                <span class="material-symbols-outlined text-base text-primary">calendar_today</span>
                <span>{{ date('d M Y') }}</span>
            </div>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Produk -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[11px] font-semibold text-outline uppercase tracking-wider">Total Produk</div>
                <div class="text-2xl font-bold text-on-surface">{{ $totalProducts ?? 0 }}</div>
                <div class="text-[11px] text-on-surface-variant">Katalog baju bayi</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary-container/30 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">styler</span>
            </div>
        </div>

        <!-- Total Lokasi Toko & Gudang -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[11px] font-semibold text-outline uppercase tracking-wider">Lokasi Toko & Gudang</div>
                <div class="text-2xl font-bold text-on-surface">{{ $totalLocations ?? 0 }}</div>
                <div class="text-[11px] text-on-surface-variant">Pemisahan titik stok</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-secondary-container/50 text-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">warehouse</span>
            </div>
        </div>

        <!-- Stok Menipis -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider">Stok Menipis</div>
                <div class="text-2xl font-bold text-amber-600">{{ count($lowStockProducts ?? []) }}</div>
                <div class="text-[11px] text-amber-600 font-medium">&le; batas minimum</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">warning</span>
            </div>
        </div>

        <!-- Stok Habis -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[11px] font-semibold text-error uppercase tracking-wider">Stok Habis (0)</div>
                <div class="text-2xl font-bold text-error">{{ count($outOfStockProducts ?? []) }}</div>
                <div class="text-[11px] text-error font-medium">Perlu restock segera</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-error-container text-on-error-container flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">inventory</span>
            </div>
        </div>
    </div>

    <!-- PERINGATAN STOK HABIS & MENIPIS (Fitur 11 Alert Section) -->
    @if(count($outOfStockProducts ?? []) > 0 || count($lowStockProducts ?? []) > 0)
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-amber-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600 text-xl">notification_important</span>
                <h3 class="text-sm font-bold text-on-surface">Peringatan Stok Minimum (Fitur 11)</h3>
            </div>
            <a href="{{ route('admin.pemesanan.create') }}" class="px-3.5 py-1.5 rounded-full bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                <span>Pesan ke Pemasok Sekarang</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($outOfStockProducts as $prod)
                <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-red-900">{{ $prod->name }}</div>
                        <div class="text-[11px] text-red-700">SKU: {{ $prod->sku }} • Varian: {{ $prod->variants->pluck('variant_label')->filter()->implode(', ') ?: '-' }}</div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-red-600 text-white font-bold text-[10px]">Stok 0</span>
                </div>
            @endforeach

            @foreach($lowStockProducts as $prod)
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-amber-900">{{ $prod->name }}</div>
                        <div class="text-[11px] text-amber-700">SKU: {{ $prod->sku }} • Varian: {{ $prod->variants->pluck('variant_label')->filter()->implode(', ') ?: '-' }}</div>
                        <div class="text-[11px] text-amber-700">Sisa: {{ $prod->total_stock }} pcs (Min: {{ $prod->min_stock }})</div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-amber-500 text-white font-bold text-[10px]">Menipis</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 4 FITUR UTAMA SHORTCUT CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Fitur 8 -->
        <a href="{{ route('admin.produk.index') }}" class="p-5 rounded-2xl bg-surface-container-lowest border border-surface-container hover:border-primary/40 hover:shadow-md transition-all group flex flex-col justify-between h-44">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                    <span class="material-symbols-outlined text-xl">styler</span>
                </div>
                <h4 class="text-sm font-bold text-on-surface">Fitur 8: Kelola Produk & Pemisahan Stok</h4>
                <p class="text-[11px] text-on-surface-variant line-clamp-2">CRUD katalog baju bayi dan alokasi stok per toko/gudang.</p>
            </div>
            <div class="flex items-center text-xs font-semibold text-primary gap-1">
                <span>Buka Menu</span>
                <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </div>
        </a>

        <!-- Fitur 9 -->
        <a href="{{ route('admin.lokasi.index') }}" class="p-5 rounded-2xl bg-surface-container-lowest border border-surface-container hover:border-primary/40 hover:shadow-md transition-all group flex flex-col justify-between h-44">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center group-hover:bg-secondary group-hover:text-white transition-all">
                    <span class="material-symbols-outlined text-xl">warehouse</span>
                </div>
                <h4 class="text-sm font-bold text-on-surface">Fitur 9: Pencatatan Lokasi & Stok</h4>
                <p class="text-[11px] text-on-surface-variant line-clamp-2">Kelola master titik toko/gudang dan atur stok produk pada lokasi tertentu.</p>
            </div>
            <div class="flex items-center text-xs font-semibold text-secondary gap-1">
                <span>Buka Menu</span>
                <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </div>
        </a>

        <!-- Fitur 10 -->
        <a href="{{ route('admin.pemesanan.index') }}" class="p-5 rounded-2xl bg-surface-container-lowest border border-surface-container hover:border-primary/40 hover:shadow-md transition-all group flex flex-col justify-between h-44">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-all">
                    <span class="material-symbols-outlined text-xl">local_shipping</span>
                </div>
                <h4 class="text-sm font-bold text-on-surface">Fitur 10: Pemesanan ke Pemasok</h4>
                <p class="text-[11px] text-on-surface-variant line-clamp-2">Catat pesanan barang (PO) baru ke konveksi/supplier baju bayi.</p>
            </div>
            <div class="flex items-center text-xs font-semibold text-amber-700 gap-1">
                <span>Buka Menu</span>
                <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </div>
        </a>

        <!-- Fitur 11 -->
        <a href="{{ route('admin.penerimaan.index') }}" class="p-5 rounded-2xl bg-surface-container-lowest border border-surface-container hover:border-primary/40 hover:shadow-md transition-all group flex flex-col justify-between h-44">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-700 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all">
                    <span class="material-symbols-outlined text-xl">inventory_2</span>
                </div>
                <h4 class="text-sm font-bold text-on-surface">Fitur 11: Penerimaan Barang & Stok</h4>
                <p class="text-[11px] text-on-surface-variant line-clamp-2">Konfirmasi barang masuk, otomatis tambah stok, dan cek batas minimum.</p>
            </div>
            <div class="flex items-center text-xs font-semibold text-purple-700 gap-1">
                <span>Buka Menu</span>
                <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </div>
        </a>
    </div>

    <!-- DAFTAR PEMESANAN TERBARU -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="space-y-0.5">
                <h3 class="text-sm font-bold text-on-surface">Riwayat Pemesanan ke Pemasok Terbaru</h3>
                <p class="text-xs text-on-surface-variant">Catatan pesanan barang restock ke supplier</p>
            </div>
            <a href="{{ route('admin.pemesanan.index') }}" class="text-xs font-semibold text-primary hover:underline">Lihat Semua &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-surface-container text-outline font-semibold">
                        <th class="py-3 px-3">No. PO</th>
                        <th class="py-3 px-3">Pemasok</th>
                        <th class="py-3 px-3">Tujuan Stok</th>
                        <th class="py-3 px-3">Tanggal Pesan</th>
                        <th class="py-3 px-3">Total Biaya</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @forelse($recentOrders ?? [] as $po)
                    <tr class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-3 font-semibold text-primary">{{ $po->po_number }}</td>
                        <td class="py-3 px-3 font-medium text-on-surface">{{ $po->supplier->name ?? '-' }}</td>
                        <td class="py-3 px-3 text-on-surface-variant">{{ $po->targetLocation->name ?? '-' }}</td>
                        <td class="py-3 px-3 text-on-surface-variant">{{ date('d M Y', strtotime($po->order_date)) }}</td>
                        <td class="py-3 px-3 font-semibold text-on-surface">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-3">
                            @if($po->status === 'received')
                                <span class="px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container font-semibold text-[10px]">Barang Diterima</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-[10px]">Menunggu Barang</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right">
                            @if($po->status === 'pending')
                                <a href="{{ route('admin.penerimaan.show', $po->id) }}" class="px-3 py-1 rounded-lg bg-primary text-on-primary text-[11px] font-semibold hover:bg-surface-tint transition-all">
                                    Proses Terima
                                </a>
                            @else
                                <a href="{{ route('admin.pemesanan.show', $po->id) }}" class="px-3 py-1 rounded-lg bg-surface-container text-on-surface text-[11px] font-semibold hover:bg-surface-container-high transition-all">
                                    Detail
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-outline">Belum ada data pemesanan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
