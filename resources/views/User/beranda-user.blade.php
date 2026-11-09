@extends('User.layout')

@section('title', 'Baby Corner - Sentuhan Lembut untuk Si Kecil')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-8 pb-16">
    
    <!-- HERO BANNER SECTION -->
    <div class="w-full bg-gradient-to-r from-primary-fixed/40 via-surface-variant/50 to-secondary-fixed/30 rounded-3xl p-6 sm:p-10 shadow-sm relative overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
        
        <div class="lg:col-span-7 z-10 space-y-4">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 shadow-xs text-primary text-[11px] font-semibold">
                <span class="material-symbols-outlined text-xs">verified</span>
                <span>100% Katun Organik Bersertifikasi OEKO-TEX</span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-on-surface tracking-tight leading-tight">
                Sentuhan Lembut untuk Awal Kehidupan Si Kecil
            </h1>
            
            <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed max-w-lg">
                Bahan 100% katun organik bersertifikasi OEKO-TEX, ekstra lembut, higienis, dan sejuk untuk menjaga kenyamanan kulit paling sensitif.
            </p>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ route('produk.index') }}" class="px-5 py-2.5 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-semibold text-xs shadow-md transition-all flex items-center gap-2">
                    <span>Belanja Sekarang</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="flex items-center gap-2 pt-2 text-[11px] text-on-surface-variant">
                <span class="material-symbols-outlined text-secondary text-sm">local_shipping</span>
                <span>Gratis ongkir & pengemasan higienis medical-grade</span>
            </div>
        </div>

        <div class="lg:col-span-5 z-10 flex justify-center">
            <div class="relative w-full max-w-md rounded-2xl overflow-hidden shadow-lg h-64 sm:h-72 bg-white">
                <img src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&q=80&w=800" class="w-full h-full object-cover" alt="Nursery Room">
                <div class="absolute bottom-3 left-3 right-3 bg-white/90 backdrop-blur-md p-3 rounded-xl shadow-sm flex items-center justify-between text-xs font-semibold">
                    <span class="text-on-surface">⭐ 4.9/5 • Bunda Percaya</span>
                    <span class="text-primary">Kualitas & Keamanan Anti Alergi</span>
                </div>
            </div>
        </div>

        <!-- Decorative blur element -->
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-secondary-fixed/20 blur-3xl pointer-events-none"></div>
    </div>

    <!-- SEARCH & FILTER SECTION -->
    <div class="bg-surface-container-lowest p-4 rounded-2xl shadow-sm border border-surface-container">
        <form action="{{ route('produk.index') }}" method="GET" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative w-full md:w-80">
                <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">search</span>
                <input type="text" name="q" placeholder="Cari katun organik, jumper, kaos..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
            </div>

            <!-- Size Filter Pills from size_guides & Sorting -->
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none max-w-full">
                    <a href="{{ route('produk.index') }}" class="px-3 py-2 rounded-xl bg-primary text-on-primary font-semibold text-xs shrink-0 transition-colors">
                        Semua
                    </a>
                    @foreach($sizeGuides as $guide)
                        <a href="{{ route('produk.index', ['size' => $guide->ukuran]) }}" class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs shrink-0 transition-colors">
                            {{ $guide->ukuran }}
                        </a>
                    @endforeach
                </div>

                <!-- Sort Dropdown -->
                <div class="relative shrink-0">
                    <select name="sort" onchange="this.form.submit()" class="h-11 pl-3 pr-8 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 appearance-none cursor-pointer font-medium">
                        <option value="terbaru">Terbaru</option>
                        <option value="harga_terendah">Harga Terendah</option>
                        <option value="harga_tertinggi">Harga Tertinggi</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-3 text-outline text-sm pointer-events-none">expand_more</span>
                </div>
            </form>
        </div>
    </div>

    <!-- PRODUK TERBARU SECTION -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-on-surface">Koleksi Terbaru</h2>
                <p class="text-xs text-on-surface-variant">Pilihan pakaian katun organik lembut untuk kenyamanan harian si kecil</p>
            </div>
            <a href="{{ route('produk.index') }}" class="text-xs font-semibold text-primary hover:text-surface-tint flex items-center gap-1 transition-colors">
                <span>Lihat Semua</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        @if(count($products) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($products as $product)
                    @include('User.components.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <!-- Placeholder Jika Kosong -->
            <div class="w-full bg-surface-container-lowest rounded-3xl p-16 text-center shadow-sm border border-surface-container flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">styler</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-1">Belum Ada Produk</h3>
                <p class="text-xs text-on-surface-variant max-w-sm">
                    Katalog pakaian bayi sedang dipersiapkan. Silakan kembali lagi nanti untuk melihat koleksi terbaru kami.
                </p>
            </div>
        @endif
    </div>

    <!-- TRUST BADGES SECTION -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-surface-container-lowest p-6 rounded-3xl shadow-sm border border-surface-container">
        <div class="flex items-center gap-4 p-3">
            <div class="w-12 h-12 rounded-2xl bg-secondary-fixed/40 text-secondary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">eco</span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-on-surface">100% Katun Organik</h4>
                <p class="text-[11px] text-on-surface-variant">Sertifikasi OEKO-TEX Standard 100 bebas pestisida & zat kimia berbahaya untuk kulit si kecil.</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-3">
            <div class="w-12 h-12 rounded-2xl bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">clean_hands</span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-on-surface">Higienis & Tersegel</h4>
                <p class="text-[11px] text-on-surface-variant">Dikemas dalam steril-bag berstandar medical-grade sebelum dikirim ke rumah Anda.</p>
            </div>
        </div>
    </div>

</div>
@endsection
