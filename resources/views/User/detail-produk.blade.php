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
            <!-- Slot untuk ulasan pembeli dan penilaian produk -->

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
