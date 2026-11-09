@extends('User.layout')

@section('title', 'Checkout Pesanan - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a href="{{ route('keranjang.index') }}" class="hover:text-primary transition-colors">Keranjang</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Checkout</span>
    </nav>

    <!-- Page Header -->
    <div class="border-b border-surface-container pb-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">Checkout Pesanan</h1>
        <p class="text-xs text-on-surface-variant mt-0.5">Lengkapi data pengiriman dan pilih metode pembayaran untuk memproses pesanan Anda.</p>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        @csrf

        <!-- Kolom Kiri: Formulir Pengiriman & Metode Pembayaran (8 kolom) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 1. Data Penerima & Alamat Pengiriman -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs space-y-5">
                <div class="flex items-center gap-2.5 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-primary text-xl">local_shipping</span>
                    <h2 class="text-base font-bold text-on-surface">Data Penerima & Alamat Pengiriman</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Penerima *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">person</span>
                            <input type="text" 
                                   name="nama_penerima" 
                                   value="{{ old('nama_penerima', $user->name) }}" 
                                   required 
                                   placeholder="Nama lengkap penerima..." 
                                   class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Nomor Telepon (WhatsApp) *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">call</span>
                            <input type="tel" 
                                   name="telepon_penerima" 
                                   value="{{ old('telepon_penerima', $user->phone) }}" 
                                   required 
                                   placeholder="Contoh: 081234567890" 
                                   class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Alamat Lengkap Pengiriman *</label>
                    <div class="relative flex items-start">
                        <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">home</span>
                        <textarea name="alamat_pengiriman" 
                                  rows="3" 
                                  required 
                                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, kode pos..." 
                                  class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 resize-none">{{ old('alamat_pengiriman', $user->address) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Catatan Pesanan (Opsional)</label>
                    <div class="relative flex items-start">
                        <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">edit_note</span>
                        <textarea name="catatan" 
                                  rows="2" 
                                  placeholder="Instruksi khusus kepada kurir atau catatan pesanan..." 
                                  class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 resize-none">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 2. Pilihan Metode Pembayaran Manual -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-xs space-y-5" x-data="{ method: '{{ old('metode_pembayaran', 'transfer_bank') }}' }">
                <div class="flex items-center gap-2.5 border-b border-surface-container pb-3">
                    <span class="material-symbols-outlined text-secondary text-xl">payments</span>
                    <h2 class="text-base font-bold text-on-surface">Pilih Metode Pembayaran Manual</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Opsi 1: Transfer Bank -->
                    <label class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3 select-none"
                           :class="method === 'transfer_bank' ? 'border-primary bg-primary/5 shadow-xs' : 'border-surface-container bg-surface-container-low hover:bg-surface-container'">
                        <input type="radio" 
                               name="metode_pembayaran" 
                               value="transfer_bank" 
                               x-model="method" 
                               class="mt-1 text-primary focus:ring-primary" />
                        <div class="space-y-1">
                            <span class="font-bold text-xs text-on-surface block">Transfer Bank Manual</span>
                            <p class="text-[11px] text-on-surface-variant">Transfer ke rekening BCA toko, lalu unggah bukti transfer.</p>
                        </div>
                    </label>

                    <!-- Opsi 2: QRIS -->
                    <label class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3 select-none"
                           :class="method === 'qris' ? 'border-primary bg-primary/5 shadow-xs' : 'border-surface-container bg-surface-container-low hover:bg-surface-container'">
                        <input type="radio" 
                               name="metode_pembayaran" 
                               value="qris" 
                               x-model="method" 
                               class="mt-1 text-primary focus:ring-primary" />
                        <div class="space-y-1">
                            <span class="font-bold text-xs text-on-surface block">QRIS (Semua E-Wallet & M-Banking)</span>
                            <p class="text-[11px] text-on-surface-variant">Pindai kode QRIS dengan GoPay, OVO, Dana, BCA, dll.</p>
                        </div>
                    </label>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Ringkasan Pesanan & Tombol Buat Pesanan (4 kolom) -->
        <div class="lg:col-span-4">
            <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-7 border border-surface-container shadow-sm space-y-6 sticky top-28">
                
                <h3 class="text-base font-bold text-on-surface border-b border-surface-container pb-3">
                    Ringkasan Pesanan
                </h3>

                <!-- Daftar Item -->
                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    @foreach($summary['items'] as $item)
                        @php
                            $variant = $item->variant;
                            $product = $variant?->product;
                        @endphp
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <div class="flex-1 min-w-0">
                                <span class="font-bold text-on-surface line-clamp-1">{{ $product?->name }}</span>
                                <span class="text-[11px] text-outline block">{{ $variant?->ukuran }} / {{ $variant?->warna }} • {{ $item->jumlah }} pcs</span>
                            </div>
                            <span class="font-semibold text-on-surface shrink-0">{{ rupiah($item->subtotal) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2.5 text-xs border-t border-surface-container pt-3">
                    <div class="flex items-center justify-between text-on-surface-variant">
                        <span>Total Item</span>
                        <span class="font-semibold text-on-surface">{{ $summary['jumlah_item'] }} pcs</span>
                    </div>

                    <div class="flex items-center justify-between text-on-surface-variant">
                        <span>Ongkos Kirim</span>
                        <span class="font-semibold text-green-700">Gratis (Promo)</span>
                    </div>

                    <div class="pt-2 border-t border-surface-container flex items-baseline justify-between">
                        <span class="text-sm font-bold text-on-surface">Total Pembayaran</span>
                        <span class="text-xl font-extrabold text-primary">
                            {{ rupiah($summary['total_price']) }}
                        </span>
                    </div>
                </div>

                <!-- Tombol Submit Buat Pesanan -->
                <button type="submit" 
                        class="w-full h-12 rounded-2xl bg-primary hover:bg-surface-tint text-on-primary font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>Buat Pesanan Sekarang</span>
                </button>

                <p class="text-[11px] text-on-surface-variant text-center leading-relaxed">
                    Dengan membuat pesanan, stok produk di toko akan langsung dialokasikan untuk Anda.
                </p>

            </div>
        </div>

    </form>

</div>
@endsection

