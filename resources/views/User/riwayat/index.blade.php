@extends('User.layout')

@section('title', 'Riwayat Pembelian - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-6 pb-16">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Riwayat Pembelian</span>
    </nav>

    <!-- Header & Judul Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">Riwayat Pembelian</h1>
            <p class="text-xs text-on-surface-variant mt-1">
                Pantau status pesanan, pembayaran, dan lacak pengiriman produk pakaian bayi Anda.
            </p>
        </div>
        <div>
            <a href="{{ route('produk.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-base">shopping_bag</span>
                <span>Belanja Produk Baru</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Kecil -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Seluruh Pesanan -->
        <div class="p-5 rounded-3xl bg-surface-container-lowest border border-surface-container shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-2xl">receipt_long</span>
            </div>
            <div>
                <span class="text-xs text-outline block font-medium">Total Pesanan</span>
                <span class="text-xl font-extrabold text-on-surface">{{ $totalPesanan }}</span>
                <span class="text-xs text-on-surface-variant block mt-0.5">Transaksi dilakukan</span>
            </div>
        </div>

        <!-- Total Belanja (Hanya diproses, dikirim, selesai) -->
        <div class="p-5 rounded-3xl bg-surface-container-lowest border border-surface-container shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-800 shrink-0">
                <span class="material-symbols-outlined text-2xl">savings</span>
            </div>
            <div>
                <span class="text-xs text-outline block font-medium">Total Belanja Berhasil</span>
                <span class="text-xl font-extrabold text-emerald-800">{{ rupiah($totalBelanja) }}</span>
                <span class="text-[11px] text-outline block mt-0.5">Pesanan aktif &amp; selesai</span>
            </div>
        </div>

        <!-- Menunggu Pembayaran -->
        <div class="p-5 rounded-3xl bg-surface-container-lowest border border-surface-container shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-800 shrink-0">
                <span class="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
            <div>
                <span class="text-xs text-outline block font-medium">Perlu Pembayaran</span>
                <span class="text-xl font-extrabold text-amber-800">{{ $pesananMenunggu }}</span>
                <span class="text-[11px] text-outline block mt-0.5">Menunggu konfirmasi bayar</span>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-surface-container-lowest rounded-3xl p-5 sm:p-6 border border-surface-container shadow-xs space-y-4">
        
        <!-- Status Tabs Cepat -->
        @php
            $currentStatus = request('status', 'semua');
            $statusTabs = [
                'semua'                => 'Semua',
                \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
                \App\Models\Order::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
                \App\Models\Order::STATUS_DIPROSES             => 'Diproses',
                \App\Models\Order::STATUS_DIKIRIM              => 'Dikirim',
                \App\Models\Order::STATUS_SELESAI              => 'Selesai',
                \App\Models\Order::STATUS_DIBATALKAN           => 'Dibatalkan',
            ];
        @endphp

        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs">
            @foreach($statusTabs as $key => $label)
                @php
                    $isActive = ($currentStatus === $key);
                    $queryParam = array_merge(request()->query(), ['status' => $key, 'page' => 1]);
                    if ($key === 'semua') unset($queryParam['status']);
                @endphp
                <a href="{{ route('riwayat.index', $queryParam) }}" 
                   class="px-4 py-2 rounded-full whitespace-nowrap font-semibold transition-all shrink-0 {{ $isActive ? 'bg-primary text-white shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Form Filter & Pencarian Lengkap -->
        <form action="{{ route('riwayat.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-2 border-t border-surface-container items-end text-xs">
            
            <!-- Pertahankan status tab jika ada -->
            @if(request('status') && request('status') !== 'semua')
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Pencarian Nomor Pesanan -->
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-outline mb-1">Cari Nomor Pesanan</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-outline text-base">search</span>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q', request('search')) }}" 
                           placeholder="Contoh: BC-2026..." 
                           class="w-full h-10 pl-9 pr-3 rounded-xl bg-surface-container-low text-on-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                </div>
            </div>

            <!-- Rentang Tanggal Mulai -->
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-outline mb-1">Dari Tanggal</label>
                <input type="date" 
                       name="tanggal_mulai" 
                       value="{{ request('tanggal_mulai', request('dari_tanggal')) }}" 
                       class="w-full h-10 px-3 rounded-xl bg-surface-container-low text-on-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary border-0" />
            </div>

            <!-- Rentang Tanggal Akhir -->
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-outline mb-1">Sampai Tanggal</label>
                <input type="date" 
                       name="tanggal_akhir" 
                       value="{{ request('tanggal_akhir', request('sampai_tanggal')) }}" 
                       class="w-full h-10 px-3 rounded-xl bg-surface-container-low text-on-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary border-0" />
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 h-10 rounded-xl bg-primary text-white font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                    <span>Terapkan</span>
                </button>
                @if(request()->hasAny(['q', 'search', 'status', 'tanggal_mulai', 'dari_tanggal', 'tanggal_akhir', 'sampai_tanggal']))
                    <a href="{{ route('riwayat.index') }}" 
                       title="Reset Filter"
                       class="h-10 w-10 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-error hover:bg-rose-50 transition-colors flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-base">refresh</span>
                    </a>
                @endif
            </div>

        </form>

    </div>

    <!-- Daftar Kartu Pesanan -->
    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
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

                    $totalItems = $order->items->count();
                    $displayItems = $order->items->take(2);
                @endphp

                <div class="bg-surface-container-lowest rounded-3xl p-5 sm:p-6 border border-surface-container shadow-xs space-y-4 hover:shadow-sm transition-all">
                    
                    <!-- Kartu Header: Nomor Pesanan, Tanggal, dan Status Badge -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-surface-container">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="font-mono font-bold text-xs sm:text-sm text-on-surface">
                                #{{ $order->nomor_pesanan }}
                            </span>
                            <span class="text-xs text-outline">•</span>
                            <span class="text-xs text-on-surface-variant">
                                {{ $order->tanggal_pesanan->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Badge Status Pesanan -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>{{ $order->status_label }}</span>
                            </span>

                            <!-- Badge Status Pembayaran -->
                            @if($order->payment)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $order->payment->status_pembayaran === \App\Models\Payment::STATUS_LUNAS ? 'bg-green-50 text-green-700 border border-green-200' : ($order->payment->status_pembayaran === \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($order->payment->status_pembayaran === \App\Models\Payment::STATUS_DITOLAK ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                                    <span>{{ $order->payment->metode_label }}</span>:
                                    <span>{{ $order->payment->status_label }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Item Ringkas (2-3 Produk Pertama) -->
                    <div class="space-y-3">
                        @foreach($displayItems as $item)
                            @php
                                $variant = $item->variant;
                                $product = $item->product ?: $variant?->product;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-xl bg-surface-container overflow-hidden shrink-0 border border-surface-container flex items-center justify-center">
                                    @if($product && $product->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->photo))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($product->photo) }}" 
                                             alt="{{ $item->nama_produk }}" 
                                             class="w-full h-full object-cover" />
                                    @else
                                        <span class="material-symbols-outlined text-outline text-xl">checkroom</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-xs sm:text-sm text-on-surface truncate">{{ $item->nama_produk }}</h4>
                                    <p class="text-[11px] text-outline mt-0.5">
                                        Varian: <span class="text-on-surface-variant font-medium">{{ $item->varian_label }}</span>
                                        • {{ $item->jumlah }} pcs × {{ rupiah($item->harga_satuan) }}
                                    </p>
                                </div>
                                <div class="text-right text-xs font-bold text-on-surface shrink-0 flex flex-col items-end gap-1">
                                    <span>{{ rupiah($item->subtotal) }}</span>
                                    @if($order->status_pesanan === \App\Models\Order::STATUS_SELESAI && $product)
                                        @php
                                            $itemRev = \App\Models\Review::where('user_id', auth()->id())
                                                ->where('product_id', $product->id)
                                                ->first();
                                        @endphp
                                        @if($itemRev)
                                            <a href="{{ route('ulasan.edit', $itemRev) }}" class="text-[11px] font-bold text-primary hover:underline flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-xs">edit_note</span>
                                                <span>Ubah Ulasan</span>
                                            </a>
                                        @else
                                            <a href="{{ route('ulasan.create', $product) }}" class="text-[11px] font-bold text-amber-600 hover:text-amber-700 hover:underline flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-xs">star</span>
                                                <span>Beri Ulasan</span>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($totalItems > 2)
                            <div class="text-[11px] text-outline font-medium pt-1">
                                +{{ $totalItems - 2 }} produk lainnya dalam pesanan ini
                            </div>
                        @endif
                    </div>

                    <!-- Footer Kartu: Total Belanja & Tombol Aksi -->
                    <div class="pt-3 border-t border-surface-container flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <span class="text-outline text-[11px] block">Total Belanja:</span>
                            <span class="text-base sm:text-lg font-extrabold text-primary">
                                {{ rupiah($order->total_harga) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Tombol Bayar Sekarang (Khusus Menunggu Pembayaran) -->
                            @if($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN)
                                <a href="{{ route('pesanan.pembayaran', $order) }}" 
                                   class="px-4 py-2 rounded-xl bg-primary text-white font-bold text-xs hover:bg-primary/90 transition-all flex items-center gap-1.5 shadow-xs">
                                    <span class="material-symbols-outlined text-sm">payments</span>
                                    <span>Bayar Sekarang</span>
                                </a>
                            @endif

                            <!-- Tombol Lacak Pesanan -->
                            <a href="{{ route('pesanan.lacak', $order) }}" 
                               class="px-3.5 py-2 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors font-semibold flex items-center gap-1.5 border border-surface-container">
                                <span class="material-symbols-outlined text-sm">route</span>
                                <span>Lacak</span>
                            </a>

                            <!-- Tombol Lihat Detail -->
                            <a href="{{ route('pesanan.show', $order) }}" 
                               class="px-3.5 py-2 rounded-xl bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                <span>Lihat Detail</span>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-4">
                {{ $orders->links() }}
            </div>
        </div>
    @else
        <!-- State Kosong: Belum ada pembelian -->
        <div class="bg-surface-container-lowest rounded-3xl p-10 sm:p-14 border border-surface-container text-center max-w-lg mx-auto shadow-xs space-y-4 my-6">
            <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center text-outline mx-auto">
                <span class="material-symbols-outlined text-4xl text-primary">receipt_long</span>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-on-surface">Belum ada pembelian</h3>
                <p class="text-xs text-on-surface-variant max-w-sm mx-auto leading-relaxed">
                    @if(request()->hasAny(['status', 'q', 'search', 'tanggal_mulai', 'tanggal_akhir']))
                        Tidak ditemukan pesanan yang sesuai dengan kriteria filter atau pencarian Anda.
                    @else
                        Anda belum pernah melakukan pemesanan pakaian bayi. Yuk, temukan pakaian lucu dan nyaman untuk si kecil!
                    @endif
                </p>
            </div>
            <div class="pt-2">
                @if(request()->hasAny(['status', 'q', 'search', 'tanggal_mulai', 'tanggal_akhir']))
                    <a href="{{ route('riwayat.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-surface-container text-on-surface text-xs font-bold hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                        <span>Reset Filter</span>
                    </a>
                @else
                    <a href="{{ route('produk.index') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-base">storefront</span>
                        <span>Mulai Belanja di Katalog</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection

