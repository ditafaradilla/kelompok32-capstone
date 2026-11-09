@php
    $totalStokToko = $product->variants->sum(fn($v) => $v->stokTersedia());
    $isStokHabis = $totalStokToko <= 0;
    $availableSizes = $product->variants->pluck('ukuran')->filter()->unique();
@endphp

<a href="{{ route('produk.show', $product->id) }}" class="group bg-surface-container-lowest rounded-2xl border border-surface-container overflow-hidden hover:border-primary/40 hover:shadow-md transition-all flex flex-col justify-between">
    <div>
        <!-- Foto Produk dengan Fallback -->
        <div class="relative w-full aspect-square bg-surface-container-low overflow-hidden flex items-center justify-center">
            @if(!empty($product->photo))
                <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-outline/50 bg-surface-container-low p-4">
                    <span class="material-symbols-outlined text-4xl mb-1 text-primary/40">styler</span>
                    <span class="text-[10px] text-outline font-medium">Baby Corner</span>
                </div>
            @endif

            <!-- Badge Stok Habis -->
            @if($isStokHabis)
                <div class="absolute inset-0 bg-white/70 backdrop-blur-[2px] flex items-center justify-center p-2">
                    <span class="px-2.5 py-1 rounded-full bg-error text-white text-[10px] font-bold shadow-xs">
                        Stok Habis
                    </span>
                </div>
            @endif

            <!-- Kategori Badge -->
            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md bg-surface/90 backdrop-blur-md text-[10px] font-semibold text-on-surface shadow-xs">
                {{ $product->category }}
            </span>
        </div>

        <!-- Info Produk -->
        <div class="p-3.5 sm:p-4 space-y-2">
            <h3 class="text-xs sm:text-sm font-bold text-on-surface line-clamp-2 group-hover:text-primary transition-colors">
                {{ $product->name }}
            </h3>

            <!-- Ukuran Tersedia (Chips) -->
            <div class="flex flex-wrap gap-1 items-center">
                @forelse($availableSizes as $size)
                    <span class="px-1.5 py-0.5 rounded bg-surface-container text-[10px] font-medium text-on-surface-variant">
                        {{ $size }}
                    </span>
                @empty
                    <span class="text-[10px] text-outline italic">-</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Harga & Tombol Detail -->
    <div class="px-3.5 sm:px-4 pb-3.5 sm:pb-4 pt-1 flex items-center justify-between border-t border-surface-container/60 mt-2">
        <div>
            <div class="text-[10px] text-outline">Mulai dari</div>
            <div class="text-xs sm:text-sm font-bold text-primary">{{ rupiah($product->price) }}</div>
        </div>
        <span class="w-7 h-7 rounded-full bg-surface-container-low group-hover:bg-primary group-hover:text-white text-on-surface-variant flex items-center justify-center transition-all">
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </span>
    </div>
</a>

