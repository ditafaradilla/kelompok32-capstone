@extends('Admin.layout')

@section('title', 'Penerimaan Barang Masuk')
@section('page-title', 'Fitur 11: Penerimaan Barang & Peringatan Stok Habis')
@section('page-subtitle', 'Sesuai Diagram Alir 4: Pilih catatan pesanan yang barangnya tiba, konfirmasi jumlah fisik yang diterima, dan tambah stok')

@section('content')
<div class="flex flex-col w-full gap-6">

    <!-- ACTION HEADER -->
    <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs space-y-1">
        <h3 class="text-sm font-bold text-on-surface">Pilih Catatan Pesanan ke Pemasok (Penerimaan Barang)</h3>
        <p class="text-xs text-on-surface-variant">Sesuai Diagram Alir 4: Pilih pesanan yang kirimannya telah sampai di toko atau gudang untuk diproses penerimaan fisiknya</p>
    </div>

    <!-- TABEL DAFTAR PESANAN UNTUK PENERIMAAN (Diagram 4) -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-surface-container-low/60 text-outline font-semibold border-b border-surface-container">
                        <th class="py-3.5 px-4">No. PO</th>
                        <th class="py-3.5 px-4">Pemasok</th>
                        <th class="py-3.5 px-4">Tujuan Gudang/Toko</th>
                        <th class="py-3.5 px-4">Tanggal Pesan</th>
                        <th class="py-3.5 px-4">Rincian Barang</th>
                        <th class="py-3.5 px-4 text-center">Status Barang</th>
                        <th class="py-3.5 px-4 text-right">Aksi Penerimaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @forelse($orders as $order)
                    <tr class="hover:bg-surface-container-low/40 transition-colors {{ $order->status === 'pending' ? 'bg-amber-50/20' : '' }}">
                        <td class="py-3.5 px-4 font-bold text-primary font-mono">{{ $order->po_number }}</td>
                        <td class="py-3.5 px-4 font-semibold text-on-surface">
                            {{ $order->supplier->name }}
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant">
                            <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface text-[10px] font-medium">
                                {{ $order->targetLocation->name ?? 'Gudang Pusat' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant">{{ date('d M Y', strtotime($order->order_date)) }}</td>
                        <td class="py-3.5 px-4 text-on-surface-variant">
                            @foreach($order->items as $item)
                                <div class="text-[11px]">&bull; {{ $item->product->name ?? 'Produk' }} ({{ $item->quantity_ordered }} pcs)</div>
                            @endforeach
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($order->status === 'received')
                                <span class="px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container font-semibold text-[10px]">Sudah Diterima</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-[10px]">Menunggu Kiriman</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <!-- Admin memilih catatan pesanan yang barangnya datang (Diagram 4) -->
                            <a href="{{ route('admin.penerimaan.show', $order->id) }}" class="px-3.5 py-1.5 rounded-lg {{ $order->status === 'pending' ? 'bg-primary text-on-primary hover:bg-surface-tint' : 'bg-surface-container text-on-surface hover:bg-surface-container-high' }} text-xs font-semibold transition-all inline-flex items-center gap-1.5 shadow-xs">
                                <span class="material-symbols-outlined text-xs">{{ $order->status === 'pending' ? 'inventory' : 'visibility' }}</span>
                                <span>{{ $order->status === 'pending' ? 'Proses Barang Datang' : 'Lihat Hasil Penerimaan' }}</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-outline">
                            <span class="material-symbols-outlined text-4xl mb-2 text-outline/50 block">inventory_2</span>
                            Belum ada catatan pemesanan barang ke pemasok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-surface-container">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
