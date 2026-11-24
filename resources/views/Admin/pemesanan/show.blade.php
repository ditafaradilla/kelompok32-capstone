@extends('Admin.layout')

@section('title', 'Detail Catatan Pemesanan ' . $order->po_number)
@section('page-title', 'Detail Catatan Pemesanan Barang (PO)')
@section('page-subtitle', 'Sesuai Diagram Alir 3: Detail catatan pesanan ke pemasok ' . $order->po_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.pemesanan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Daftar Pemesanan</span>
        </a>

        @if($order->status === 'pending')
            <a href="{{ route('admin.penerimaan.show', $order->id) }}" class="px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-sm">inventory</span>
                <span>Proses Penerimaan Barang Datang (Fitur 11)</span>
            </a>
        @endif
    </div>

    <!-- KARTU DETAIL PEMESANAN -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-6 sm:p-8 space-y-6">
        
        <!-- Header PO -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-surface-container">
            <div>
                <span class="text-xs text-outline font-semibold uppercase">Nomor Purchase Order:</span>
                <h2 class="text-2xl font-extrabold text-primary font-mono tracking-tight">{{ $order->po_number }}</h2>
                <div class="text-xs text-on-surface-variant mt-1">Dipesan pada tanggal: <strong>{{ date('d F Y', strtotime($order->order_date)) }}</strong></div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-xs text-outline font-semibold uppercase">Status Pemesanan:</span>
                <div class="mt-1">
                    @if($order->status === 'received')
                        <span class="px-3.5 py-1.5 rounded-full bg-secondary-container text-on-secondary-container font-bold text-xs inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            <span>Barang Sudah Diterima</span>
                        </span>
                        @if($order->received_date)
                            <div class="text-[11px] text-outline mt-1">Diterima pada: {{ date('d M Y', strtotime($order->received_date)) }}</div>
                        @endif
                    @else
                        <span class="px-3.5 py-1.5 rounded-full bg-amber-100 text-amber-800 font-bold text-xs inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span>Menunggu Barang Tiba (Pending)</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Pemasok & Lokasi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-1.5">
                <div class="font-bold text-on-surface uppercase tracking-wider text-[11px] text-outline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-primary">store</span>
                    <span>Informasi Pemasok (Supplier)</span>
                </div>
                <div class="font-bold text-sm text-on-surface">{{ $order->supplier->name }}</div>
                <div class="text-on-surface-variant">PIC: {{ $order->supplier->contact_person }}</div>
                <div class="text-on-surface-variant">Telepon/WA: {{ $order->supplier->phone }}</div>
                <div class="text-on-surface-variant">Alamat: {{ $order->supplier->address ?? '-' }}</div>
            </div>

            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container space-y-1.5">
                <div class="font-bold text-on-surface uppercase tracking-wider text-[11px] text-outline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-secondary">warehouse</span>
                    <span>Lokasi Alokasi Stok Masuk</span>
                </div>
                <div class="font-bold text-sm text-on-surface">{{ $order->targetLocation->name ?? 'Gudang Pusat' }}</div>
                <div class="text-on-surface-variant">Tipe: {{ ucfirst($order->targetLocation->type ?? 'Gudang') }} ({{ $order->targetLocation->code ?? '-' }})</div>
                <div class="text-on-surface-variant">Alamat: {{ $order->targetLocation->address ?? '-' }}</div>
            </div>
        </div>

        <!-- Tabel Barang yang Dipesan -->
        <div class="space-y-3">
            <h4 class="text-xs font-bold text-on-surface uppercase tracking-wider text-outline">Daftar Barang yang Dipesan</h4>
            
            <div class="overflow-x-auto border border-surface-container rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-surface-container-low text-outline font-semibold border-b border-surface-container">
                            <th class="py-3 px-4">Nama Produk Baju Bayi</th>
                            <th class="py-3 px-4 text-center">Jumlah Dipesan</th>
                            <th class="py-3 px-4 text-center">Jumlah Diterima</th>
                            <th class="py-3 px-4 text-right">Harga Modal</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-low">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="font-bold text-on-surface">{{ $item->product->name }}</div>
                                <div class="text-[11px] text-outline">SKU: {{ $item->product->sku }} • Varian: {{ $item->product->variants ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-on-surface">{{ $item->quantity_ordered }} pcs</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded font-bold {{ $item->quantity_received >= $item->quantity_ordered ? 'bg-green-100 text-green-800' : ($item->quantity_received > 0 ? 'bg-amber-100 text-amber-800' : 'bg-surface-container text-outline') }}">
                                    {{ $item->quantity_received }} pcs
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-on-surface">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold text-on-surface">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-surface-container-low/70 font-bold text-on-surface">
                            <td colspan="4" class="py-3 px-4 text-right text-xs uppercase tracking-wider">Total Biaya Pemesanan:</td>
                            <td class="py-3 px-4 text-right text-sm text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($order->notes)
            <div class="p-3.5 rounded-xl bg-surface-container-low text-xs space-y-1">
                <span class="font-semibold text-outline">Catatan Pemesanan:</span>
                <p class="text-on-surface-variant">{{ $order->notes }}</p>
            </div>
        @endif

    </div>

</div>
@endsection
