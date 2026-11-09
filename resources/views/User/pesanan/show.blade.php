@extends('User.layout')

@section('title', 'Detail Pesanan #' . $order->nomor_pesanan . ' - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.index') }}" class="hover:text-primary transition-colors">Katalog</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Pesanan #{{ $order->nomor_pesanan }}</span>
    </nav>

    <!-- Header & Status Pesanan -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono text-outline">Nomor Pesanan:</span>
                <span class="text-sm font-mono font-bold text-on-surface">#{{ $order->nomor_pesanan }}</span>
                <span class="text-xs text-outline">•</span>
                <span class="text-xs text-on-surface-variant">{{ $order->tanggal_pesanan->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight mt-1">Detail Pesanan</h1>
        </div>

        <div>
            @php
                $badgeClasses = match($order->status_pesanan) {
                    \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 'bg-amber-100 text-amber-800 border-amber-300',
                    \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI => 'bg-blue-100 text-blue-800 border-blue-300',
                    \App\Models\Order::STATUS_DIPROSES             => 'bg-teal-100 text-teal-800 border-teal-300',
                    \App\Models\Order::STATUS_DIKIRIM              => 'bg-purple-100 text-purple-800 border-purple-300',
                    \App\Models\Order::STATUS_SELESAI              => 'bg-green-100 text-green-800 border-green-300',
                    \App\Models\Order::STATUS_DIBATALKAN           => 'bg-rose-100 text-rose-800 border-rose-300',
                    default                                        => 'bg-gray-100 text-gray-800 border-gray-300',
                };
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                <span class="w-2 h-2 rounded-full bg-current"></span>
                {{ $order->status_label }}
            </span>
        </div>
    </div>

    <!-- Alert Status Khusus -->
    @if($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN)
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-start sm:items-center gap-2.5">
                <span class="material-symbols-outlined text-amber-600 text-2xl shrink-0">pending_actions</span>
                <div>
                    <span class="font-bold block text-sm">Menunggu Pembayaran</span>
                    <span>Silakan selesaikan pembayaran dan unggah bukti transfer agar pesanan Anda dapat segera kami proses.</span>
                </div>
            </div>
            <a href="{{ route('pesanan.pembayaran', $order) }}" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-primary/90 transition-all text-center shrink-0 shadow-sm">
                Bayar Sekarang
            </a>
        </div>
    @elseif($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI)
        <div class="p-5 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-start sm:items-center gap-2.5">
                <span class="material-symbols-outlined text-blue-600 text-2xl shrink-0">hourglass_top</span>
                <div>
                    <span class="font-bold block text-sm">Bukti Pembayaran Sedang Diverifikasi</span>
                    <span>Admin kami sedang memeriksa bukti transfer Anda. Kami akan memperbarui status pesanan dalam waktu maksimal 1x24 jam.</span>
                </div>
            </div>
            <a href="{{ route('pesanan.pembayaran', $order) }}" class="px-4 py-2 rounded-xl bg-blue-100 text-blue-900 font-bold text-xs hover:bg-blue-200 transition-colors text-center shrink-0">
                Lihat Bukti Bayar
            </a>
        </div>
    @elseif($order->status_pesanan === \App\Models\Order::STATUS_DIPROSES)
        <div class="p-5 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center gap-2.5 shadow-xs">
            <span class="material-symbols-outlined text-teal-600 text-2xl shrink-0">inventory_2</span>
            <div>
                <span class="font-bold block text-sm">Pesanan Sedang Dipersiapkan</span>
                <span>Pembayaran Anda telah diverifikasi oleh admin. Kami sedang mengemas produk pesanan Anda dengan rapi dan aman.</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Kolom Kiri: Rincian Produk & Data Pengiriman (8 kolom) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Daftar Produk -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs space-y-5">
                <div class="flex items-center gap-2.5 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">shopping_bag</span>
                    <h2 class="text-base font-bold text-on-surface">Daftar Produk yang Dipesan</h2>
                </div>

                <div class="divide-y divide-surface-container">
                    @foreach($order->items as $item)
                        @php
                            $variant = $item->variant;
                            $product = $item->product ?: $variant?->product;
                        @endphp
                        <div class="py-4 first:pt-0 last:pb-0 flex items-start gap-4">
                            <!-- Foto Produk -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-surface-container overflow-hidden shrink-0 border border-surface-container flex items-center justify-center">
                                @if($product && $product->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->photo))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->photo) }}" 
                                         alt="{{ $item->nama_produk }}" 
                                         class="w-full h-full object-cover" />
                                @else
                                    <span class="material-symbols-outlined text-outline text-2xl">checkroom</span>
                                @endif
                            </div>

                            <!-- Info Item -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm text-on-surface line-clamp-1">{{ $item->nama_produk }}</h3>
                                <div class="flex items-center gap-2 text-xs text-outline mt-0.5">
                                    <span>Varian: <strong class="text-on-surface-variant">{{ $item->varian_label }}</strong></span>
                                    <span>•</span>
                                    <span>Jumlah: <strong class="text-on-surface-variant">{{ $item->jumlah }} pcs</strong></span>
                                </div>
                                <div class="text-xs text-on-surface-variant mt-1">
                                    {{ rupiah($item->harga_satuan) }} × {{ $item->jumlah }}
                                </div>
                            </div>

                            <!-- Subtotal Item -->
                            <div class="text-right shrink-0">
                                <span class="font-bold text-sm text-on-surface">{{ rupiah($item->subtotal) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Data Pengiriman -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs space-y-4">
                <div class="flex items-center gap-2.5 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">local_shipping</span>
                    <h2 class="text-base font-bold text-on-surface">Informasi Pengiriman</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1">
                        <span class="text-outline block">Nama Penerima</span>
                        <p class="font-bold text-on-surface text-sm">{{ $order->nama_penerima }}</p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-outline block">Nomor Telepon</span>
                        <p class="font-semibold text-on-surface">{{ $order->telepon_penerima }}</p>
                    </div>

                    <div class="sm:col-span-2 space-y-1 pt-1">
                        <span class="text-outline block">Alamat Tujuan</span>
                        <p class="font-medium text-on-surface leading-relaxed">{{ $order->alamat_pengiriman }}</p>
                    </div>

                    @if($order->catatan)
                        <div class="sm:col-span-2 space-y-1 pt-1 border-t border-surface-container">
                            <span class="text-outline block">Catatan untuk Penjual</span>
                            <p class="text-on-surface-variant italic">"{{ $order->catatan }}"</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Rincian Pembayaran & Aksi (4 kolom) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Ringkasan Pembayaran -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-7 border border-surface-container shadow-xs space-y-5">
                <h3 class="text-base font-bold text-on-surface border-b border-surface-container pb-3">
                    Informasi Pembayaran
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-outline">Metode Pembayaran</span>
                        <span class="font-bold text-on-surface">{{ $order->payment?->metode_label ?? 'Transfer Bank' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-outline">Status Pembayaran</span>
                        <span class="font-bold px-2.5 py-0.5 rounded-full text-[11px] {{ $order->payment?->status_pembayaran === \App\Models\Payment::STATUS_LUNAS ? 'bg-green-100 text-green-800' : ($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI ? 'bg-blue-100 text-blue-800' : ($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_DITOLAK ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')) }}">
                            {{ $order->payment?->status_label ?? 'Belum Bayar' }}
                        </span>
                    </div>

                    @if($order->payment?->tanggal_bayar)
                        <div class="flex items-center justify-between">
                            <span class="text-outline">Waktu Pembayaran</span>
                            <span class="text-on-surface">{{ $order->payment->tanggal_bayar->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    @endif

                    @if($order->payment?->bukti_bayar)
                        <div class="pt-2 border-t border-surface-container flex items-center justify-between">
                            <span class="text-outline">Bukti Transfer</span>
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($order->payment->bukti_bayar) }}" 
                               target="_blank" 
                               class="text-primary font-bold hover:underline inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Lihat File
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Rincian Biaya -->
                <div class="space-y-2.5 text-xs border-t border-surface-container pt-4">
                    <div class="flex items-center justify-between text-on-surface-variant">
                        <span>Subtotal Produk</span>
                        <span class="font-semibold text-on-surface">{{ rupiah($order->items->sum('subtotal')) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-on-surface-variant">
                        <span>Ongkos Kirim</span>
                        <span class="font-semibold text-primary">GRATIS</span>
                    </div>

                    <div class="flex items-center justify-between text-sm font-bold text-on-surface pt-3 border-t border-surface-container">
                        <span>Total Pembayaran</span>
                        <span class="text-lg text-primary">{{ rupiah($order->total_harga) }}</span>
                    </div>
                </div>

                <!-- Tombol Aksi Pesanan -->
                <div class="pt-2 space-y-3">
                    @if($order->canBeCancelled())
                        <a href="{{ route('pesanan.pembayaran', $order) }}" 
                           class="w-full h-11 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-sm">
                            <span class="material-symbols-outlined text-base">payments</span>
                            <span>Lanjutkan Pembayaran</span>
                        </a>

                        <form action="{{ route('pesanan.cancel', $order) }}" 
                              method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok produk akan dikembalikan ke toko.');">
                            @csrf
                            <button type="submit" 
                                    class="w-full h-10 rounded-xl bg-surface-container-low text-error text-xs font-bold hover:bg-rose-50 transition-colors flex items-center justify-center gap-1.5 border border-error/20">
                                <span class="material-symbols-outlined text-sm">close</span>
                                <span>Batalkan Pesanan</span>
                            </button>
                        </form>
                    @elseif($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI)
                        <a href="{{ route('pesanan.pembayaran', $order) }}" 
                           class="w-full h-10 rounded-xl bg-surface-container text-on-surface text-xs font-bold hover:bg-surface-container-high transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">receipt_long</span>
                            <span>Periksa / Ubah Bukti Bayar</span>
                        </a>
                    @endif

                    <!-- Hubungi Bantuan WhatsApp -->
                    @php
                        $waMsg = urlencode("Halo Admin Baby Corner, saya ingin bertanya tentang pesanan #" . $order->nomor_pesanan);
                        $waUrl = "https://wa.me/" . config('shop.whatsapp') . "?text=" . $waMsg;
                    @endphp
                    <a href="{{ $waUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="w-full h-10 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold hover:bg-emerald-100 transition-colors flex items-center justify-center gap-2 border border-emerald-200">
                        <span class="material-symbols-outlined text-emerald-600 text-base">chat</span>
                        <span>Bantuan CS via WhatsApp</span>
                    </a>

                    <a href="{{ route('produk.index') }}" 
                       class="w-full h-10 rounded-xl bg-surface-container-low text-on-surface-variant text-xs font-bold hover:text-primary transition-colors flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        <span>Kembali Belanja</span>
                    </a>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection

