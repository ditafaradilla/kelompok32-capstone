@extends('User.layout')

@section('title', ($isEdit ? 'Ubah Ulasan' : 'Beri Ulasan') . ' - ' . $product->name . ' - Baby Corner')

@section('content')
<div class="w-full max-w-[800px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.index') }}" class="hover:text-primary transition-colors">Katalog</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.show', $product) }}" class="hover:text-primary transition-colors truncate max-w-[150px]">{{ $product->name }}</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">{{ $isEdit ? 'Ubah Ulasan' : 'Beri Ulasan' }}</span>
    </nav>

    <!-- Header Form -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">
            {{ $isEdit ? 'Ubah Penilaian & Ulasan Produk' : 'Beri Penilaian & Ulasan Produk' }}
        </h1>
        <p class="text-xs text-on-surface-variant">
            Bagikan pengalaman Anda tentang bahan, ukuran, dan kenyamanan pakaian ini untuk membantu para bunda lainnya.
        </p>
    </div>

    <!-- Ringkasan Produk yang Diulas -->
    <div class="bg-surface-container-lowest rounded-3xl p-5 sm:p-6 border border-surface-container shadow-xs flex items-center gap-4">
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-surface-container overflow-hidden shrink-0 border border-surface-container flex items-center justify-center">
            @if($product->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->photo))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->photo) }}" 
                     alt="{{ $product->name }}" 
                     class="w-full h-full object-cover" />
            @else
                <span class="material-symbols-outlined text-outline text-2xl">checkroom</span>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-container text-on-surface-variant mb-1">
                {{ $product->category }}
            </span>
            <h2 class="text-sm sm:text-base font-bold text-on-surface truncate">{{ $product->name }}</h2>
            @if($orderItem)
                <p class="text-xs text-outline mt-0.5">
                    Varian yang pernah Anda beli: <span class="font-medium text-on-surface-variant">{{ $orderItem->varian_label }}</span>
                </p>
            @endif
        </div>
    </div>

    <!-- Form Ulasan & Rating -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs">
        <form action="{{ $isEdit ? route('ulasan.update', $review) : route('ulasan.store', $product) }}" 
              method="POST" 
              x-data="{ 
                  rating: {{ old('rating', $review?->rating ?? 5) }}, 
                  hoverRating: 0,
                  ulasanText: '{{ addslashes(old('ulasan', $review?->ulasan ?? '')) }}'
              }" 
              class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Pemilih Rating Bintang Interaktif -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-on-surface">
                    Penilaian Bintang (Rating) *
                </label>
                <p class="text-[11px] text-outline">
                    Pilih bintang 1 hingga 5 sesuai kepuasan Anda terhadap produk ini.
                </p>

                <div class="flex items-center gap-2 pt-1" @mouseleave="hoverRating = 0">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer relative p-1 focus-within:ring-2 focus-within:ring-primary rounded-lg">
                            <input type="radio" 
                                   name="rating" 
                                   value="{{ $i }}" 
                                   x-model.number="rating" 
                                   class="sr-only" 
                                   required />
                            <span class="material-symbols-outlined text-3xl sm:text-4xl transition-all duration-150"
                                  @mouseenter="hoverRating = {{ $i }}"
                                  :class="(hoverRating || rating) >= {{ $i }} ? 'text-amber-400 scale-110' : 'text-outline-variant hover:text-outline'"
                                  :style="(hoverRating || rating) >= {{ $i }} ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                                star
                            </span>
                        </label>
                    @endfor

                    <span class="text-xs font-bold ml-2 text-on-surface" x-text="
                        (hoverRating || rating) === 5 ? 'Sangat Puas (5/5)' :
                        (hoverRating || rating) === 4 ? 'Puas / Bagus (4/5)' :
                        (hoverRating || rating) === 3 ? 'Cukup (3/5)' :
                        (hoverRating || rating) === 2 ? 'Kurang Puas (2/5)' : 'Sangat Kecewa (1/5)'
                    "></span>
                </div>

                @error('rating')
                    <p class="text-error text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Catatan Ulasan -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="ulasan-input" class="block text-xs font-bold text-on-surface">
                        Ulasan Tertulis (Opsional)
                    </label>
                    <span class="text-[11px] text-outline">
                        <span x-text="ulasanText.length"></span>/1000 Karakter
                    </span>
                </div>

                <textarea name="ulasan" 
                          id="ulasan-input" 
                          rows="5" 
                          maxlength="1000"
                          x-model="ulasanText"
                          placeholder="Ceritakan bagaimana bahan kainnya, apakah ukurannya pas dengan si kecil, dan kepuasan Anda secara keseluruhan..." 
                          class="w-full p-4 rounded-2xl bg-surface-container-low text-on-surface text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary border border-surface-container leading-relaxed placeholder:text-outline">{{ old('ulasan', $review?->ulasan) }}</textarea>

                @error('ulasan')
                    <p class="text-error text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-surface-container flex flex-col-reverse sm:flex-row items-center justify-end gap-3 text-xs">
                <a href="{{ route('produk.show', $product) }}" 
                   class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-surface-container text-on-surface font-semibold hover:bg-surface-container-high transition-colors text-center">
                    Batal
                </a>

                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-primary text-white font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>{{ $isEdit ? 'Simpan Perubahan Ulasan' : 'Kirim Ulasan' }}</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

