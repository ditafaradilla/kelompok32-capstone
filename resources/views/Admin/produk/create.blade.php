@extends('Admin.layout')

@section('title', 'Tambah Produk Baru')
@section('page-title', 'Tambah Produk Baru & Varian Stok')
@section('page-subtitle', 'Isi data pakaian bayi, varian ukuran/warna, harga, dan alokasikan stok awal ke toko/gudang')

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

                <!-- SKU / Barcode Master -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Kode SKU Master (Opsional)</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Otomatis di-generate jika kosong (e.g. BC-JMP-101)" class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface font-mono" />
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

            <!-- Batas Minimum Stok Peringatan -->
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">notifications_active</span>
                        <span>Batas Minimum Peringatan Stok (Fitur 11)</span>
                    </div>
                    <p class="text-[11px] text-amber-700">Sistem akan memberi alert peringatan jika total seluruh varian stok &le; jumlah ini.</p>
                </div>
                <div class="w-full sm:w-36 shrink-0">
                    <input type="number" name="min_stock" value="{{ old('min_stock', 5) }}" required min="0" class="w-full h-10 px-3 text-xs bg-white rounded-lg border border-amber-300 font-bold text-amber-900 focus:ring-2 focus:ring-amber-500/40 outline-none text-center" />
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
                        <p class="text-[11px] text-on-surface-variant">Tambahkan ukuran, warna, dan alokasikan stok awal untuk masing-masing varian ke toko fisik / gudang</p>
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
                <!-- Baris Varian Default Pertama -->
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
                                    <input type="number" name="variants[0][stocks][{{ $loc->id }}]" value="0" min="0" class="w-16 h-8 px-2 text-xs bg-surface-container-low rounded border border-surface-container text-center font-bold text-on-surface outline-none" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: FOTO & DESKRIPSI -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-outline text-xl">image</span>
                <h3 class="text-sm font-bold text-on-surface">3. Foto Produk & Deskripsi</h3>
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
                <span>Simpan Produk & Varian</span>
            </button>
        </div>
    </form>
</div>

<!-- SCRIPT REPEATER VARIAN -->
<script>
    let variantIndex = 1;

    function addVariantRow() {
        const container = document.getElementById('variants-container');
        const template = `
        <div class="variant-card p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-surface-container/60">
                <span class="text-xs font-bold text-primary flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">sell</span>
                    <span class="variant-number-label">Varian #${variantIndex + 1}</span>
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
