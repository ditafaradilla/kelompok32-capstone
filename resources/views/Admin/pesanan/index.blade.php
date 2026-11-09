@extends('Admin.layout')

@section('title', 'Kelola Pesanan Pelanggan')
@section('page-title', 'Kelola Pesanan Pelanggan')
@section('page-subtitle', 'Pantau pesanan online, verifikasi bukti pembayaran (transfer & QRIS), dan perbarui status pesanan')

@section('content')
<div class="flex flex-col w-full gap-6">

    <!-- STATISTIK RINGKASAN STATUS PESANAN -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Semua -->
        <a href="{{ route('admin.pesanan.index') }}" 
           class="p-4 rounded-2xl border transition-all {{ !request('status') ? 'bg-primary/10 border-primary shadow-xs' : 'bg-surface-container-lowest border-surface-container hover:bg-surface-container-low' }}">
            <span class="text-[11px] font-semibold text-outline block">Semua Pesanan</span>
            <span class="text-xl font-bold text-on-surface mt-1 block">{{ $counts['total'] }}</span>
        </a>

        <!-- Menunggu Pembayaran -->
        <a href="{{ route('admin.pesanan.index', ['status' => \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN]) }}" 
           class="p-4 rounded-2xl border transition-all {{ request('status') === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN ? 'bg-amber-100/60 border-amber-400 shadow-xs' : 'bg-surface-container-lowest border-surface-container hover:bg-surface-container-low' }}">
            <span class="text-[11px] font-semibold text-amber-800 block">Menunggu Bayar</span>
            <span class="text-xl font-bold text-amber-900 mt-1 block">{{ $counts['menunggu_pembayaran'] }}</span>
        </a>

        <!-- Menunggu Verifikasi (Butuh Perhatian Admin) -->
        <a href="{{ route('admin.pesanan.index', ['status' => \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI]) }}" 
           class="p-4 rounded-2xl border transition-all {{ request('status') === \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI ? 'bg-blue-100 border-blue-400 shadow-xs' : 'bg-blue-50/50 border-blue-200 hover:bg-blue-100/60' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-blue-800 block">Verifikasi Bukti</span>
                @if($counts['menunggu_verifikasi'] > 0)
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                @endif
            </div>
            <span class="text-xl font-bold text-blue-900 mt-1 block">{{ $counts['menunggu_verifikasi'] }}</span>
        </a>

        <!-- Diproses -->
        <a href="{{ route('admin.pesanan.index', ['status' => \App\Models\Order::STATUS_DIPROSES]) }}" 
           class="p-4 rounded-2xl border transition-all {{ request('status') === \App\Models\Order::STATUS_DIPROSES ? 'bg-teal-100/60 border-teal-400 shadow-xs' : 'bg-surface-container-lowest border-surface-container hover:bg-surface-container-low' }}">
            <span class="text-[11px] font-semibold text-teal-800 block">Diproses</span>
            <span class="text-xl font-bold text-teal-900 mt-1 block">{{ $counts['diproses'] }}</span>
        </a>

        <!-- Selesai -->
        <a href="{{ route('admin.pesanan.index', ['status' => \App\Models\Order::STATUS_SELESAI]) }}" 
           class="p-4 rounded-2xl border transition-all {{ request('status') === \App\Models\Order::STATUS_SELESAI ? 'bg-green-100/60 border-green-400 shadow-xs' : 'bg-surface-container-lowest border-surface-container hover:bg-surface-container-low' }}">
            <span class="text-[11px] font-semibold text-green-800 block">Selesai</span>
            <span class="text-xl font-bold text-green-900 mt-1 block">{{ $counts['selesai'] }}</span>
        </a>

        <!-- Dibatalkan -->
        <a href="{{ route('admin.pesanan.index', ['status' => \App\Models\Order::STATUS_DIBATALKAN]) }}" 
           class="p-4 rounded-2xl border transition-all {{ request('status') === \App\Models\Order::STATUS_DIBATALKAN ? 'bg-rose-100/60 border-rose-400 shadow-xs' : 'bg-surface-container-lowest border-surface-container hover:bg-surface-container-low' }}">
            <span class="text-[11px] font-semibold text-rose-800 block">Dibatalkan</span>
            <span class="text-xl font-bold text-rose-900 mt-1 block">{{ $counts['dibatalkan'] }}</span>
        </a>
    </div>

    <!-- FILTER & PENCARIAN -->
    <div class="bg-surface-container-lowest p-4 rounded-2xl border border-surface-container shadow-xs">
        <form method="GET" action="{{ route('admin.pesanan.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-base">search</span>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nomor pesanan, nama penerima, atau email akun..." 
                       class="w-full h-10 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
            </div>

            <div class="w-full sm:w-52">
                <select name="status" 
                        class="w-full h-10 px-3 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0">
                    <option value="">Semua Status</option>
                    <option value="{{ \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN }}" {{ request('status') === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="{{ \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI }}" {{ request('status') === \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="{{ \App\Models\Order::STATUS_DIPROSES }}" {{ request('status') === \App\Models\Order::STATUS_DIPROSES ? 'selected' : '' }}>Diproses</option>
                    <option value="{{ \App\Models\Order::STATUS_DIKIRIM }}" {{ request('status') === \App\Models\Order::STATUS_DIKIRIM ? 'selected' : '' }}>Dikirim</option>
                    <option value="{{ \App\Models\Order::STATUS_SELESAI }}" {{ request('status') === \App\Models\Order::STATUS_SELESAI ? 'selected' : '' }}>Selesai</option>
                    <option value="{{ \App\Models\Order::STATUS_DIBATALKAN }}" {{ request('status') === \App\Models\Order::STATUS_DIBATALKAN ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 h-10 rounded-xl bg-primary text-white text-xs font-semibold hover:bg-primary/90 transition-all flex items-center justify-center gap-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.pesanan.index') }}" class="px-3 h-10 rounded-xl bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-high transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL DAFTAR PESANAN -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-surface-container-low/60 text-outline font-semibold border-b border-surface-container">
                        <th class="py-3.5 px-4">No. Pesanan & Tanggal</th>
                        <th class="py-3.5 px-4">Penerima & Alamat</th>
                        <th class="py-3.5 px-4">Rincian Item</th>
                        <th class="py-3.5 px-4">Total Biaya</th>
                        <th class="py-3.5 px-4">Pembayaran</th>
                        <th class="py-3.5 px-4 text-center">Status Pesanan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @forelse($orders as $order)
                        <tr class="hover:bg-surface-container-low/40 transition-colors">
                            <!-- Nomor Pesanan & Tanggal -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.pesanan.show', $order) }}" class="font-bold font-mono text-primary hover:underline">
                                    #{{ $order->nomor_pesanan }}
                                </a>
                                <div class="text-[11px] text-outline mt-0.5">
                                    {{ $order->tanggal_pesanan->translatedFormat('d M Y, H:i') }}
                                </div>
                            </td>

                            <!-- Penerima & Alamat -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-on-surface">{{ $order->nama_penerima }}</div>
                                <div class="text-[11px] text-outline">{{ $order->telepon_penerima }}</div>
                                <div class="text-[11px] text-on-surface-variant line-clamp-1 max-w-xs mt-0.5" title="{{ $order->alamat_pengiriman }}">
                                    {{ $order->alamat_pengiriman }}
                                </div>
                            </td>

                            <!-- Rincian Item -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-on-surface">{{ $order->items->count() }} macam item ({{ $order->items->sum('jumlah') }} pcs)</div>
                                <div class="text-[11px] text-outline line-clamp-1 max-w-xs">
                                    {{ $order->items->pluck('nama_produk')->take(2)->implode(', ') }}
                                    @if($order->items->count() > 2) ... @endif
                                </div>
                            </td>

                            <!-- Total Biaya -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-on-surface">{{ rupiah($order->total_harga) }}</span>
                            </td>

                            <!-- Pembayaran -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-on-surface">{{ $order->payment?->metode_label ?? 'Transfer Bank' }}</div>
                                <div>
                                    @php
                                        $payClass = match($order->payment?->status_pembayaran) {
                                            \App\Models\Payment::STATUS_LUNAS => 'bg-green-100 text-green-800',
                                            \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI => 'bg-blue-100 text-blue-800 font-bold',
                                            \App\Models\Payment::STATUS_DITOLAK => 'bg-rose-100 text-rose-800',
                                            default => 'bg-amber-100 text-amber-800',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] {{ $payClass }}">
                                        {{ $order->payment?->status_label ?? 'Belum Bayar' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status Pesanan -->
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $orderBadge = match($order->status_pesanan) {
                                        \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 'bg-amber-100 text-amber-800 border-amber-300',
                                        \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI => 'bg-blue-100 text-blue-800 border-blue-300 font-bold',
                                        \App\Models\Order::STATUS_DIPROSES             => 'bg-teal-100 text-teal-800 border-teal-300',
                                        \App\Models\Order::STATUS_DIKIRIM              => 'bg-purple-100 text-purple-800 border-purple-300',
                                        \App\Models\Order::STATUS_SELESAI              => 'bg-green-100 text-green-800 border-green-300',
                                        \App\Models\Order::STATUS_DIBATALKAN           => 'bg-rose-100 text-rose-800 border-rose-300',
                                        default                                        => 'bg-gray-100 text-gray-800 border-gray-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $orderBadge }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.pesanan.show', $order) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg {{ $order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI ? 'bg-primary text-white font-bold hover:bg-primary/90 shadow-xs' : 'bg-surface-container text-on-surface font-semibold hover:bg-surface-container-high' }} transition-all">
                                    <span>Detail</span>
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-outline">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-outline/50">receipt_long</span>
                                <p class="font-semibold text-xs">Belum ada pesanan ditemukan.</p>
                                <p class="text-[11px] text-outline mt-0.5">Semua transaksi pesanan pelanggan online akan dicatat di sini.</p>
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

