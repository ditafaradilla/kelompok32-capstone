@extends('Admin.layout')

@section('title', 'Tambah Produk Baru')
@section('page-title', 'Tambah Produk Baru & Alokasi Stok')
@section('page-subtitle', 'Sesuai Diagram Alir: Isi data pakaian bayi, varian ukuran/warna, harga, dan alokasikan stok awal ke toko/gudang')

@section('content')
<div class="max-w-4xl mx-auto">
    
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

    <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest p-6 sm:p-8 rounded-2xl border border-surface-container shadow-xs space-y-8">
        @csrf

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
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Jumper Bayi Katun Organik Polos" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>

                <!-- Kategori -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Kategori Pakaian <span class="text-error">*</span></label>
                    <input type="text" name="category" value="{{ old('category', 'Jumper') }}" list="category-suggestions" required placeholder="Pilih atau ketik kategori..." class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                    <datalist id="category-suggestions">
                        <option value="Jumper">
                        <option value="Romper">
                        <option value="Piyama">
                        <option value="Setelan Katun">
                        <option value="Baju Kutung">
                        <option value="Aksesoris & Topi">
                    </datalist>
                </div>

                <!-- SKU / Barcode -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Kode SKU / Barcode (Opsional)</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Otomatis di-generate jika kosong (e.g. BC-JMP-101)" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface font-mono" />
                </div>
            </div>
        </div>

        <!-- SECTION 2: VARIAN UKURAN, WARNA & HARGA (Sesuai Diagram 1) -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-secondary text-xl">palette</span>
                <h3 class="text-sm font-bold text-on-surface">2. Varian Ukuran / Warna & Harga</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Varian Ukuran -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Varian Ukuran Bayi</label>
                    <input type="text" name="size" value="{{ old('size') }}" placeholder="Contoh: 0-3 Bulan, 6-12 Bulan" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>

                <!-- Varian Warna -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Varian Warna</label>
                    <input type="text" name="color" value="{{ old('color') }}" placeholder="Contoh: Sage Green, Pastel Pink" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>

                <!-- Harga Jual -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Harga Jual (Rp) <span class="text-error">*</span></label>
                    <input type="number" name="price" value="{{ old('price', 65000) }}" required min="0" placeholder="65000" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface font-semibold" />
                </div>

                <!-- Harga Modal (Pemasok) -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Harga Beli Modal (Rp)</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', 40000) }}" min="0" placeholder="40000" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>
            </div>

            <!-- Batas Minimum Stok (Fitur 11 Peringatan Stok Habis) -->
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">notifications_active</span>
                        <span>Batas Minimum Peringatan Stok (Fitur 11)</span>
                    </div>
                    <p class="text-[11px] text-amber-700">Sistem akan memberi alert peringatan jika total stok mencapai atau berada di bawah jumlah ini.</p>
                </div>
                <div class="w-full sm:w-36 shrink-0">
                    <input type="number" name="min_stock" value="{{ old('min_stock', 5) }}" required min="0" class="w-full h-10 px-3 text-xs bg-white rounded-lg border border-amber-300 font-bold text-amber-900 focus:ring-2 focus:ring-amber-500/40 outline-none text-center" />
                </div>
            </div>
        </div>

        <!-- SECTION 3: ALOKASI STOK AWAL TOKO & GUDANG (Pemisahan Stok - Fitur 8 & 9) -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-purple-600 text-xl">warehouse</span>
                <div class="space-y-0.5">
                    <h3 class="text-sm font-bold text-on-surface">3. Alokasi Stok Awal per Lokasi (Pemisahan Stok Toko & Gudang)</h3>
                    <p class="text-[11px] text-on-surface-variant">Sesuai Diagram 1: Atur jumlah stok untuk produk ini pada masing-masing titik toko fisik dan gudang</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($locations as $loc)
                    <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-on-surface flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm text-outline">{{ $loc->type === 'toko' ? 'storefront' : 'warehouse' }}</span>
                                {{ $loc->name }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $loc->type === 'toko' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $loc->type }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] text-outline">Jumlah Stok (pcs):</label>
                            <input type="number" name="stocks[{{ $loc->id }}]" value="{{ old('stocks.' . $loc->id, 0) }}" min="0" class="w-full h-9 px-3 text-xs bg-white rounded-lg border border-surface-container font-semibold text-on-surface text-center focus:ring-2 focus:ring-primary/40 outline-none" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SECTION 4: FOTO & DESKRIPSI -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-outline text-xl">image</span>
                <h3 class="text-sm font-bold text-on-surface">4. Foto Produk & Deskripsi</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Foto Produk (JPG, PNG, WebP maks. 2MB)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-outline file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-surface-container file:text-on-surface hover:file:bg-surface-container-high cursor-pointer" />
                </div>

                <div class="space-y-1 sm:col-span-2">
                    <label class="text-xs font-semibold text-on-surface">Deskripsi Produk Baju Bayi</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan jenis kain, kelembutan, dan sertifikasi katun organik..." class="w-full p-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface">{{ old('description') }}</textarea>
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
                <span>Simpan Produk & Stok</span>
            </button>
        </div>
    </form>
</div>
@endsection
