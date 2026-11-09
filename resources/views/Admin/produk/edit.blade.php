@extends('Admin.layout')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk: ' . $product->name)
@section('page-subtitle', 'Ubah data pakaian bayi, varian ukuran/warna, harga, dan perbarui alokasi stok per varian')

@section('content')
<div class="max-w-5xl mx-auto">
    
    <div class="mb-4">
        <a href="{{ route('admin.produk.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Daftar Produk</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-error-container border border-error text-on-error-container text-xs shadow-xs space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">error</span>
                <span>Terjadi kesalahan pengisian formulir:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.produk.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest p-6 sm:p-8 rounded-2xl border border-surface-container shadow-xs space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: INFORMASI UTAMA PRODUK -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-primary text-xl">styler</span>
                <h3 class="text-sm font-bold text-on-surface">1. Informasi Utama Baju Bayi</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Produk -->
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-xs font-semibold text-on-surface">Nama Produk <span class="text-error">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>

                <!-- Kategori -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Kategori Pakaian <span class="text-error">*</span></label>
                    <input type="text" name="category" value="{{ old('category', $product->category) }}" list="category-suggestions" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                    <datalist id="category-suggestions">
                        <option value="Jumper">
                        <option value="Romper">
                        <option value="Piyama">
                        <option value="Setelan Katun">
                        <option value="Baju Kutung">
                        <option value="Aksesoris & Topi">
                    </datalist>
                </div>

                <!-- SKU / Barcode Master -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Kode SKU Master <span class="text-error">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface font-mono" />
                </div>

                <!-- Harga Jual -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Harga Jual (Rp) <span class="text-error">*</span></label>
                    <input type="number" name="price" value="{{ old('price', (int) $product->price) }}" required min="0" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface font-semibold" />
                </div>

                <!-- Harga Modal (Pemasok) -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Harga Modal (Rp)</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', (int) $product->cost_price) }}" min="0" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>
            </div>

            <!-- Batas Minimum Stok -->
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">notifications_active</span>
                        <span>Batas Minimum Peringatan Stok (Fitur 11)</span>
                    </div>
                    <p class="text-[11px] text-amber-700">Peringatan otomatis muncul di dashboard jika stok total seluruh varian &le; batas minimum ini.</p>
                </div>
                <div class="w-full sm:w-36 shrink-0">
                    <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" required min="0" class="w-full h-10 px-3 text-xs bg-white rounded-lg border border-amber-300 font-bold text-amber-900 focus:ring-2 focus:ring-amber-500/40 outline-none text-center" />
                </div>
            </div>
        </div>

        <!-- SECTION 2: VARIAN UKURAN, WARNA & ALOKASI STOK PER LOKASI -->
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-surface-container">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-xl">palette</span>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface">2. Daftar Varian & Stok per Lokasi</h3>
                        <p class="text-[11px] text-on-surface-variant">Ubah ukuran, warna, dan alokasi stok untuk masing-masing varian pakaian bayi</p>
                    </div>
                </div>
                <button type="button" onclick="addVariantRow()" class="px-3 py-1.5 rounded-lg bg-secondary-container text-on-secondary-container text-xs font-semibold hover:opacity-90 flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-xs">add</span>
                    <span>Tambah Baris Varian</span>
                </button>
            </div>

            <datalist id="size-suggestions">
                @foreach($sizeGuides as $sg)
                    <option value="{{ $sg->ukuran }}">{{ $sg->ukuran }} ({{ $sg->usia_min_bulan }}-{{ $sg->usia_maks_bulan }} bln)</option>
                @endforeach
            </datalist>

            <div class="space-y-3" id="variants-container">
                @forelse($product->variants as $idx => $variant)
                    @php
                        $varStocks = $variant->stocks->pluck('quantity', 'location_id')->toArray();
                    @endphp
                    <div class="variant-card p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-3">
                        <input type="hidden" name="variants[{{ $idx }}][id]" value="{{ $variant->id }}" />

                        <div class="flex items-center justify-between pb-2 border-b border-surface-container/60">
                            <span class="text-xs font-bold text-primary flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">sell</span>
                                <span class="variant-number-label">Varian #{{ $idx + 1 }}</span>
                            </span>
                            <button type="button" onclick="removeVariantRow(this)" class="text-outline hover:text-error text-xs flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">delete</span>
                                <span>Hapus</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-on-surface">Ukuran (Size Guide) <span class="text-error">*</span></label>
                                <input type="text" name="variants[{{ $idx }}][ukuran]" value="{{ old('variants.'.$idx.'.ukuran', $variant->ukuran) }}" list="size-suggestions" required class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-on-surface">Warna</label>
                                <input type="text" name="variants[{{ $idx }}][warna]" value="{{ old('variants.'.$idx.'.warna', $variant->warna) }}" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-on-surface">SKU Varian (Opsional)</label>
                                <input type="text" name="variants[{{ $idx }}][sku]" value="{{ old('variants.'.$idx.'.sku', $variant->sku) }}" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container font-mono text-[11px] focus:ring-2 focus:ring-primary/40 outline-none" />
                            </div>
                        </div>

                        <!-- Stok per Lokasi untuk varian ini -->
                        <div class="pt-2">
                            <label class="text-[11px] font-semibold text-on-surface block mb-1.5">Alokasi Stok di Lokasi Toko & Gudang (pcs):</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($locations as $loc)
                                    <div class="p-2.5 rounded-lg bg-white border border-surface-container flex items-center justify-between gap-2">
                                        <div class="space-y-0.5 min-w-0">
                                            <div class="text-[11px] font-bold text-on-surface truncate">{{ $loc->name }}</div>
                                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.2 rounded {{ $loc->type === 'toko' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">{{ $loc->type }}</span>
                                        </div>
                                        <input type="number" name="variants[{{ $idx }}][stocks][{{ $loc->id }}]" value="{{ old('variants.'.$idx.'.stocks.'.$loc->id, $varStocks[$loc->id] ?? 0) }}" min="0" class="w-16 h-8 px-2 text-xs bg-surface-container-low rounded border border-surface-container text-center font-bold text-on-surface outline-none" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Fallback satu baris varian jika produk belum memiliki varian -->
                    <div class="variant-card p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-surface-container/60">
                            <span class="text-xs font-bold text-primary flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">sell</span>
                                <span class="variant-number-label">Varian #1</span>
                            </span>
                            <button type="button" onclick="removeVariantRow(this)" class="text-outline hover:text-error text-xs flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">delete</span>
                                <span>Hapus</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-on-surface">Ukuran (Size Guide) <span class="text-error">*</span></label>
                                <input type="text" name="variants[0][ukuran]" list="size-suggestions" required placeholder="Pilih: 0-3M, 3-6M, dst." class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-on-surface">Warna</label>
                                <input type="text" name="variants[0][warna]" placeholder="Contoh: Sage Green, Putih" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-on-surface">SKU Varian (Opsional)</label>
                                <input type="text" name="variants[0][sku]" placeholder="Auto generate" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container font-mono text-[11px] focus:ring-2 focus:ring-primary/40 outline-none" />
                            </div>
                        </div>
                        <div class="pt-2">
                            <label class="text-[11px] font-semibold text-on-surface block mb-1.5">Alokasi Stok di Lokasi Toko & Gudang (pcs):</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($locations as $loc)
                                    <div class="p-2.5 rounded-lg bg-white border border-surface-container flex items-center justify-between gap-2">
                                        <div class="space-y-0.5 min-w-0">
                                            <div class="text-[11px] font-bold text-on-surface truncate">{{ $loc->name }}</div>
                                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.2 rounded {{ $loc->type === 'toko' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">{{ $loc->type }}</span>
                                        </div>
                                        <input type="number" name="variants[0][stocks][{{ $loc->id }}]" value="0" min="0" class="w-16 h-8 px-2 text-xs bg-surface-container-low rounded border border-surface-container text-center font-bold text-on-surface outline-none" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- SECTION 3: FOTO & DESKRIPSI -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-outline text-xl">image</span>
                <h3 class="text-sm font-bold text-on-surface">3. Foto Produk & Deskripsi</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-on-surface">Foto Produk Saat Ini</label>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 rounded-xl bg-surface-container flex items-center justify-center overflow-hidden border border-surface-container">
                            @if($product->photo)
                                <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-full h-full object-cover" />
                            @else
                                <span class="material-symbols-outlined text-outline text-2xl">styler</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <input type="file" name="photo" accept="image/*" class="text-xs text-outline file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-surface-container file:text-on-surface hover:file:bg-surface-container-high cursor-pointer" />
                            <p class="text-[10px] text-outline">Biarkan kosong jika tidak ingin mengubah foto</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 sm:col-span-2">
                    <label class="text-xs font-semibold text-on-surface">Deskripsi Produk Baju Bayi</label>
                    <textarea name="description" rows="3" class="w-full p-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SUBMIT BUTTONS -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container">
            <a href="{{ route('admin.produk.index') }}" class="px-5 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-high transition-all">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all shadow-xs flex items-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined text-sm">save</span>
                <span>Simpan Perubahan Produk & Varian</span>
            </button>
        </div>
    </form>
</div>

<!-- SCRIPT REPEATER VARIAN -->
<script>
    let variantIndex = {{ max(count($product->variants), 1) }};

    function addVariantRow() {
        const container = document.getElementById('variants-container');
        const template = `
        <div class="variant-card p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-surface-container/60">
                <span class="text-xs font-bold text-primary flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">sell</span>
                    <span class="variant-number-label">Varian Baru</span>
                </span>
                <button type="button" onclick="removeVariantRow(this)" class="text-outline hover:text-error text-xs flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">delete</span>
                    <span>Hapus</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-on-surface">Ukuran (Size Guide) <span class="text-error">*</span></label>
                    <input type="text" name="variants[${variantIndex}][ukuran]" list="size-suggestions" required placeholder="Pilih: 0-3M, 3-6M, dst." class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none" />
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-on-surface">Warna</label>
                    <input type="text" name="variants[${variantIndex}][warna]" placeholder="Contoh: Sage Green, Putih" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none" />
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-on-surface">SKU Varian (Opsional)</label>
                    <input type="text" name="variants[${variantIndex}][sku]" placeholder="Auto generate" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container font-mono text-[11px] focus:ring-2 focus:ring-primary/40 outline-none" />
                </div>
            </div>

            <div class="pt-2">
                <label class="text-[11px] font-semibold text-on-surface block mb-1.5">Alokasi Stok di Lokasi Toko & Gudang (pcs):</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($locations as $loc)
                        <div class="p-2.5 rounded-lg bg-white border border-surface-container flex items-center justify-between gap-2">
                            <div class="space-y-0.5 min-w-0">
                                <div class="text-[11px] font-bold text-on-surface truncate">{{ $loc->name }}</div>
                                <span class="text-[9px] uppercase font-bold px-1.5 py-0.2 rounded {{ $loc->type === 'toko' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">{{ $loc->type }}</span>
                            </div>
                            <input type="number" name="variants[${variantIndex}][stocks][{{ $loc->id }}]" value="0" min="0" class="w-16 h-8 px-2 text-xs bg-surface-container-low rounded border border-surface-container text-center font-bold text-on-surface outline-none" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        variantIndex++;
    }

    function removeVariantRow(button) {
        const cards = document.querySelectorAll('.variant-card');
        if (cards.length <= 1) {
            alert('Produk harus memiliki minimal satu varian.');
            return;
        }
        button.closest('.variant-card').remove();
    }
</script>
@endsection
