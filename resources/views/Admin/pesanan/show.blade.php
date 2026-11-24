@extends('Admin.layout')

@section('title', 'Detail Pesanan #' . $order->nomor_pesanan)
@section('page-title', 'Detail Pesanan #' . $order->nomor_pesanan)
@section('page-subtitle', 'Periksa informasi pesanan pelanggan, verifikasi bukti pembayaran, dan perbarui status pesanan')

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

        <div class="flex items-center gap-2 flex-wrap">
            @if($order->kurir && $order->nomor_resi)
                <div class="px-3 py-1 rounded-full bg-surface-container text-xs font-mono font-medium text-on-surface border border-surface-container-high flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs text-primary">local_shipping</span>
                    <span>{{ $order->kurir }}: <strong>{{ $order->nomor_resi }}</strong></span>
                </div>
            @endif

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

        <!-- KOLOM KIRI: ITEM PESANAN, DATA PENGIRIMAN & RIWAYAT STATUS (7 kolom) -->
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

            <!-- INFORMASI PENGIRIMAN & EKSPEDISI -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">local_shipping</span>
                    <h4 class="text-sm font-bold text-on-surface">Data Penerima & Pengiriman</h4>
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

                    @if($order->kurir || $order->nomor_resi)
                        <div>
                            <span class="text-outline text-[11px] block">Ekspedisi / Kurir:</span>
                            <p class="font-bold text-on-surface mt-0.5">{{ $order->kurir ?: '-' }}</p>
                        </div>
                        <div>
                            <span class="text-outline text-[11px] block">Nomor Resi:</span>
                            <p class="font-mono font-bold text-primary mt-0.5">{{ $order->nomor_resi ?: '-' }}</p>
                        </div>
                    @endif

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

            <!-- RIWAYAT PELACAKAN STATUS PESANAN (FRI-05) -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-container pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">history</span>
                        <h4 class="text-sm font-bold text-on-surface">Riwayat Perjalanan & Status Pesanan</h4>
                    </div>
                    <span class="text-xs text-outline">{{ $order->histories->count() }} catatan</span>
                </div>

                <div class="relative pl-6 space-y-4 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-surface-container">
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
                            <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-surface-container-lowest border-2 border-primary flex items-center justify-center">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary"></div>
                            </div>

                            <div class="bg-surface-container-low/60 rounded-xl p-3 border border-surface-container text-xs space-y-1">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $histBadge }}">
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
                        <p class="text-xs text-outline italic">Belum ada riwayat status tercatat untuk pesanan ini.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: BUKTI PEMBAYARAN & TINDAKAN ADMIN (5 kolom) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- FORM PERBARUI STATUS PESANAN (FRI-05) -->
            @php
                $nextAllowed = $order->getNextAllowedStatuses();
            @endphp
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">update</span>
                    <h4 class="text-sm font-bold text-on-surface">Perbarui Status Pesanan</h4>
                </div>

                @if(!empty($nextAllowed))
                    <form action="{{ route('admin.pesanan.status', $order) }}" 
                          method="POST" 
                          x-data="{ selectedStatus: '{{ old('status', $nextAllowed[0]) }}' }"
                          class="space-y-3.5 text-xs">
                        @csrf
                        @method('PATCH')

                        <!-- Pilih Status Berikutnya yang Sah -->
                        <div>
                            <label class="block text-[11px] font-semibold text-on-surface mb-1">
                                Pilih Status Baru *
                            </label>
                            <select name="status" 
                                    x-model="selectedStatus" 
                                    required 
                                    class="w-full h-10 px-3 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 font-medium">
                                @foreach($nextAllowed as $st)
                                    <option value="{{ $st }}" {{ old('status', $nextAllowed[0]) === $st ? 'selected' : '' }}>
                                        {{ \App\Models\Order::STATUS_LABELS[$st] ?? $st }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="text-error text-[11px]">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Input Kurir & Resi (Muncul Jika Memilih 'dikirim') -->
                        <div x-show="selectedStatus === '{{ \App\Models\Order::STATUS_DIKIRIM }}'" 
                             x-transition 
                             class="space-y-3 p-3.5 rounded-xl bg-surface-container-low border border-surface-container">
                            <div class="font-bold text-[11px] text-on-surface flex items-center gap-1.5 text-primary">
                                <span class="material-symbols-outlined text-base">local_shipping</span>
                                <span>Informasi Ekspedisi Pengiriman *</span>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-outline mb-1">Nama Kurir / Ekspedisi *</label>
                                <input type="text" 
                                       name="kurir" 
                                       value="{{ old('kurir', $order->kurir) }}" 
                                       placeholder="Contoh: JNE, J&T, SiCepat..." 
                                       class="w-full h-9 px-3 bg-surface-container-lowest text-on-surface text-xs rounded-lg focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                @error('kurir')
                                    <span class="text-error text-[10px]">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-outline mb-1">Nomor Resi Pengiriman *</label>
                                <input type="text" 
                                       name="nomor_resi" 
                                       value="{{ old('nomor_resi', $order->nomor_resi) }}" 
                                       placeholder="Contoh: JNE1234567890..." 
                                       class="w-full h-9 px-3 bg-surface-container-lowest text-on-surface text-xs rounded-lg focus:outline-none focus:ring-2 focus:ring-primary border-0 font-mono" />
                                @error('nomor_resi')
                                    <span class="text-error text-[10px]">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Catatan Opsional -->
                        <div>
                            <label class="block text-[11px] font-semibold text-on-surface mb-1">
                                Catatan Perubahan Status (Opsional)
                            </label>
                            <textarea name="catatan" 
                                      rows="2" 
                                      placeholder="Tuliskan catatan tambahan jika ada..." 
                                      class="w-full p-2.5 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <span class="text-error text-[11px]">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" 
                                class="w-full h-10 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Perubahan Status</span>
                        </button>
                    </form>
                @else
                    <div class="p-3.5 rounded-xl bg-surface-container-low text-on-surface-variant text-xs">
                        <span class="font-semibold block text-on-surface">Status Final</span>
                        <p class="text-[11px] text-outline mt-0.5">Pesanan ini telah mencapai status <strong>{{ $order->status_label }}</strong> dan tidak memiliki transisi lanjutan.</p>
                    </div>
                @endif
            </div>

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
            @if($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI)
                <div class="bg-surface-container-lowest rounded-2xl border border-surface-container shadow-xs p-5 space-y-4">
                    <h4 class="text-sm font-bold text-on-surface border-b border-surface-container pb-3">
                        Tindakan Verifikasi Pembayaran
                    </h4>

                    <div class="space-y-4">
                        <!-- 1. Tombol Verifikasi & Proses -->
                        <form action="{{ route('admin.pesanan.verify', $order) }}" 
                              method="POST" 
                              onsubmit="return confirm('Verifikasi pembayaran pesanan ini? Status pesanan akan diubah menjadi Diproses dan transaksi pemasukan akan dicatat ke pembukuan.');">
                            @csrf
                            <button type="submit" 
                                    class="w-full h-11 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
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
                                    class="w-full h-10 rounded-xl bg-surface-container-low text-rose-700 text-xs font-bold hover:bg-rose-50 transition-colors flex items-center justify-center gap-1.5 border border-rose-200 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">cancel</span>
                                <span>Tolak Bukti Pembayaran</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
