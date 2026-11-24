@extends('Admin.layout')

@section('title', 'Kelola Produk & Stok')
@section('page-title', 'Kelola Produk & Pemisahan Stok Toko')
@section('page-subtitle', 'Katalog busana bayi, pemisahan alokasi stok di toko fisik vs gudang, dan status ketersediaan')

@section('content')
<div class="flex flex-col w-full gap-6">

    <!-- ACTION HEADER & FILTER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs">
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('admin.produk.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative w-full sm:w-72">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk, SKU..." class="w-full h-10 pl-9 pr-3 text-xs bg-surface-container-low rounded-xl border-none focus:ring-2 focus:ring-primary/30 outline-none text-on-surface" />
            </div>

            <select name="category" onchange="this.form.submit()" class="h-10 px-3 text-xs bg-surface-container-low rounded-xl border-none focus:ring-2 focus:ring-primary/30 outline-none text-on-surface">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>

            @if(request('search') || request('category'))
                <a href="{{ route('admin.produk.index') }}" class="text-xs text-outline hover:text-error">Reset Filter</a>
            @endif
        </form>

        <!-- Tombol Tambah Produk (Sesuai Diagram 1) -->
        <a href="{{ route('admin.produk.create') }}" class="px-5 h-10 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center justify-center gap-2 shadow-xs shrink-0">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>Tambah Produk Baru</span>
        </a>
    </div>

    <!-- TABEL DAFTAR PRODUK & PEMISAHAN STOK -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden">
        <div class="p-5 border-b border-surface-container flex items-center justify-between">
            <div class="space-y-0.5">
                <h3 class="text-sm font-bold text-on-surface">Daftar Produk & Rincian Stok Lokasi</h3>
                <p class="text-xs text-on-surface-variant">Pemisahan alokasi stok di toko (etalase) dan gudang penyimpanan</p>
            </div>
            <span class="text-xs text-outline">Total: <strong>{{ $products->total() }}</strong> Produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-surface-container-low/60 text-outline font-semibold border-b border-surface-container">
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Kategori & Varian</th>
                        <th class="py-3.5 px-4">Harga Jual</th>
                        <!-- Kolom Pemisahan Stok per Lokasi (Fitur 8 & 9) -->
                        <th class="py-3.5 px-4">Rincian Stok Toko vs Gudang</th>
                        <th class="py-3.5 px-4 text-center">Total Stok</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @forelse($products as $product)
                    <tr class="hover:bg-surface-container-low/40 transition-colors">
                        <!-- Info Produk -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-surface-container-low flex items-center justify-center overflow-hidden shrink-0 border border-surface-container">
                                    @if($product->photo)
                                        <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                                    @else
                                        <span class="material-symbols-outlined text-outline text-xl">styler</span>
                                    @endif
                                </div>
                                <div class="space-y-0.5">
                                    <div class="font-bold text-on-surface text-[13px]">{{ $product->name }}</div>
                                    <div class="text-[11px] text-outline font-mono">SKU: {{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Kategori & Varian -->
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 rounded-md bg-surface-container text-on-surface text-[10px] font-semibold">{{ $product->category }}</span>
                                <div class="text-[11px] text-on-surface-variant">
                                    {{ $product->variants ?? ($product->size . ' ' . $product->color) ?: '-' }}
                                </div>
                            </div>
                        </td>

                        <!-- Harga -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-on-surface">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-outline">Modal: Rp {{ number_format($product->cost_price, 0, ',', '.') }}</div>
                        </td>

                        <!-- Pemisahan Stok Toko vs Gudang (Fitur 8) -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col gap-1 max-w-xs">
                                @forelse($product->stocks as $stock)
                                    <div class="flex items-center justify-between text-[11px] px-2 py-0.5 rounded bg-surface-container-low">
                                        <span class="text-on-surface-variant flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs text-outline">{{ $stock->location->type === 'toko' ? 'storefront' : 'warehouse' }}</span>
                                            {{ $stock->location->name ?? 'Lokasi' }}:
                                        </span>
                                        <span class="font-bold text-on-surface">{{ $stock->quantity }} pcs</span>
                                    </div>
                                @empty
                                    <span class="text-[11px] text-outline italic">Belum ada alokasi stok</span>
                                @endforelse
                            </div>
                        </td>

                        <!-- Total Stok -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-sm font-extrabold {{ $product->is_out_of_stock ? 'text-red-600' : ($product->is_low_stock ? 'text-amber-600' : 'text-on-surface') }}">
                                {{ $product->total_stock }}
                            </span>
                            <div class="text-[10px] text-outline">Min: {{ $product->min_stock }}</div>
                        </td>

                        <!-- Status Badge (Fitur 11 Peringatan Stok) -->
                        <td class="py-3.5 px-4 text-center">
                            @if($product->is_out_of_stock)
                                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-bold text-[10px]">Stok Habis</span>
                            @elseif($product->is_low_stock)
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">Stok Menipis</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-800 font-semibold text-[10px]">Aman</span>
                            @endif
                        </td>

                        <!-- Aksi (Edit & Hapus dengan Konfirmasi Sesuai Diagram 1) -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.produk.edit', $product->id) }}" class="w-8 h-8 rounded-lg bg-surface-container text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all shadow-xs" title="Edit Produk">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                </a>

                                <form action="{{ route('admin.produk.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ addslashes($product->name) }} beserta stoknya? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-error-container text-on-error-container flex items-center justify-center hover:bg-error hover:text-white transition-all shadow-xs cursor-pointer" title="Hapus Produk">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-outline">
                            <span class="material-symbols-outlined text-4xl mb-2 text-outline/50 block">inventory_2</span>
                            Tidak ada produk ditemukan. Silakan tambah produk baru!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-surface-container">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
