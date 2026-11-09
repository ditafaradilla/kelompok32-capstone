@extends('Admin.layout')

@section('title', 'Detail Pesanan #' . $order->nomor_pesanan)
@section('page-title', 'Detail Pesanan #' . $order->nomor_pesanan)
@section('page-subtitle', 'Periksa informasi pesanan pelanggan, verifikasi bukti pembayaran, dan proses pesanan')

@section('content')
<div class="flex flex-col w-full gap-6 pb-12">

    <!-- ACTION HEADER & KEMBALI -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-5 rounded-2xl border border-surface-container shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pesanan.index') }}" 
               class="w-9 h-9 rounded-xl bg-surface-container hover:bg-surface-container-high transition-colors flex items-center justify-center text-on-surface">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-on-surface font-mono">#{{ $order->nomor_pesanan }}</h3>
                    <span class="text-xs text-outline">•</span>
                    <span class="text-xs text-on-surface-variant">{{ $order->tanggal_pesanan->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
                <p class="text-xs text-on-surface-variant mt-0.5">Pemesan: <strong>{{ $order->user?->name ?? 'Tamu' }}</strong> ({{ $order->user?->email ?? '-' }})</p>
            </div>
        </div>

        <div>
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
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $orderBadge }}">
                <span class="w-2 h-2 rounded-full bg-current"></span>
                Status: {{ $order->status_label }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- KOLOM KIRI: ITEM PESANAN & DATA PENGIRIMAN (7 kolom) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- TABEL ITEM PESANAN -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-container pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">shopping_cart</span>
                        <h4 class="text-sm font-bold text-on-surface">Item yang Dipesan</h4>
                    </div>
                    <span class="text-xs text-outline">{{ $order->items->count() }} jenis barang • {{ $order->items->sum('jumlah') }} pcs</span>
                </div>

                <div class="divide-y divide-surface-container">
                    @foreach($order->items as $item)
                        @php
                            $variant = $item->variant;
                            $product = $item->product ?: $variant?->product;
                        @endphp
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-start gap-3.5">
                            <div class="w-14 h-14 rounded-xl bg-surface-container overflow-hidden shrink-0 border border-surface-container flex items-center justify-center">
                                @if($product && $product->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->photo))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->photo) }}" 
                                         alt="{{ $item->nama_produk }}" 
                                         class="w-full h-full object-cover" />
                                @else
                                    <span class="material-symbols-outlined text-outline text-xl">checkroom</span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 text-xs">
                                <h5 class="font-bold text-on-surface line-clamp-1">{{ $item->nama_produk }}</h5>
                                <p class="text-outline text-[11px] mt-0.5">Varian: <span class="font-semibold text-on-surface-variant">{{ $item->varian_label }}</span></p>
                                <p class="text-on-surface-variant text-[11px] mt-1">{{ rupiah($item->harga_satuan) }} × {{ $item->jumlah }}</p>
                            </div>

                            <div class="text-right shrink-0 text-xs">
                                <span class="font-bold text-on-surface">{{ rupiah($item->subtotal) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Total Kalkulasi -->
                <div class="border-t border-surface-container pt-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-on-surface-variant">
                        <span>Subtotal Produk</span>
                        <span class="font-semibold text-on-surface">{{ rupiah($order->items->sum('subtotal')) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-on-surface-variant">
                        <span>Ongkos Kirim</span>
                        <span class="font-semibold text-primary">Rp 0 (Gratis)</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-bold text-on-surface pt-2 border-t border-surface-container">
                        <span>Total Nilai Pesanan</span>
                        <span class="text-primary text-base">{{ rupiah($order->total_harga) }}</span>
                    </div>
                </div>
            </div>

            <!-- INFORMASI PENGIRIMAN -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">local_shipping</span>
                    <h4 class="text-sm font-bold text-on-surface">Data Penerima & Alamat Pengiriman</h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-outline text-[11px] block">Nama Penerima:</span>
                        <p class="font-bold text-on-surface mt-0.5">{{ $order->nama_penerima }}</p>
                    </div>

                    <div>
                        <span class="text-outline text-[11px] block">No. Telepon / WhatsApp:</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <p class="font-semibold text-on-surface">{{ $order->telepon_penerima }}</p>
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $order->telepon_penerima);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold inline-flex items-center text-[11px] gap-0.5">
                                <span class="material-symbols-outlined text-xs">chat</span> WA
                            </a>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-outline text-[11px] block">Alamat Tujuan Lengkap:</span>
                        <p class="font-medium text-on-surface mt-0.5 leading-relaxed bg-surface-container-low p-3 rounded-xl border border-surface-container">
                            {{ $order->alamat_pengiriman }}
                        </p>
                    </div>

                    @if($order->catatan)
                        <div class="sm:col-span-2">
                            <span class="text-outline text-[11px] block">Catatan Pelanggan:</span>
                            <p class="text-on-surface-variant italic mt-0.5 bg-surface-container-low p-2.5 rounded-xl border border-surface-container">
                                "{{ $order->catatan }}"
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: BUKTI PEMBAYARAN & TINDAKAN VERIFIKASI (5 kolom) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- INFORMASI STATUS PEMBAYARAN -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-container pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">payments</span>
                        <h4 class="text-sm font-bold text-on-surface">Informasi Pembayaran</h4>
                    </div>
                    @php
                        $payClass = match($order->payment?->status_pembayaran) {
                            \App\Models\Payment::STATUS_LUNAS => 'bg-green-100 text-green-800',
                            \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI => 'bg-blue-100 text-blue-800 font-bold',
                            \App\Models\Payment::STATUS_DITOLAK => 'bg-rose-100 text-rose-800',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $payClass }}">
                        {{ $order->payment?->status_label ?? 'Belum Bayar' }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-outline">Metode:</span>
                        <span class="font-bold text-on-surface">{{ $order->payment?->metode_label ?? 'Transfer Bank' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-outline">Jumlah yang Harus Dibayar:</span>
                        <span class="font-bold text-primary">{{ rupiah($order->total_harga) }}</span>
                    </div>

                    @if($order->payment?->tanggal_bayar)
                        <div class="flex items-center justify-between">
                            <span class="text-outline">Waktu Pelanggan Bayar:</span>
                            <span class="text-on-surface">{{ $order->payment->tanggal_bayar->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    @endif

                    @if($order->payment?->catatan_admin)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
                            <span class="font-bold block text-[11px]">Catatan Penolakan Admin:</span>
                            <p class="mt-0.5">{{ $order->payment->catatan_admin }}</p>
                        </div>
                    @endif
                </div>

                <!-- BUKTI PEMBAYARAN -->
                <div class="pt-3 border-t border-surface-container">
                    <span class="text-xs font-bold text-on-surface block mb-2">Lampiran Bukti Bayar:</span>
                    
                    @if($order->payment?->bukti_bayar)
                        @php
                            $isPdf = str_ends_with(strtolower($order->payment->bukti_bayar), '.pdf');
                            $proofUrl = \Illuminate\Support\Facades\Storage::url($order->payment->bukti_bayar);
                        @endphp

                        @if($isPdf)
                            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-rose-600 text-2xl">picture_as_pdf</span>
                                    <div>
                                        <span class="font-bold text-xs text-on-surface block">Dokumen Bukti (PDF)</span>
                                        <span class="text-[11px] text-outline">Klik tombol untuk membaca</span>
                                    </div>
                                </div>
                                <a href="{{ $proofUrl }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-high transition-colors">
                                    Buka PDF
                                </a>
                            </div>
                        @else
                            <div class="space-y-2">
                                <div class="w-full max-h-72 rounded-xl bg-surface-container-low overflow-hidden border border-surface-container flex items-center justify-center">
                                    <img src="{{ $proofUrl }}" alt="Bukti Pembayaran" class="max-h-72 w-auto object-contain" />
                                </div>
                                <div class="text-center">
                                    <a href="{{ $proofUrl }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-primary font-bold hover:underline">
                                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                                        <span>Lihat Gambar Ukuran Penuh</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs text-center">
                            <span class="material-symbols-outlined text-2xl block text-amber-500 mb-1">pending</span>
                            Pelanggan belum mengunggah file bukti transfer/pembayaran.
                        </div>
                    @endif
                </div>
            </div>

            <!-- TINDAKAN VERIFIKASI / PENOLAKAN OLEH ADMIN -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <h4 class="text-sm font-bold text-on-surface border-b border-surface-container pb-3">
                    Tindakan Admin
                </h4>

                @if($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI)
                    <div class="space-y-4">
                        <!-- 1. Tombol Verifikasi & Proses -->
                        <form action="{{ route('admin.pesanan.verify', $order) }}" 
                              method="POST" 
                              onsubmit="return confirm('Verifikasi pembayaran pesanan ini? Status pesanan akan diubah menjadi Diproses dan transaksi pemasukan akan dicatat ke pembukuan.');">
                            @csrf
                            <button type="submit" 
                                    class="w-full h-11 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-xs">
                                <span class="material-symbols-outlined text-base">verified</span>
                                <span>Verifikasi & Proses Pesanan</span>
                            </button>
                        </form>

                        <div class="relative flex items-center justify-center my-2">
                            <div class="border-t border-surface-container w-full"></div>
                            <span class="bg-surface-container-lowest px-2 text-[11px] text-outline absolute">atau tolak bukti</span>
                        </div>

                        <!-- 2. Formulir Penolakan Bukti Bayar -->
                        <form action="{{ route('admin.pesanan.reject', $order) }}" 
                              method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menolak bukti pembayaran ini? Pelanggan akan diminta mengunggah ulang.');"
                              class="space-y-2.5">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-semibold text-on-surface mb-1">
                                    Alasan Penolakan (akan dibaca pelanggan) *
                                </label>
                                <textarea name="catatan_admin" 
                                          rows="3" 
                                          required 
                                          placeholder="Contoh: Nominal transfer kurang, atau foto resi tidak jelas terbaca..." 
                                          class="w-full p-2.5 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-error border-0"></textarea>
                                @error('catatan_admin')
                                    <span class="text-error text-[11px]">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" 
                                    class="w-full h-10 rounded-xl bg-surface-container-low text-rose-700 text-xs font-bold hover:bg-rose-50 transition-colors flex items-center justify-center gap-1.5 border border-rose-200">
                                <span class="material-symbols-outlined text-sm">cancel</span>
                                <span>Tolak Bukti Pembayaran</span>
                            </button>
                        </form>
                    </div>

                @elseif($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_LUNAS)
                    <div class="p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-teal-600 text-xl shrink-0">check_circle</span>
                        <div>
                            <span class="font-bold block">Pembayaran Terverifikasi (Lunas)</span>
                            <span class="text-[11px]">Pemasukan senilai {{ rupiah($order->total_harga) }} telah dicatat pada sistem pembukuan.</span>
                        </div>
                    </div>

                @elseif($order->status_pesanan === \App\Models\Order::STATUS_DIBATALKAN)
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-rose-600 text-xl shrink-0">cancel</span>
                        <div>
                            <span class="font-bold block">Pesanan Dibatalkan</span>
                            <span class="text-[11px]">Pesanan ini telah dibatalkan dan stok item telah dikembalikan ke toko.</span>
                        </div>
                    </div>

                @else
                    <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container text-on-surface-variant text-xs">
                        <span>Menunggu tindakan pembayaran dari pelanggan. Admin dapat melakukan verifikasi setelah pelanggan mengunggah bukti bayar.</span>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

