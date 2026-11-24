@extends('User.layout')

@section('title', 'Pelacakan Pesanan #' . $order->nomor_pesanan . ' - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('produk.index') }}" class="hover:text-primary transition-colors">Katalog</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Pelacakan Pesanan #{{ $order->nomor_pesanan }}</span>
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
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight mt-1">Pelacakan & Status Pesanan</h1>
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

    <!-- STEPPER STATUS PELACAKAN (FRI-05) -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs space-y-6">
        
        @if($order->status_pesanan === \App\Models\Order::STATUS_DIBATALKAN)
            <!-- Kondisi Khusus: Dibatalkan (Berwarna Error) -->
            <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start sm:items-center gap-3">
                <span class="material-symbols-outlined text-rose-600 text-3xl shrink-0">cancel</span>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-rose-900">Pesanan Ini Telah Dibatalkan</h3>
                    <p class="text-xs text-rose-800">
                        Pesanan telah dibatalkan dan proses transaksi dihentikan. Seluruh alokasi stok produk telah dikembalikan ke toko.
                    </p>
                </div>
            </div>
        @else
            @php
                $stepRanks = [
                    \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 1,
                    \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI => 2,
                    \App\Models\Order::STATUS_DIPROSES             => 3,
                    \App\Models\Order::STATUS_DIKIRIM              => 4,
                    \App\Models\Order::STATUS_SELESAI              => 5,
                ];
                $currentRank = $stepRanks[$order->status_pesanan] ?? 1;

                $steps = [
                    1 => ['key' => 'menunggu_pembayaran', 'title' => 'Menunggu Pembayaran', 'icon' => 'payments'],
                    2 => ['key' => 'menunggu_verifikasi', 'title' => 'Diverifikasi',         'icon' => 'verified_user'],
                    3 => ['key' => 'diproses',             'title' => 'Diproses',             'icon' => 'inventory_2'],
                    4 => ['key' => 'dikirim',              'title' => 'Dikirim',              'icon' => 'local_shipping'],
                    5 => ['key' => 'selesai',              'title' => 'Selesai',              'icon' => 'check_circle'],
                ];
            @endphp

            <!-- Stepper Linear Bar -->
            <div class="w-full">
                <div class="grid grid-cols-5 gap-2 relative">
                    @foreach($steps as $rank => $step)
                        @php
                            $isDone = $currentRank > $rank;
                            $isActive = $currentRank === $rank;
                        @endphp
                        <div class="flex flex-col items-center text-center relative z-10">
                            <!-- Step Circle -->
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center font-bold text-xs sm:text-sm transition-all {{ $isDone ? 'bg-primary text-white shadow-xs' : ($isActive ? 'bg-primary-container text-on-primary-container ring-4 ring-primary/20 font-extrabold shadow-sm' : 'bg-surface-container text-outline') }}">
                                @if($isDone)
                                    <span class="material-symbols-outlined text-base sm:text-xl">check</span>
                                @else
                                    <span class="material-symbols-outlined text-base sm:text-xl">{{ $step['icon'] }}</span>
                                @endif
                            </div>
                            <!-- Step Title -->
                            <span class="mt-2 text-[11px] sm:text-xs font-semibold line-clamp-2 {{ $isActive ? 'text-primary font-bold' : ($isDone ? 'text-on-surface' : 'text-outline') }}">
                                {{ $step['title'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Banner Aksi Tambahan Berdasarkan Status -->
        @if($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-xl shrink-0">pending_actions</span>
                    <span>Segera selesaikan pembayaran agar pesanan dapat diproses ke tahap verifikasi.</span>
                </div>
                <a href="{{ route('pesanan.pembayaran', $order) }}" class="px-4 py-2 rounded-xl bg-primary text-white font-bold text-xs hover:bg-primary/90 transition-all text-center shrink-0">
                    Bayar Sekarang
                </a>
            </div>
        @elseif($order->status_pesanan === \App\Models\Order::STATUS_DIKIRIM)
            <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="material-symbols-outlined text-emerald-600 text-2xl shrink-0">package_2</span>
                    <div>
                        <h4 class="font-bold text-xs text-emerald-900">Pesanan Sedang Dalam Perjalanan</h4>
                        <p class="text-[11px] text-emerald-800 mt-0.5">Sudah menerima paket pesanan dengan baik? Tekan tombol di samping untuk menyelesaikan transaksi.</p>
                    </div>
                </div>
                <!-- Tombol Pesanan Diterima (FRI-05) -->
                <form action="{{ route('pesanan.terima', $order) }}" 
                      method="POST" 
                      onsubmit="return confirm('Konfirmasi bahwa Anda telah menerima pesanan ini dengan kondisi baik? Status akan diselesaikan.');"
                      class="shrink-0">
                    @csrf
                    <button type="submit" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Pesanan Diterima</span>
                    </button>
                </form>
            </div>
        @elseif($order->status_pesanan === \App\Models\Order::STATUS_SELESAI)
            <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-900 text-xs flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-green-600 text-xl">verified</span>
                <span>Pesanan telah selesai diterima. Terima kasih telah berbelanja di Baby Corner!</span>
            </div>
        @endif

    </div>

    <!-- KARTU INFORMASI RESI & KURIR (Bila Ada) -->
    @if($order->kurir || $order->nomor_resi)
        <div class="bg-surface-container-lowest rounded-3xl p-6 border border-surface-container shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">local_shipping</span>
                </div>
                <div>
                    <span class="text-xs text-outline block">Kurir / Ekspedisi Pengiriman:</span>
                    <h3 class="text-sm font-bold text-on-surface">{{ $order->kurir ?: 'Ekspedisi Rekanan' }}</h3>
                </div>
            </div>

            @if($order->nomor_resi)
                <div class="flex items-center gap-2 bg-surface-container-low px-4 py-2.5 rounded-2xl border border-surface-container">
                    <div>
                        <span class="text-[10px] text-outline uppercase font-semibold block">Nomor Resi:</span>
                        <span id="nomor-resi-text" class="text-xs font-mono font-bold text-on-surface tracking-wider">{{ $order->nomor_resi }}</span>
                    </div>
                    <button type="button" 
                            onclick="navigator.clipboard.writeText('{{ $order->nomor_resi }}'); alert('Nomor resi {{ $order->nomor_resi }} berhasil disalin ke clipboard!');" 
                            title="Salin Nomor Resi"
                            class="ml-2 px-2.5 py-1.5 rounded-xl bg-surface-container text-primary text-[11px] font-bold hover:bg-surface-container-high transition-colors flex items-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-xs">content_copy</span>
                        <span>Salin</span>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Kolom Kiri: Riwayat Status & Daftar Item (8 kolom) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- TIMELINE RIWAYAT STATUS PESANAN (FRI-05) -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs space-y-5">
                <div class="flex items-center gap-2.5 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">history</span>
                    <h2 class="text-base font-bold text-on-surface">Riwayat Perjalanan Pesanan</h2>
                </div>

                <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-surface-container">
                    @forelse($order->histories as $history)
                        @php
                            $histBadge = match($history->status) {
                                \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 'bg-amber-100 text-amber-800 border-amber-300',
                                \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI => 'bg-blue-100 text-blue-800 border-blue-300',
                                \App\Models\Order::STATUS_DIPROSES             => 'bg-teal-100 text-teal-800 border-teal-300',
                                \App\Models\Order::STATUS_DIKIRIM              => 'bg-purple-100 text-purple-800 border-purple-300',
                                \App\Models\Order::STATUS_SELESAI              => 'bg-green-100 text-green-800 border-green-300',
                                \App\Models\Order::STATUS_DIBATALKAN           => 'bg-rose-100 text-rose-800 border-rose-300',
                                default                                        => 'bg-gray-100 text-gray-800 border-gray-300',
                            };
                        @endphp
                        <div class="relative">
                            <!-- Bullet Icon on Timeline -->
                            <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-surface-container-lowest border-2 border-primary flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full bg-primary"></div>
                            </div>

                            <div class="bg-surface-container-low/60 rounded-2xl p-4 border border-surface-container text-xs space-y-1.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $histBadge }}">
                                        {{ $history->status_label }}
                                    </span>
                                    <span class="text-[11px] text-outline">
                                        {{ $history->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </span>
                                </div>

                                @if($history->catatan)
                                    <p class="text-on-surface mt-1 leading-relaxed">{{ $history->catatan }}</p>
                                @endif

                                @if($history->changer)
                                    <div class="text-[10px] text-outline">
                                        Oleh: <span class="font-semibold text-on-surface-variant">{{ $history->changer->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-outline italic">
                            Belum ada riwayat tercatat untuk pesanan ini.
                        </div>
                    @endforelse
                </div>
            </div>

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
                                    class="w-full h-10 rounded-xl bg-surface-container-low text-error text-xs font-bold hover:bg-rose-50 transition-colors flex items-center justify-center gap-1.5 border border-error/20 cursor-pointer">
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
                        $waMsg = urlencode("Halo Admin Baby Corner, saya ingin bertanya tentang pelacakan pesanan #" . $order->nomor_pesanan);
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
