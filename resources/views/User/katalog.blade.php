@extends('User.layout')

@section('title', 'Katalog Pakaian Bayi Katun Organik - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Katalog Produk</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">Katalog Pakaian Bayi</h1>
            <p class="text-xs text-on-surface-variant">Pilihan pakaian bayi 100% katun organik bersertifikasi aman untuk kulit sensitif</p>
        </div>
        <div class="text-xs text-outline">
            Menampilkan <strong>{{ $products->total() }}</strong> produk
        </div>
    </div>

    <!-- FILTER & PENCARIAN -->
    <form action="{{ route('produk.index') }}" method="GET" class="bg-surface-container-lowest p-4 sm:p-5 rounded-2xl shadow-sm border border-surface-container space-y-4">
        
        <div class="flex flex-col lg:flex-row items-center justify-between gap-3">
            <!-- Search Input -->
            <div class="relative w-full lg:w-96">
                <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari baju, jumper, bedong, kaos..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
            </div>

            <!-- Category & Sort Dropdowns -->
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-end">
                
                <!-- Category Dropdown -->
                <div class="relative flex-1 sm:flex-none">
                    <select name="category" onchange="this.form.submit()" class="w-full sm:w-auto h-11 pl-3 pr-8 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 appearance-none cursor-pointer font-medium">
                        <option value="Semua" {{ request('category') == 'Semua' || !request('category') ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-3 text-outline text-sm pointer-events-none">expand_more</span>
                </div>

                <!-- Sort Dropdown -->
                <div class="relative flex-1 sm:flex-none">
                    <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto h-11 pl-3 pr-8 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 appearance-none cursor-pointer font-medium">
                        <option value="terbaru" {{ request('sort') == 'terbaru' || !request('sort') ? 'selected' : '' }}>Terbaru</option>
                        <option value="harga_terendah" {{ request('sort') == 'harga_terendah' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="harga_tertinggi" {{ request('sort') == 'harga_tertinggi' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-3 text-outline text-sm pointer-events-none">expand_more</span>
                </div>

                <!-- Submit Button on mobile -->
                <button type="submit" class="h-11 px-4 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center justify-center gap-1 shadow-xs shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">tune</span>
                    <span class="hidden sm:inline">Terapkan</span>
                </button>
            </div>
        </div>

        <!-- Ukuran Filter Pills (dari size_guides) -->
        <div class="pt-3 border-t border-surface-container/60 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-outline mr-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">straighten</span>
                <span>Ukuran:</span>
            </span>

            <a href="{{ route('produk.index', array_merge(request()->except('size', 'page'), ['size' => 'Semua'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('size') == 'Semua' || !request('size') ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface hover:bg-surface-container' }}">
                Semua
            </a>

            @foreach($sizeGuides as $guide)
                <a href="{{ route('produk.index', array_merge(request()->except('size', 'page'), ['size' => $guide->ukuran])) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request('size') == $guide->ukuran ? 'bg-primary text-on-primary font-semibold' : 'bg-surface-container-low text-on-surface hover:bg-surface-container' }}">
                    {{ $guide->ukuran }}
                </a>
            @endforeach

            @if(request()->filled('q') || (request()->filled('category') && request('category') !== 'Semua') || (request()->filled('size') && request('size') !== 'Semua'))
                <a href="{{ route('produk.index') }}" class="ml-auto text-xs text-error hover:underline flex items-center gap-1 font-semibold">
                    <span class="material-symbols-outlined text-xs">close</span>
                    <span>Reset Filter</span>
                </a>
            @endif
        </div>

    </form>

    <!-- GRID PRODUK -->
    @if($products->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
                @include('User.components.product-card', ['product' => $product])
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $products->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="w-full bg-surface-container-lowest rounded-3xl p-16 text-center shadow-sm border border-surface-container flex flex-col items-center justify-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">search_off</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-on-surface">Produk Tidak Ditemukan</h3>
            <p class="text-xs text-on-surface-variant max-w-md">
                Tidak ada pakaian bayi yang cocok dengan kriteria pencarian Anda. Silakan coba kata kunci lain atau reset filter Anda.
            </p>
            <a href="{{ route('produk.index') }}" class="mt-2 px-5 py-2.5 rounded-full bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all shadow-xs inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">refresh</span>
                <span>Lihat Semua Produk</span>
            </a>
        </div>
    @endif

</div>
@endsection

