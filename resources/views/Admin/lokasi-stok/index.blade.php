@extends('Admin.layout')

@section('title', 'Lokasi & Stok Toko')
@section('page-title', 'Pencatatan Lokasi dan Stok Toko')
@section('page-subtitle', 'Sesuai Diagram Alir: Kelola master titik toko & gudang, serta input/sesuaikan jumlah stok produk pada lokasi tertentu')

@section('content')
<div class="flex flex-col w-full gap-8">

    <!-- BAGIAN 1: DAFTAR LOKASI TOKO & GUDANG + FORM TAMBAH LOKASI (Diagram 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Tabel Daftar Lokasi Toko & Gudang -->
        <div class="lg:col-span-8 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-surface-container">
                <div class="space-y-0.5">
                    <h3 class="text-sm font-bold text-on-surface">Daftar Titik Lokasi Toko & Gudang</h3>
                    <p class="text-xs text-on-surface-variant">Pemisahan titik penyimpanan barang (toko etalase vs gudang sentral)</p>
                </div>
                <span class="text-xs text-outline">{{ count($locations) }} Titik Terdaftar</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($locations as $loc)
                    <div class="p-4 rounded-xl border {{ ($selectedLocation && $selectedLocation->id == $loc->id) ? 'border-primary bg-primary/5 shadow-xs' : 'border-surface-container bg-surface-container-low/40' }} flex flex-col justify-between space-y-3 transition-all">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $loc->type === 'toko' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ $loc->type === 'toko' ? 'Toko Fisik' : 'Gudang' }}
                                </span>
                                <span class="text-[11px] font-mono font-semibold text-outline">{{ $loc->code }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-on-surface">{{ $loc->name }}</h4>
                            <p class="text-[11px] text-on-surface-variant line-clamp-2">{{ $loc->address ?: 'Alamat belum disetel' }}</p>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-surface-container/60 text-xs">
                            <span class="text-[11px] text-outline">
                                Total Stok: <strong>{{ $loc->stocks->sum('quantity') }}</strong> pcs
                            </span>
                            <!-- Tombol Kelola Stok pada Lokasi Tertentu (Sesuai Diagram 2) -->
                            <a href="{{ route('admin.lokasi.index', ['location_id' => $loc->id]) }}" class="px-3 py-1 rounded-lg {{ ($selectedLocation && $selectedLocation->id == $loc->id) ? 'bg-primary text-white font-semibold' : 'bg-surface-container text-primary hover:bg-surface-container-high' }} text-[11px] transition-all flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">tune</span>
                                <span>Kelola Stok Lokasi Ini</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Tambah Lokasi Baru (Sesuai Diagram 2) -->
        <div class="lg:col-span-4 bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 space-y-4">
            <div class="space-y-0.5 pb-3 border-b border-surface-container">
                <h3 class="text-sm font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-base">add_location_alt</span>
                    <span>Tambah Lokasi Baru</span>
                </h3>
                <p class="text-[11px] text-on-surface-variant">Tambahkan cabang toko fisik atau gudang cadangan baru</p>
            </div>

            <form action="{{ route('admin.lokasi.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Nama Lokasi <span class="text-error">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Toko Cabang Bintaro" class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-on-surface">Kode Lokasi</label>
                        <input type="text" name="code" placeholder="Auto (TKO-02)" class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface font-mono" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-on-surface">Tipe Lokasi <span class="text-error">*</span></label>
                        <select name="type" required class="w-full h-10 px-2 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface">
                            <option value="toko">Toko Fisik / Etalase</option>
                            <option value="gudang">Gudang Penyimpanan</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Alamat / Lokasi Rak</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap toko atau detail nomor rak gudang..." class="w-full p-2.5 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface"></textarea>
                </div>

                <button type="submit" class="w-full h-10 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Simpan Lokasi Baru</span>
                </button>
            </form>
        </div>

    </div>

    <!-- BAGIAN 2: KELOLA STOK PADA LOKASI TERTENTU (Sesuai Diagram 2) -->
    @if($selectedLocation)
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-surface-container">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $selectedLocation->type === 'toko' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                        {{ $selectedLocation->type }}
                    </span>
                    <h3 class="text-sm font-bold text-on-surface">Kelola Stok di: {{ $selectedLocation->name }} ({{ $selectedLocation->code }})</h3>
                </div>
                <p class="text-xs text-on-surface-variant">Sesuai Diagram Alir 2: Masukkan atau sesuaikan jumlah stok untuk setiap produk baju bayi di lokasi ini</p>
            </div>
        </div>

        <form action="{{ route('admin.lokasi.updateStock') }}" method="POST">
            @csrf
            <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}" />

            @php
                $stocksInSelectedLocation = $selectedLocation->stocks->pluck('quantity', 'product_variant_id')->toArray();
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-surface-container-low/70 text-outline font-semibold border-b border-surface-container">
                            <th class="py-3 px-4">Nama Produk & Varian</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Batas Min. Produk</th>
                            <th class="py-3 px-4 text-center">Jumlah Stok di Lokasi Ini (pcs)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-low">
                        @forelse($allVariants as $variant)
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-on-surface">{{ $variant->nama_lengkap }}</div>
                                    <div class="text-[11px] text-outline font-mono">SKU Varian: {{ $variant->sku ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface text-[10px] font-semibold">{{ $variant->product->category ?? 'Pakaian' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-outline text-[11px]">{{ $variant->product->min_stock ?? 5 }} pcs</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <input type="number" name="stocks[{{ $variant->id }}]" value="{{ $stocksInSelectedLocation[$variant->id] ?? 0 }}" min="0" class="w-24 h-9 px-3 text-xs bg-surface-container-low rounded-lg border border-surface-container text-center font-bold text-on-surface focus:ring-2 focus:ring-primary/40 outline-none" />
                                        <span class="text-[11px] text-outline">pcs</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-outline italic">Belum ada varian produk di sistem</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-surface-container">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all shadow-xs flex items-center gap-2 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">check</span>
                    <span>Simpan Perubahan Stok di Lokasi Ini</span>
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection
