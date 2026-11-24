@extends('User.layout')

@section('title', $product->name . ' - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-8 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.index') }}" class="hover:text-primary transition-colors">Katalog Produk</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.index', ['category' => $product->category]) }}" class="hover:text-primary transition-colors">{{ $product->category }}</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold truncate max-w-[200px]">{{ $product->name }}</span>
    </nav>

    <!-- Detail Produk Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" 
         x-data="variantSelector({
             variants: {{ Js::from($variantsData) }},
             productPrice: {{ (float) $product->price }},
             recommendedSize: '{{ $recommendation['ukuran_utama'] ?? '' }}',
             alternativeSize: '{{ $recommendation['alternatif'] ?? '' }}'
         })">
        
        <!-- Kolom Kiri: Galeri Foto Produk -->
        <div class="lg:col-span-5 space-y-4">
            <div class="relative w-full aspect-square rounded-3xl bg-surface-container-low border border-surface-container overflow-hidden flex items-center justify-center shadow-xs">
                @if(!empty($product->photo))
                    <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                @else
                    <div class="flex flex-col items-center justify-center text-outline/40 p-8 text-center">
                        <span class="material-symbols-outlined text-7xl text-primary/30 mb-2">styler</span>
                        <span class="text-xs font-semibold text-outline">Baby Corner Organik</span>
                        <span class="text-[10px] text-outline/60 mt-1">100% Katun Organik Bersertifikasi</span>
                    </div>
                @endif

                <!-- Badge Kategori -->
                <span class="absolute top-4 left-4 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-xs font-semibold text-on-surface shadow-xs">
                    {{ $product->category }}
                </span>
            </div>

            <!-- Jaminan Kualitas di Bawah Foto -->
            <div class="grid grid-cols-2 gap-3 text-[11px] text-on-surface-variant">
                <div class="p-3 rounded-2xl bg-surface-container-low flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-base shrink-0">verified</span>
                    <span>OEKO-TEX Certified</span>
                </div>
                <div class="p-3 rounded-2xl bg-surface-container-low flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base shrink-0">sanitizer</span>
                    <span>Medical-grade Pack</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Informasi, Rekomendasi Ukuran & Pemilih Varian -->
        <div class="lg:col-span-7 bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-sm space-y-6">
            
            <!-- Judul & Harga -->
            <div class="space-y-2 pb-4 border-b border-surface-container">
                <div class="text-[11px] font-mono text-outline">SKU: <span x-text="currentVariant ? currentVariant.sku : '{{ $product->sku }}'"></span></div>
                <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight leading-snug">
                    {{ $product->name }}
                </h1>
                
                <!-- Rating Ringkas di Bawah Judul (FRI-07) -->
                <div class="flex items-center gap-2 pt-0.5">
                    <div class="flex items-center text-amber-500 font-bold text-xs gap-1">
                        <span class="material-symbols-outlined text-base fill-current" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span>{{ number_format($averageRating, 1) }}</span>
                    </div>
                    <span class="text-xs text-outline">•</span>
                    <a href="#section-ulasan" class="text-xs text-primary font-semibold hover:underline">
                        {{ $totalReviews }} Ulasan Pembeli
                    </a>
                </div>

                <div class="flex items-baseline gap-3 pt-1">
                    <div class="text-2xl sm:text-3xl font-extrabold text-primary">
                        {{ rupiah($product->price) }}
                    </div>
                    <span class="text-[11px] text-outline">Harga per potong</span>
                </div>
            </div>

            <!-- SLOT FRI-01: Rekomendasi Ukuran -->
            {{-- SLOT FRI-01: rekomendasi ukuran --}}
            @if($baby && $recommendation)
                @php
                    $recSize = $recommendation['ukuran_utama'];
                    $altSize = $recommendation['alternatif'];
                    $hasRecInProduct = $recSize ? ($product->variants->where('ukuran', $recSize)->count() > 0) : false;
                    $recStockInProduct = $recSize ? $product->variants->where('ukuran', $recSize)->sum(fn($v) => $v->stokTersedia()) : 0;
                    $hasAltInProduct = $altSize ? ($product->variants->where('ukuran', $altSize)->count() > 0) : false;
                    $altStockInProduct = $altSize ? $product->variants->where('ukuran', $altSize)->sum(fn($v) => $v->stokTersedia()) : 0;
                @endphp

                <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-primary/10 via-primary-fixed/20 to-surface-container-low border border-primary/20 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shrink-0 shadow-xs">
                                <span class="material-symbols-outlined text-sm">child_care</span>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-on-surface">
                                    Rekomendasi untuk {{ $baby->nama_bayi }}
                                </h3>
                                <p class="text-[11px] text-on-surface-variant">
                                    Usia: {{ $baby->usia_bulan }} bulan • Berat: {{ $baby->berat_badan ? $baby->berat_badan . ' kg' : '-' }} • Tinggi: {{ $baby->tinggi_badan ? $baby->tinggi_badan . ' cm' : '-' }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit', ['redirect' => request()->getRequestUri()]) }}" class="text-[11px] text-primary hover:underline font-semibold shrink-0 flex items-center gap-0.5">
                            <span>Ubah data bayi</span>
                            <span class="material-symbols-outlined text-xs">edit</span>
                        </a>
                    </div>

                    @if($recommendation['status'] === 'di_luar_rentang')
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 leading-relaxed">
                            {{ $recommendation['alasan'] }}
                        </div>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <div class="flex items-baseline gap-2.5">
                                <span class="text-xs font-semibold text-outline">Ukuran Disarankan:</span>
                                <span class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight">
                                    {{ $recSize }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full {{ $recommendation['status'] === 'batas' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }} text-[10px] font-bold">
                                    {{ $recommendation['status'] === 'batas' ? 'Naik 1 Ukuran' : 'Pas' }}
                                </span>
                            </div>

                            @if($altSize)
                                <div class="text-[11px] text-on-surface-variant bg-white/80 px-2.5 py-1 rounded-lg border border-surface-container">
                                    Alternatif: <strong class="text-on-surface">{{ $altSize }}</strong> (jika ingin lebih longgar)
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-on-surface leading-relaxed">
                            {{ $recommendation['alasan'] }}
                        </p>

                        <!-- Pengecekan Ketersediaan Ukuran Rekomendasi di Produk Ini -->
                        @if(!$hasRecInProduct || $recStockInProduct <= 0)
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                                <div class="font-bold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm text-amber-600">info</span>
                                    <span>Ukuran {{ $recSize }} sedang tidak tersedia pada produk ini.</span>
                                </div>
                                @if($hasAltInProduct && $altStockInProduct > 0)
                                    <p class="text-amber-800">
                                        Anda dapat memilih ukuran alternatif <strong>{{ $altSize }}</strong> yang saat ini tersedia (<span class="font-semibold">{{ $altStockInProduct }} pcs</span>).
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

            @else
                <!-- Ajakan untuk user yang belum mengisi data bayi -->
                <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">child_care</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-on-surface">Isi data bayi untuk mendapat rekomendasi ukuran</h4>
                            <p class="text-[11px] text-on-surface-variant">Sistem kami akan memilihkan ukuran pakaian yang paling pas otomatis.</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit', ['redirect' => request()->getRequestUri()]) }}" class="px-4 py-2 rounded-xl bg-primary hover:bg-surface-tint text-on-primary text-xs font-semibold shrink-0 text-center shadow-xs transition-all">
                        Isi Data Bayi Sekarang
                    </a>
                </div>
            @endif

            <!-- Pemilih Varian (Ukuran & Warna) -->
            <div class="space-y-5 pt-2">
                
                <!-- 1. Pemilih Ukuran -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-primary">straighten</span>
                            <span>Pilih Ukuran: <span class="text-primary font-bold" x-text="selectedSize"></span></span>
                        </label>

                        <a href="{{ route('panduan.ukuran') }}" class="text-[11px] text-primary hover:underline font-semibold flex items-center gap-0.5">
                            <span>Panduan Ukuran</span>
                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <template x-for="size in sizes" :key="size">
                            <button type="button"
                                    @click="selectSize(size)"
                                    :class="selectedSize === size 
                                        ? 'border-primary bg-primary/10 text-primary font-bold shadow-xs' 
                                        : 'border-surface-container bg-surface-container-low text-on-surface hover:bg-surface-container'"
                                    class="px-3.5 py-2 rounded-xl border text-xs transition-all cursor-pointer flex items-center gap-1.5 relative">
                                <span x-text="size"></span>
                                <!-- Badge Disarankan jika sama dengan rekomendasi -->
                                <template x-if="recommendedSize && size === recommendedSize">
                                    <span class="px-1.5 py-0.5 rounded-full bg-primary text-white text-[9px] font-bold">
                                        Disarankan
                                    </span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 2. Pemilih Warna (Hanya warna yang tersedia di ukuran terpilih) -->
                <div class="space-y-2" x-show="availableColorsForSelectedSize.length > 0">
                    <label class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-secondary">palette</span>
                        <span>Pilih Warna: <span class="text-secondary font-bold" x-text="selectedColor"></span></span>
                    </label>

                    <div class="flex flex-wrap gap-2">
                        <template x-for="item in availableColorsForSelectedSize" :key="item.warna">
                            <button type="button"
                                    @click="selectColor(item.warna, item.stok_tersedia)"
                                    :disabled="item.stok_tersedia <= 0"
                                    :class="{
                                        'border-secondary bg-secondary/10 text-secondary font-bold shadow-xs': selectedColor === item.warna && item.stok_tersedia > 0,
                                        'border-surface-container bg-surface-container-low text-on-surface hover:bg-surface-container cursor-pointer': selectedColor !== item.warna && item.stok_tersedia > 0,
                                        'opacity-40 cursor-not-allowed line-through border-dashed border-outline/30 bg-surface-container-low text-outline': item.stok_tersedia <= 0
                                    }"
                                    class="px-3.5 py-2 rounded-xl border text-xs transition-all flex items-center gap-1.5">
                                <span x-text="item.warna"></span>
                                <span x-show="item.stok_tersedia <= 0" class="text-[9px] uppercase font-bold text-error">(Habis)</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 3. Status Stok Varian Terpilih -->
                <div class="p-3.5 rounded-2xl bg-surface-container-low flex items-center justify-between text-xs">
                    <span class="text-on-surface-variant font-medium flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-outline">inventory_2</span>
                        <span>Status Ketersediaan:</span>
                    </span>

                    <div>
                        <template x-if="isOutOfStock">
                            <span class="px-2.5 py-1 rounded-full bg-error-container text-error font-bold text-[11px]">
                                Stok Habis
                            </span>
                        </template>
                        <template x-if="!isOutOfStock && currentStock <= 5">
                            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[11px]">
                                Sisa <span x-text="currentStock"></span> pcs
                            </span>
                        </template>
                        <template x-if="!isOutOfStock && currentStock > 5">
                            <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-800 font-bold text-[11px]">
                                Stok Tersedia (<span x-text="currentStock"></span> pcs)
                            </span>
                        </template>
                    </div>
                </div>

            </div>

            <!-- ACTION BUTTONS & SLOTS -->
            <div class="space-y-4 pt-2">
                {{-- SLOT FRI-03: Formulir Tambah ke Keranjang --}}
                <form action="{{ route('keranjang.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_variant_id" :value="currentVariant ? currentVariant.id : ''">

                    <!-- Kontrol Input Jumlah / Qty (Default 1, Maksimal = Stok Tersedia) -->
                    <div class="flex items-center gap-4">
                        <label class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-outline">pin</span>
                            <span>Jumlah:</span>
                        </label>

                        <div class="flex items-center border border-surface-container rounded-xl overflow-hidden bg-surface-container-low h-10 shadow-xs">
                            <button type="button" 
                                    @click="decreaseQty()" 
                                    :disabled="qty <= 1 || isOutOfStock" 
                                    class="w-10 h-full flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                    title="Kurangi jumlah">
                                <span class="material-symbols-outlined text-sm font-bold">remove</span>
                            </button>
                            <input type="number" 
                                   name="jumlah" 
                                   x-model.number="qty" 
                                   @input="validateQty()" 
                                   min="1" 
                                   :max="currentStock" 
                                   :disabled="isOutOfStock" 
                                   class="w-14 text-center text-xs font-bold text-on-surface bg-transparent border-0 focus:outline-none focus:ring-0">
                            <button type="button" 
                                    @click="increaseQty()" 
                                    :disabled="qty >= currentStock || isOutOfStock" 
                                    class="w-10 h-full flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                    title="Tambah jumlah">
                                <span class="material-symbols-outlined text-sm font-bold">add</span>
                            </button>
                        </div>

                        <span class="text-xs text-on-surface-variant" x-show="currentStock > 0">
                            (Maks. <span class="font-bold text-on-surface" x-text="currentStock"></span> pcs)
                        </span>
                    </div>

                    <!-- Tombol Tambah ke Keranjang -->
                    <button type="submit" 
                            :disabled="!currentVariant || isOutOfStock" 
                            :class="(!currentVariant || isOutOfStock) ? 'bg-surface-container text-outline cursor-not-allowed' : 'bg-primary hover:bg-surface-tint text-on-primary shadow-md cursor-pointer'"
                            class="w-full h-12 rounded-2xl font-semibold text-xs flex items-center justify-center gap-2 transition-all">
                        <span class="material-symbols-outlined text-base">shopping_bag</span>
                        <span x-text="isOutOfStock ? 'Stok Varian Habis' : 'Tambah ke Keranjang'"></span>
                    </button>
                </form>

                {{-- SLOT FRI-16: tombol WhatsApp --}}
                <!-- Slot untuk tombol pemesanan WhatsApp cepat -->
            </div>

            <!-- Deskripsi Produk -->
            <div class="pt-4 border-t border-surface-container space-y-2">
                <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider text-outline flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">description</span>
                    <span>Deskripsi Produk</span>
                </h3>
                <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                    {{ $product->description ?: 'Pakaian bayi katun organik berkualitas tinggi, didesain khusus untuk memberikan kelembutan alami dan kenyamanan maksimal bagi buah hati Anda.' }}
                </p>
            </div>

            {{-- SLOT FRI-07: rating & ulasan --}}
            <div id="section-ulasan" class="pt-6 border-t border-surface-container space-y-5">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider text-outline flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">hotel_class</span>
                            <span>Penilaian &amp; Ulasan Pembeli</span>
                        </h3>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            Ulasan dari pelanggan yang telah membeli produk ini.
                        </p>
                    </div>

                    @if($canReview)
                        @if($userReview)
                            <a href="{{ route('ulasan.edit', $userReview) }}" 
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-all">
                                <span class="material-symbols-outlined text-sm text-primary">edit_note</span>
                                <span>Ubah Ulasan Anda</span>
                            </a>
                        @else
                            <a href="{{ route('ulasan.create', $product) }}" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all shadow-xs">
                                <span class="material-symbols-outlined text-sm">rate_review</span>
                                <span>Beri Ulasan Produk</span>
                            </a>
                        @endif
                    @endif
                </div>

                <!-- Ringkasan Nilai & Distribusi Bintang -->
                <div class="p-4 sm:p-5 rounded-2xl bg-surface-container-low border border-surface-container grid grid-cols-1 sm:grid-cols-12 gap-5 items-center">
                    <!-- Skor Rata-rata -->
                    <div class="sm:col-span-5 text-center sm:text-left flex flex-col items-center sm:items-start justify-center border-b sm:border-b-0 sm:border-r border-surface-container pb-4 sm:pb-0 sm:pr-4">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl sm:text-4xl font-extrabold text-on-surface">{{ number_format($averageRating, 1) }}</span>
                            <span class="text-xs text-outline font-semibold">/ 5.0</span>
                        </div>

                        <!-- 5 Star Icons -->
                        <div class="flex items-center text-amber-400 gap-0.5 my-1.5">
                            @for($s = 1; $s <= 5; $s++)
                                <span class="material-symbols-outlined text-lg sm:text-xl" style="font-variation-settings: 'FILL' {{ $s <= round($averageRating) ? '1' : '0' }};">star</span>
                            @endfor
                        </div>

                        <span class="text-xs text-on-surface-variant font-medium">
                            Berdasarkan <strong>{{ $totalReviews }}</strong> ulasan pembeli
                        </span>
                    </div>

                    <!-- Bar Distribusi Bintang 5..1 -->
                    <div class="sm:col-span-7 space-y-1.5 text-xs">
                        @for($star = 5; $star >= 1; $star--)
                            @php
                                $d = $ratingDistribution[$star] ?? ['count' => 0, 'percentage' => 0];
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="w-7 text-right text-[11px] font-bold text-on-surface-variant flex items-center justify-end gap-0.5">
                                    <span>{{ $star }}</span>
                                    <span class="material-symbols-outlined text-xs text-amber-500 fill-current" style="font-variation-settings: 'FILL' 1;">star</span>
                                </span>
                                <div class="flex-1 h-2 rounded-full bg-surface-container overflow-hidden">
                                    <div class="h-full rounded-full bg-amber-400 transition-all duration-300" 
                                         style="width: {{ $d['percentage'] }}%;"></div>
                                </div>
                                <span class="w-8 text-[11px] text-outline text-right font-medium">
                                    {{ $d['count'] }}
                                </span>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Daftar Ulasan Pembeli -->
                @if($reviews->count() > 0)
                    <div class="space-y-3 pt-2 divide-y divide-surface-container">
                        @foreach($reviews as $r)
                            <div class="pt-3 first:pt-0 space-y-2">
                                <div class="flex items-start justify-between gap-3 flex-wrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($r->masked_user_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-on-surface">{{ $r->masked_user_name }}</span>
                                                @if(auth()->check() && $r->user_id === auth()->id())
                                                    <span class="px-2 py-0.5 rounded-full bg-primary-fixed text-primary font-bold text-[10px]">
                                                        Ulasan Anda
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px] text-outline mt-0.5">
                                                <span>{{ $r->created_at->translatedFormat('d M Y') }}</span>
                                                @if($r->orderItem && $r->orderItem->varian_label)
                                                    <span>•</span>
                                                    <span>Varian: <strong class="text-on-surface-variant">{{ $r->orderItem->varian_label }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bintang Ulasan -->
                                    <div class="flex items-center text-amber-400 gap-0.5">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' {{ $s <= $r->rating ? '1' : '0' }};">star</span>
                                        @endfor
                                    </div>
                                </div>

                                <!-- Isi Ulasan (strictly escaped) -->
                                @if(!empty($r->ulasan))
                                    <p class="text-xs text-on-surface-variant leading-relaxed pl-10 whitespace-pre-line">{{ $r->ulasan }}</p>
                                @endif

                                <!-- Tombol Ubah / Hapus untuk Pemilik Ulasan -->
                                @if(auth()->check() && $r->user_id === auth()->id())
                                    <div class="flex items-center justify-end gap-3 pl-10 pt-1 text-xs">
                                        <a href="{{ route('ulasan.edit', $r) }}" class="text-primary font-semibold hover:underline flex items-center gap-1 text-[11px]">
                                            <span class="material-symbols-outlined text-xs">edit</span>
                                            <span>Ubah</span>
                                        </a>
                                        <form action="{{ route('ulasan.destroy', $r) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error font-semibold hover:underline flex items-center gap-1 text-[11px] cursor-pointer">
                                                <span class="material-symbols-outlined text-xs">delete</span>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- State Kosong: Belum Ada Ulasan -->
                    <div class="p-6 rounded-2xl bg-surface-container-low border border-surface-container text-center space-y-2">
                        <span class="material-symbols-outlined text-3xl text-outline">rate_review</span>
                        <h4 class="text-xs font-bold text-on-surface">Belum Ada Ulasan untuk Produk Ini</h4>
                        <p class="text-[11px] text-on-surface-variant max-w-sm mx-auto">
                            @if($canReview)
                                Anda telah membeli pakaian ini. Berikan ulasan pertama Anda untuk membantu pembeli lain!
                            @else
                                Belum ada pelanggan yang menuliskan ulasan untuk produk ini.
                            @endif
                        </p>
                        @if($canReview && !$userReview)
                            <div class="pt-1">
                                <a href="{{ route('ulasan.create', $product) }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all shadow-xs">
                                    <span class="material-symbols-outlined text-sm">star</span>
                                    <span>Tulis Ulasan Pertama</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Produk Terkait (4 Produk Kategori Serupa) -->
    @if($relatedProducts->count() > 0)
        <div class="space-y-4 pt-8 border-t border-surface-container">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-on-surface">Pilihan Lain Kategori {{ $product->category }}</h2>
                <p class="text-xs text-on-surface-variant">Rekomendasi pakaian bayi katun organik sejenis</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($relatedProducts as $rel)
                    @include('User.components.product-card', ['product' => $rel])
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function variantSelector(config) {
        return {
            variants: config.variants,
            sizes: [...new Set(config.variants.map(v => v.ukuran).filter(Boolean))],
            recommendedSize: config.recommendedSize || '',
            alternativeSize: config.alternativeSize || '',
            selectedSize: '',
            selectedColor: '',
            qty: 1,
            
            init() {
                // Pilih ukuran otomatis: prioritaskan rekomendasi ukuran jika ada stoknya
                let targetSize = '';
                if (this.recommendedSize && this.variants.some(v => v.ukuran === this.recommendedSize && v.stok_tersedia > 0)) {
                    targetSize = this.recommendedSize;
                } else if (this.alternativeSize && this.variants.some(v => v.ukuran === this.alternativeSize && v.stok_tersedia > 0)) {
                    targetSize = this.alternativeSize;
                } else {
                    const inStockVariant = this.variants.find(v => v.stok_tersedia > 0);
                    targetSize = inStockVariant ? inStockVariant.ukuran : (this.sizes[0] || '');
                }

                this.selectedSize = targetSize;
                const available = this.variants.filter(v => v.ukuran === targetSize);
                const inStockColor = available.find(v => v.stok_tersedia > 0);
                this.selectedColor = inStockColor ? inStockColor.warna : (available[0] ? available[0].warna : '');
                this.qty = 1;
            },

            get availableColorsForSelectedSize() {
                return this.variants.filter(v => v.ukuran === this.selectedSize);
            },

            get currentVariant() {
                return this.variants.find(v => v.ukuran === this.selectedSize && v.warna === this.selectedColor)
                    || this.availableColorsForSelectedSize[0]
                    || null;
            },

            get currentStock() {
                return this.currentVariant ? parseInt(this.currentVariant.stok_tersedia) : 0;
            },

            get isOutOfStock() {
                return this.currentStock <= 0;
            },

            increaseQty() {
                if (this.qty < this.currentStock) {
                    this.qty++;
                }
            },

            decreaseQty() {
                if (this.qty > 1) {
                    this.qty--;
                }
            },

            validateQty() {
                if (this.qty < 1 || isNaN(this.qty)) this.qty = 1;
                if (this.qty > this.currentStock && this.currentStock > 0) this.qty = this.currentStock;
            },

            selectSize(size) {
                this.selectedSize = size;
                const colors = this.variants.filter(v => v.ukuran === size);
                const sameColor = colors.find(v => v.warna === this.selectedColor);
                if (sameColor && sameColor.stok_tersedia > 0) {
                    // maintain color
                } else {
                    const inStock = colors.find(v => v.stok_tersedia > 0);
                    this.selectedColor = inStock ? inStock.warna : (colors[0] ? colors[0].warna : '');
                }
                this.qty = 1;
            },

            selectColor(color, stock) {
                if (stock <= 0) return;
                this.selectedColor = color;
                this.qty = 1;
            }
        };
    }
</script>
@endpush
