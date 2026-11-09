@extends('Admin.layout')

@section('title', 'Form Penerimaan Barang ' . $order->po_number)
@section('page-title', 'Penerimaan Barang Datang: ' . $order->po_number)
@section('page-subtitle', 'Sesuai Diagram Alir 4: Detail pesanan dan form penerimaan barang di bawahnya, penambahan stok otomatis, dan deteksi peringatan stok minimum')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <a href="{{ route('admin.penerimaan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Daftar Penerimaan Barang</span>
        </a>
    </div>

    <!-- BAGIAN 1: DETAIL CATATAN PESANAN (Sesuai Diagram 4) -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <div class="space-y-0.5">
                <span class="text-[11px] text-outline font-semibold uppercase">Detail Catatan Pesanan ke Pemasok</span>
                <h3 class="text-lg font-bold text-primary font-mono">{{ $order->po_number }}</h3>
            </div>
            <div>
                @if($order->status === 'received')
                    <span class="px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Barang Sudah Diterima</span>
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">hourglass_top</span>
                        <span>Menunggu Konfirmasi Penerimaan</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container space-y-1">
                <div class="text-[10px] uppercase font-bold text-outline">Pemasok (Supplier):</div>
                <div class="font-bold text-on-surface">{{ $order->supplier->name }}</div>
                <div class="text-on-surface-variant">{{ $order->supplier->contact_person }} ({{ $order->supplier->phone }})</div>
            </div>

            <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container space-y-1">
                <div class="text-[10px] uppercase font-bold text-outline">Tanggal Pemesanan:</div>
                <div class="font-bold text-on-surface">{{ date('d F Y', strtotime($order->order_date)) }}</div>
                <div class="text-on-surface-variant">Total Biaya: Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
            </div>

            <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container space-y-1">
                <div class="text-[10px] uppercase font-bold text-outline">Tujuan Alokasi Stok Masuk:</div>
                <div class="font-bold text-on-surface">{{ $order->targetLocation->name ?? 'Gudang Pusat' }}</div>
                <div class="text-on-surface-variant">Tipe: {{ ucfirst($order->targetLocation->type ?? 'Gudang') }}</div>
            </div>
        </div>
    </div>

    <!-- BAGIAN 2: FORM PENERIMAAN BARANG DI BAWAHNYA (Sesuai Diagram 4) -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 sm:p-8 space-y-6">
        <div class="space-y-0.5 pb-4 border-b border-surface-container">
            <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">inventory</span>
                <span>Form Penerimaan Barang Masuk</span>
            </h3>
            <p class="text-xs text-on-surface-variant">Sesuai Diagram 4: Periksa barang yang datang dari pemasok, isi jumlah barang yang diterima secara fisik, lalu tekan "Simpan"</p>
        </div>

        <form action="{{ route('admin.penerimaan.store', $order->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Tanggal Penerimaan Fisik -->
            <div class="w-full sm:w-72 space-y-1">
                <label class="text-xs font-semibold text-on-surface">Tanggal Barang Diterima <span class="text-error">*</span></label>
                <input type="date" name="received_date" value="{{ old('received_date', $order->received_date ?? date('Y-m-d')) }}" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
            </div>

            <!-- Tabel Pemeriksaan Barang yang Datang -->
            <div class="overflow-x-auto border border-surface-container rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-surface-container-low text-outline font-semibold border-b border-surface-container">
                            <th class="py-3 px-4">Nama Produk Baju Bayi</th>
                            <th class="py-3 px-4 text-center">Jumlah Dipesan</th>
                            <th class="py-3 px-4 text-center">Sudah Diterima Lalu</th>
                            <th class="py-3 px-4 text-center w-48">Jumlah Diterima Hari Ini (pcs) <span class="text-error">*</span></th>
                            <th class="py-3 px-4 text-center">Stok Total Produk Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-low">
                        @foreach($order->items as $item)
                        @php
                            $remainingQty = max(0, $item->quantity_ordered - $item->quantity_received);
                        @endphp
                        <tr class="hover:bg-surface-container-low/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-on-surface">{{ $item->variant ? $item->variant->nama_lengkap : ($item->product->name ?? 'Produk') }}</div>
                                <div class="text-[11px] text-outline">SKU: {{ $item->variant->sku ?? $item->product->sku ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-on-surface">{{ $item->quantity_ordered }} pcs</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-on-surface-variant">{{ $item->quantity_received }} pcs</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <input type="number" name="items[{{ $item->id }}][received_qty]" value="{{ $order->status === 'received' ? 0 : $remainingQty }}" min="0" required class="w-24 h-10 px-3 text-xs bg-surface-container-low rounded-lg border border-surface-container text-center font-bold text-primary focus:ring-2 focus:ring-primary/40 outline-none" />
                                    <span class="text-outline">pcs</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="font-bold {{ $item->product->is_out_of_stock ? 'text-red-600' : ($item->product->is_low_stock ? 'text-amber-600' : 'text-on-surface') }}">
                                    {{ $item->product->total_stock }} pcs
                                </div>
                                <div class="text-[10px] text-outline">Batas Min: {{ $item->product->min_stock }} pcs</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 rounded-xl bg-secondary-container/30 border border-secondary/40 text-xs text-on-secondary-container flex items-start gap-2.5">
                <span class="material-symbols-outlined text-secondary text-lg shrink-0">info</span>
                <div>
                    <span class="font-bold">Otomatisasi Sistem (Diagram 4):</span>
                    Saat Anda menekan <strong>Simpan Penerimaan Barang</strong>, stok produk akan <strong>otomatis bertambah</strong> pada lokasi <strong>{{ $order->targetLocation->name ?? 'Gudang Pusat' }}</strong> dan sistem akan mengecek apakah stok telah melewati batas minimum.
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container">
                <a href="{{ route('admin.penerimaan.index') }}" class="px-5 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-high transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all shadow-xs flex items-center gap-2 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">inventory_2</span>
                    <span>Simpan Penerimaan Barang & Tambah Stok</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
