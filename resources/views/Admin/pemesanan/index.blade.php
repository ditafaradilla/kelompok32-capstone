@extends('Admin.layout')

@section('title', 'Pemesanan Barang ke Pemasok')
@section('page-title', 'Fitur 10: Pemesanan Barang ke Pemasok')
@section('page-subtitle', 'Sesuai Diagram Alir: Catatan pesanan pembelian barang (Purchase Order) ke pemasok/konveksi baju bayi')

@section('content')
<div class="flex flex-col w-full gap-6">

    <!-- ACTION HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs">
        <div class="space-y-0.5">
            <h3 class="text-sm font-bold text-on-surface">Daftar Catatan Pemesanan ke Pemasok</h3>
            <p class="text-xs text-on-surface-variant">Pantau pesanan pembelian (PO), estimasi biaya, dan status pengiriman barang dari supplier</p>
        </div>

        <!-- Tombol Buat Pemesanan Baru (Sesuai Diagram 3) -->
        <a href="{{ route('admin.pemesanan.create') }}" class="px-5 h-10 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center justify-center gap-2 shadow-xs shrink-0">
            <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
            <span>Buat Pemesanan ke Pemasok</span>
        </a>
    </div>

    <!-- TABEL DAFTAR CATATAN PEMESANAN (Diagram 3) -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-surface-container-low/60 text-outline font-semibold border-b border-surface-container">
                        <th class="py-3.5 px-4">No. PO</th>
                        <th class="py-3.5 px-4">Nama Pemasok</th>
                        <th class="py-3.5 px-4">Lokasi Tujuan Stok</th>
                        <th class="py-3.5 px-4">Tanggal Pesan</th>
                        <th class="py-3.5 px-4">Jumlah Jenis Barang</th>
                        <th class="py-3.5 px-4">Total Biaya</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @forelse($orders as $order)
                    <tr class="hover:bg-surface-container-low/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-primary font-mono">{{ $order->po_number }}</td>
                        <td class="py-3.5 px-4 font-semibold text-on-surface">
                            {{ $order->supplier->name ?? '-' }}
                            <div class="text-[11px] text-outline font-normal">{{ $order->supplier->contact_person }} ({{ $order->supplier->phone }})</div>
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant">
                            <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface text-[10px] font-medium">
                                {{ $order->targetLocation->name ?? 'Gudang Pusat' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant">{{ date('d M Y', strtotime($order->order_date)) }}</td>
                        <td class="py-3.5 px-4 text-on-surface font-medium">{{ $order->items->count() }} item</td>
                        <td class="py-3.5 px-4 font-bold text-on-surface">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($order->status === 'received')
                                <span class="px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container font-semibold text-[10px]">Barang Tiba (Received)</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-[10px]">Menunggu Barang (Pending)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.pemesanan.show', $order->id) }}" class="px-3 py-1.5 rounded-lg bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-high transition-all">
                                    Detail
                                </a>
                                @if($order->status === 'pending')
                                    <a href="{{ route('admin.penerimaan.show', $order->id) }}" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all flex items-center gap-1 shadow-xs">
                                        <span class="material-symbols-outlined text-xs">inventory</span>
                                        <span>Terima</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-outline">
                            <span class="material-symbols-outlined text-4xl mb-2 text-outline/50 block">local_shipping</span>
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
