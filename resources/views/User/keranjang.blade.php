@extends('User.layout')

@section('title', 'Keranjang Belanja - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.index') }}" class="hover:text-primary transition-colors">Katalog</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Keranjang Belanja</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-surface-container pb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">Keranjang Belanja</h1>
            <p class="text-xs text-on-surface-variant mt-0.5">Periksa dan kelola pakaian bayi pilihan Anda sebelum melanjutkan pemesanan.</p>
        </div>

        @if($summary['items']->isNotEmpty())
            <!-- Tombol Kosongkan Keranjang -->
            <form action="{{ route('keranjang.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh isi keranjang belanja?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-semibold text-error hover:underline flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">remove_shopping_cart</span>
                    <span>Kosongkan Keranjang</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Peringatan Global Jika Ada Masalah Stok -->
    @if($summary['has_stock_issue'])
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-start gap-3 shadow-xs">
            <span class="material-symbols-outlined text-amber-600 text-lg mt-0.5 shrink-0">warning</span>
            <div class="space-y-1">
                <span class="font-bold block">Perhatian: Ketersediaan Stok Berubah</span>
                <p>Beberapa produk dalam keranjang memiliki jumlah yang melebihi sisa stok di toko kami saat ini. Silakan kurangi jumlah item yang bersangkutan agar tombol checkout dapat aktif.</p>
            </div>
        </div>
    @endif

    @if($summary['items']->isEmpty())
        <!-- STATE KERANJANG KOSONG -->
        <div class="w-full py-16 px-4 bg-surface-container-lowest rounded-3xl border border-surface-container flex flex-col items-center justify-center text-center space-y-4 shadow-xs">
            <div class="w-20 h-20 rounded-full bg-surface-container-low flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl text-primary/40">shopping_bag</span>
            </div>
            <div class="space-y-1 max-w-sm">
                <h3 class="text-base sm:text-lg font-bold text-on-surface">Keranjang Belanja Masih Kosong</h3>
                <p class="text-xs text-on-surface-variant">Anda belum menambahkan pakaian si kecil ke dalam keranjang. Temukan koleksi katun organik terbaik kami sekarang.</p>
            </div>
            <a href="{{ route('produk.index') }}" class="mt-2 px-8 h-11 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer">
                <span class="material-symbols-outlined text-sm">grid_view</span>
                <span>Mulai Belanja di Katalog</span>
            </a>
        </div>
    @else
        <!-- KONTEN KERANJANG AKTIF -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Kolom Kiri: Daftar Item Keranjang (8 kolom) -->
            <div class="lg:col-span-8 space-y-4">
                @foreach($summary['items'] as $item)
                    @php
                        $variant = $item->variant;
                        $product = $variant?->product;
                    @endphp

                    <div class="bg-surface-container-lowest rounded-3xl p-4 sm:p-6 border {{ $item->has_stock_issue ? 'border-amber-300 bg-amber-50/20' : 'border-surface-container' }} shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all">
                        
                        <!-- Info Produk & Foto -->
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <!-- Foto Produk -->
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-surface-container-low border border-surface-container overflow-hidden shrink-0 flex items-center justify-center">
                                @if(!empty($product?->photo))
                                    <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                                @else
                                    <span class="material-symbols-outlined text-3xl text-outline/40">styler</span>
                                @endif
                            </div>

                            <!-- Detail Deskripsi -->
                            <div class="space-y-1.5 flex-1 min-w-0">
                                @if($product)
                                    <a href="{{ route('produk.show', $product->id) }}" class="text-sm font-bold text-on-surface hover:text-primary transition-colors line-clamp-1 block">
                                        {{ $product->name }}
                                    </a>
                                @else
                                    <span class="text-sm font-bold text-on-surface">Produk</span>
                                @endif

                                <!-- Badge Varian -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                    <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface text-[11px] font-semibold">
                                        Ukuran: {{ $variant?->ukuran ?: '-' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface text-[11px] font-semibold">
                                        Warna: {{ $variant?->warna ?: '-' }}
                                    </span>
                                </div>

                                <!-- Harga Satuan -->
                                <div class="text-xs font-semibold text-primary pt-0.5">
                                    {{ rupiah($item->unit_price) }} <span class="text-[10px] text-outline font-normal">/ pcs</span>
                                </div>

                                <!-- Pesan Peringatan Stok Per Item -->
                                @if($item->has_stock_issue)
                                    <div class="pt-1 text-[11px] font-bold text-amber-700 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">info</span>
                                        @if($item->available_stock > 0)
                                            <span>Sisa {{ $item->available_stock }} pcs. Harap sesuaikan jumlah.</span>
                                        @else
                                            <span class="text-error">Stok varian ini telah habis.</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Kontrol Jumlah, Subtotal & Tombol Hapus -->
                        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-4 pt-3 sm:pt-0 border-t sm:border-t-0 border-surface-container">
                            
                            <!-- Subtotal Per Item -->
                            <div class="text-right">
                                <span class="text-[10px] text-outline block sm:hidden">Subtotal:</span>
                                <span class="text-sm sm:text-base font-extrabold text-on-surface">
                                    {{ rupiah($item->subtotal) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <!-- Kontrol Qty +/- -->
                                <div class="flex items-center border border-surface-container rounded-xl overflow-hidden bg-surface-container-low h-9 shadow-xs">
                                    <!-- Tombol Kurang (-) -->
                                    <form action="{{ route('keranjang.update', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="jumlah" value="{{ $item->jumlah - 1 }}" />
                                        <button type="submit" 
                                                {{ $item->jumlah <= 1 ? 'disabled' : '' }} 
                                                class="w-8 h-9 flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                                title="Kurangi 1">
                                            <span class="material-symbols-outlined text-xs font-bold">remove</span>
                                        </button>
                                    </form>

                                    <!-- Angka Jumlah -->
                                    <span class="w-10 text-center text-xs font-bold text-on-surface">
                                        {{ $item->jumlah }}
                                    </span>

                                    <!-- Tombol Tambah (+) -->
                                    <form action="{{ route('keranjang.update', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="jumlah" value="{{ $item->jumlah + 1 }}" />
                                        <button type="submit" 
                                                {{ $item->jumlah >= $item->available_stock ? 'disabled' : '' }} 
                                                class="w-8 h-9 flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                                title="{{ $item->jumlah >= $item->available_stock ? 'Mencapai batas stok' : 'Tambah 1' }}">
                                            <span class="material-symbols-outlined text-xs font-bold">add</span>
                                        </button>
                                    </form>
                                </div>

                                <!-- Tombol Hapus Item -->
                                <form action="{{ route('keranjang.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-full hover:bg-error-container text-outline hover:text-error transition-all flex items-center justify-center cursor-pointer" title="Hapus dari keranjang">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>

                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Kolom Kanan: Ringkasan Belanja (4 kolom) -->
            <div class="lg:col-span-4">
                <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-7 border border-surface-container shadow-sm space-y-6 sticky top-28">
                    
                    <h3 class="text-base font-bold text-on-surface border-b border-surface-container pb-3">
                        Ringkasan Pesanan
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between text-on-surface-variant">
                            <span>Total Item</span>
                            <span class="font-semibold text-on-surface">{{ $summary['jumlah_item'] }} pcs</span>
                        </div>

                        <div class="flex items-center justify-between text-on-surface-variant">
                            <span>Subtotal Produk</span>
                            <span class="font-semibold text-on-surface">{{ rupiah($summary['total_price']) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-on-surface-variant">
                            <span>Estimasi Ongkos Kirim</span>
                            <span class="text-outline text-[11px] italic">Dihitung saat checkout</span>
                        </div>

                        <div class="pt-3 border-t border-surface-container flex items-baseline justify-between">
                            <span class="text-sm font-bold text-on-surface">Total Belanja</span>
                            <span class="text-xl font-extrabold text-primary">
                                {{ rupiah($summary['total_price']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Checkout -->
                    {{-- TODO FRI-04: tautkan ke route('checkout.index') setelah fitur checkout selesai --}}
                    @if($summary['has_stock_issue'])
                        <button type="button" 
                                disabled 
                                class="w-full h-12 rounded-2xl bg-surface-container text-outline font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed transition-all shadow-xs" 
                                title="Perbaiki jumlah item yang melebihi stok terlebih dahulu">
                            <span class="material-symbols-outlined text-base">lock</span>
                            <span>Checkout Tidak Tersedia</span>
                        </button>
                        <p class="text-[11px] text-amber-700 text-center font-medium">
                            Ada item yang melebihi stok tersedia. Harap sesuaikan sebelum checkout.
                        </p>
                    @else
                        <a href="#" 
                           class="w-full h-12 rounded-2xl bg-primary hover:bg-surface-tint text-on-primary font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer"
                           onclick="alert('Fitur Checkout akan segera hadir pada FRI-04.'); return false;">
                            <span>Lanjut ke Checkout</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    @endif

                    <div class="pt-4 border-t border-surface-container/60 space-y-2 text-[11px] text-outline">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary text-sm">verified_user</span>
                            <span>Bahan 100% Organik & Aman bagi Bayi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-sm">local_shipping</span>
                            <span>Pengemasan Higienis & Aman</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    @endif

</div>
@endsection
