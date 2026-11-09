@extends('User.layout')

@section('title', 'Pembayaran Pesanan #' . $order->nomor_pesanan . ' - Baby Corner')

@section('content')
<div class="w-full max-w-[960px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('pesanan.show', $order) }}" class="hover:text-primary transition-colors">Pesanan #{{ $order->nomor_pesanan }}</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Instruksi Pembayaran</span>
    </nav>

    <!-- Header Status Pesanan -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono text-outline">Nomor Pesanan:</span>
                <span class="text-xs font-mono font-bold text-on-surface">#{{ $order->nomor_pesanan }}</span>
            </div>
            <h1 class="text-2xl font-bold text-on-surface tracking-tight mt-1">Selesaikan Pembayaran</h1>
            <p class="text-xs text-on-surface-variant mt-0.5">
                Batas waktu pembayaran: <strong>24 jam</strong> sejak pesanan dibuat ({{ $order->tanggal_pesanan->translatedFormat('d M Y, H:i') }} WIB).
            </p>
        </div>

        <div class="text-right">
            <span class="text-xs text-outline block">Total yang Harus Dibayar:</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-primary">
                {{ rupiah($order->total_harga) }}
            </span>
        </div>
    </div>

    <!-- Peringatan Jika Bukti Pembayaran Ditolak -->
    @if($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_DITOLAK)
        <div class="p-4 rounded-2xl bg-error-container border border-error text-error text-xs space-y-1 shadow-xs">
            <div class="font-bold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">error</span>
                <span>Bukti Pembayaran Ditolak oleh Admin</span>
            </div>
            <p class="text-error">Alasan: {{ $order->payment->catatan_admin ?: 'Bukti pembayaran tidak terbaca atau nominal tidak sesuai.' }}</p>
            <p class="text-[11px] opacity-90">Silakan unggah ulang bukti transfer atau pembayaran yang valid di bawah ini.</p>
        </div>
    @endif

    <!-- Status Menunggu Verifikasi -->
    @if($order->payment?->status_pembayaran === \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600 text-xl">hourglass_top</span>
                <div>
                    <span class="font-bold block">Bukti Pembayaran Sedang Diverifikasi</span>
                    <span>Admin kami sedang memeriksa bukti pembayaran Anda. Status akan diperbarui segera.</span>
                </div>
            </div>
            <a href="{{ route('pesanan.show', $order) }}" class="px-3.5 py-1.5 rounded-xl bg-amber-200 text-amber-900 font-bold text-xs hover:bg-amber-300 transition-colors shrink-0">
                Lihat Detail Pesanan
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        
        <!-- Kolom Kiri: Petunjuk Pembayaran Sesuai Metode (7 kolom) -->
        <div class="md:col-span-7 space-y-6">
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-7 border border-surface-container shadow-xs space-y-5">
                <div class="flex items-center gap-2 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
                    <h2 class="text-base font-bold text-on-surface">Petunjuk Pembayaran</h2>
                </div>

                @if($order->payment?->metode_pembayaran === \App\Models\Payment::METODE_TRANSFER)
                    <!-- Instruksi Transfer Bank -->
                    <div class="space-y-4">
                        <p class="text-xs text-on-surface-variant leading-relaxed">
                            Silakan lakukan transfer sejumlah nominal yang tertera ke rekening bank resmi Baby Corner:
                        </p>

                        <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-outline">Nama Bank:</span>
                                <span class="font-bold text-on-surface text-sm">{{ config('shop.bank_name', 'BCA') }}</span>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-outline">Nomor Rekening:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-primary text-sm sm:text-base">{{ config('shop.bank_account', '1234567890') }}</span>
                                    <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ config('shop.bank_account', '1234567890') }}'); alert('Nomor rekening disalin!');" 
                                            class="text-[11px] text-primary hover:underline font-semibold cursor-pointer">
                                        Salin
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-outline">Atas Nama:</span>
                                <span class="font-bold text-on-surface">{{ config('shop.bank_holder', 'Baby Corner Official') }}</span>
                            </div>
                        </div>

                        <ol class="list-decimal list-inside text-xs text-on-surface-variant space-y-1 pl-1">
                            <li>Buka aplikasi m-Banking atau ATM bank Anda.</li>
                            <li>Pilih transfer ke rekening bank di atas.</li>
                            <li>Masukkan nominal tepat <strong>{{ rupiah($order->total_harga) }}</strong>.</li>
                            <li>Simpan struk / tangkapan layar (screenshot) bukti transfer.</li>
                            <li>Unggah bukti pada formulir di samping.</li>
                        </ol>
                    </div>

                @else
                    <!-- Instruksi QRIS -->
                    <div class="space-y-4">
                        <p class="text-xs text-on-surface-variant leading-relaxed">
                            Pindai kode QRIS di bawah ini menggunakan aplikasi pembayaran pilihan Anda (GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, dll.):
                        </p>

                        <div class="flex flex-col items-center justify-center p-6 rounded-2xl bg-surface-container-low border border-surface-container space-y-3">
                            @if(file_exists(public_path('images/qris.png')))
                                <img src="{{ asset('images/qris.png') }}" alt="QRIS Baby Corner" class="w-48 h-48 object-contain rounded-xl border border-surface-container bg-white p-2" />
                            @else
                                <div class="w-48 h-48 rounded-xl bg-white border border-surface-container flex flex-col items-center justify-center text-center p-4">
                                    <span class="material-symbols-outlined text-6xl text-primary mb-1">qr_code_2</span>
                                    <span class="text-[11px] font-bold text-on-surface">QRIS BABY CORNER</span>
                                    <span class="text-[9px] text-outline">NMID: ID1020304050</span>
                                </div>
                            @endif

                            <div class="text-center space-y-0.5">
                                <span class="text-xs font-bold text-on-surface block">{{ config('shop.bank_holder', 'Baby Corner Official') }}</span>
                                <span class="text-[11px] text-outline">Nominal: {{ rupiah($order->total_harga) }}</span>
                            </div>
                        </div>

                        <ol class="list-decimal list-inside text-xs text-on-surface-variant space-y-1 pl-1">
                            <li>Buka aplikasi dompet digital atau mobile banking Anda.</li>
                            <li>Pilih menu <strong>Bayar / QRIS / Scan</strong>.</li>
                            <li>Pindai kode QR di atas lalu periksa nama penerima.</li>
                            <li>Selesaikan pembayaran dan simpan bukti tangkapan layar.</li>
                            <li>Unggah bukti pada formulir di samping.</li>
                        </ol>
                    </div>
                @endif
            </div>

            <!-- Tombol Batal Pesanan -->
            @if($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN)
                <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-on-surface block">Batalkan Pesanan?</span>
                        <span class="text-[11px] text-on-surface-variant">Stok akan segera dikembalikan ke toko.</span>
                    </div>
                    <form action="{{ route('pesanan.cancel', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl border border-error text-error hover:bg-error hover:text-white font-semibold text-xs transition-colors cursor-pointer">
                            Batalkan Pesanan
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Form Unggah Bukti Pembayaran (5 kolom) -->
        <div class="md:col-span-5 space-y-6">
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-7 border border-surface-container shadow-xs space-y-5">
                <div class="flex items-center gap-2 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-secondary text-xl">upload_file</span>
                    <h2 class="text-base font-bold text-on-surface">Unggah Bukti Bayar</h2>
                </div>

                <!-- Pratinjau Bukti Terunggah (Jika Ada) -->
                @if(!empty($order->payment?->bukti_bayar))
                    <div class="p-3 rounded-2xl bg-surface-container-low border border-surface-container space-y-2">
                        <span class="text-[11px] font-semibold text-outline block">Bukti Pembayaran Terakhir:</span>
                        @if(str_ends_with(strtolower($order->payment->bukti_bayar), '.pdf'))
                            <a href="{{ asset('storage/' . $order->payment->bukti_bayar) }}" target="_blank" class="flex items-center gap-2 text-xs font-bold text-primary hover:underline">
                                <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                                <span>Lihat Dokumen PDF Bukti</span>
                            </a>
                        @else
                            <a href="{{ asset('storage/' . $order->payment->bukti_bayar) }}" target="_blank" class="block aspect-video w-full rounded-xl overflow-hidden border border-surface-container bg-white">
                                <img src="{{ asset('storage/' . $order->payment->bukti_bayar) }}" alt="Bukti Pembayaran" class="w-full h-full object-contain" />
                            </a>
                        @endif
                        <span class="text-[10px] text-outline block">Diunggah pada: {{ $order->payment->tanggal_bayar ? $order->payment->tanggal_bayar->translatedFormat('d M Y, H:i') : '-' }}</span>
                    </div>
                @endif

                <!-- Form Upload -->
                @if($order->payment?->status_pembayaran !== \App\Models\Payment::STATUS_LUNAS)
                    <form action="{{ route('pesanan.uploadBukti', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">
                                {{ !empty($order->payment?->bukti_bayar) ? 'Ganti File Bukti Pembayaran' : 'Pilih File Bukti Pembayaran' }} *
                            </label>
                            <input type="file" 
                                   name="bukti_bayar" 
                                   required 
                                   accept="image/jpeg,image/png,image/webp,application/pdf"
                                   class="block w-full text-xs text-on-surface file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-on-primary hover:file:bg-surface-tint file:cursor-pointer cursor-pointer border border-surface-container rounded-xl bg-surface-container-low p-2" />
                            <p class="text-[10px] text-outline mt-1">Format: JPG, PNG, WebP, atau PDF (maks. 3 MB)</p>
                        </div>

                        <button type="submit" 
                                class="w-full h-11 rounded-2xl bg-secondary hover:bg-secondary/90 text-on-secondary font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-base">send</span>
                            <span>{{ !empty($order->payment?->bukti_bayar) ? 'Perbarui Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}</span>
                        </button>
                    </form>
                @else
                    <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-900 text-xs font-medium text-center">
                        Pembayaran telah diverifikasi lunas oleh admin.
                    </div>
                @endif

                <div class="pt-2 text-center">
                    <a href="{{ route('pesanan.show', $order) }}" class="text-xs text-primary font-semibold hover:underline">
                        Lihat Rincian Lengkap Pesanan
                    </a>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection

