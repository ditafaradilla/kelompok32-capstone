@extends('User.layout')

@section('title', 'Panduan Ukuran Pakaian Bayi - Baby Corner')

@section('content')
<div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-8 pb-16">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 text-xs text-outline pt-2">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-on-surface font-semibold">Panduan Ukuran</span>
    </nav>

    <!-- Page Header -->
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-[11px] font-semibold">
            <span class="material-symbols-outlined text-xs">straighten</span>
            <span>Standar Ukuran Baby Corner Organik</span>
        </div>
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-on-surface tracking-tight">
            Panduan dan Rekomendasi Ukuran Bayi
        </h1>
        <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">
            Gunakan tabel standar atau kalkulator cepat di bawah ini untuk menemukan ukuran pakaian yang paling pas, nyaman, dan tidak cepat sempit bagi si kecil.
        </p>
    </div>

    <!-- BAGIAN 1: KALKULATOR CEPAT REKOMENDASI UKURAN -->
    <div id="kalkulator" class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-sm space-y-6">
        <div class="flex items-center gap-2 pb-3 border-b border-surface-container">
            <span class="material-symbols-outlined text-primary text-xl">calculate</span>
            <div>
                <h2 class="text-sm sm:text-base font-bold text-on-surface">Kalkulator Cepat Rekomendasi Ukuran</h2>
                <p class="text-[11px] text-on-surface-variant">Cukup masukkan usia, berat, dan tinggi si kecil tanpa harus login atau menyimpan data profil.</p>
            </div>
        </div>

        <form action="{{ route('panduan.ukuran') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Usia -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Usia Bayi (Bulan) <span class="text-error">*</span></label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-outline text-sm">calendar_month</span>
                        <input type="number" name="usia" value="{{ request('usia') }}" min="0" max="60" required placeholder="Contoh: 4" class="w-full h-11 pl-9 pr-14 bg-surface-container-low text-xs rounded-xl border border-surface-container text-on-surface focus:ring-2 focus:ring-primary outline-none" />
                        <span class="absolute right-3 text-xs text-outline font-medium">Bulan</span>
                    </div>
                </div>

                <!-- Berat Badan -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-on-surface">Berat Badan</label>
                        <span class="text-[10px] text-outline">Opsional</span>
                    </div>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-outline text-sm">monitor_weight</span>
                        <input type="number" step="0.1" name="berat" value="{{ request('berat') }}" min="1" max="30" placeholder="Contoh: 6.5" class="w-full h-11 pl-9 pr-12 bg-surface-container-low text-xs rounded-xl border border-surface-container text-on-surface focus:ring-2 focus:ring-primary outline-none" />
                        <span class="absolute right-3 text-xs text-outline font-medium">kg</span>
                    </div>
                </div>

                <!-- Tinggi Badan -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-on-surface">Tinggi Badan</label>
                        <span class="text-[10px] text-outline">Opsional</span>
                    </div>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-outline text-sm">height</span>
                        <input type="number" step="0.5" name="tinggi" value="{{ request('tinggi') }}" min="30" max="130" placeholder="Contoh: 63" class="w-full h-11 pl-9 pr-12 bg-surface-container-low text-xs rounded-xl border border-surface-container text-on-surface focus:ring-2 focus:ring-primary outline-none" />
                        <span class="absolute right-3 text-xs text-outline font-medium">cm</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                @if(request()->filled('usia'))
                    <a href="{{ route('panduan.ukuran') }}" class="px-5 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold flex items-center justify-center transition-all">
                        Reset
                    </a>
                @endif
                <button type="submit" class="px-6 h-10 rounded-xl bg-primary hover:bg-surface-tint text-on-primary text-xs font-semibold flex items-center justify-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm">search</span>
                    <span>Hitung Rekomendasi Ukuran</span>
                </button>
            </div>
        </form>

        <!-- Hasil Rekomendasi Kalkulator Cepat -->
        @if($calcResult)
            <div class="pt-4 border-t border-surface-container">
                @if($calcResult['status'] === 'di_luar_rentang')
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-amber-600">info</span>
                            <span>Ukuran di Luar Rentang Panduan</span>
                        </div>
                        <p class="text-amber-800 leading-relaxed">{{ $calcResult['alasan'] }}</p>
                    </div>
                @else
                    <div class="p-5 rounded-2xl bg-primary/5 border border-primary/20 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <span class="text-[10px] uppercase font-bold text-primary tracking-wider">Hasil Rekomendasi Ukuran:</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-extrabold text-primary">{{ $calcResult['ukuran_utama'] }}</span>
                                    <span class="px-2 py-0.5 rounded-full {{ $calcResult['status'] === 'batas' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }} text-[10px] font-bold">
                                        {{ $calcResult['status'] === 'batas' ? 'Naik 1 Ukuran' : 'Ukuran Pas' }}
                                    </span>
                                </div>
                            </div>

                            @if($calcResult['alternatif'])
                                <div class="p-3 rounded-xl bg-white border border-surface-container text-xs">
                                    <span class="text-outline text-[10px] block">Ukuran Alternatif (Lebih Longgar):</span>
                                    <span class="font-bold text-on-surface">{{ $calcResult['alternatif'] }}</span>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-on-surface leading-relaxed">
                            {{ $calcResult['alasan'] }}
                        </p>

                        <div class="pt-2">
                            <a href="{{ route('produk.index', ['size' => $calcResult['ukuran_utama']]) }}" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint inline-flex items-center gap-1.5 shadow-xs transition-all">
                                <span>Lihat Pakaian Ukuran {{ $calcResult['ukuran_utama'] }}</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- BAGIAN 2: TABEL PANDUAN UKURAN LENGKAP -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 border border-surface-container shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-surface-container">
            <span class="material-symbols-outlined text-secondary text-xl">table_chart</span>
            <div>
                <h2 class="text-sm sm:text-base font-bold text-on-surface">Tabel Acuan Standar Ukuran Baju Bayi</h2>
                <p class="text-[11px] text-on-surface-variant">Rujukan resmi spesifikasi konveksi pakaian Baby Corner</p>
            </div>
        </div>

        <div class="overflow-x-auto border border-surface-container rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-outline font-semibold border-b border-surface-container">
                        <th class="py-3.5 px-4 font-bold text-on-surface">Label Ukuran</th>
                        <th class="py-3.5 px-4">Rentang Usia</th>
                        <th class="py-3.5 px-4">Rentang Berat Badan</th>
                        <th class="py-3.5 px-4">Rentang Panjang Badan</th>
                        <th class="py-3.5 px-4 text-center">Koleksi Produk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @foreach($sizeGuides as $guide)
                        <tr class="hover:bg-surface-container-low/40 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-sm text-primary">
                                {{ $guide->ukuran }}
                            </td>
                            <td class="py-3.5 px-4 text-on-surface">
                                {{ $guide->usia_min_bulan }} - {{ $guide->usia_maks_bulan }} Bulan
                            </td>
                            <td class="py-3.5 px-4 text-on-surface">
                                {{ (float)$guide->berat_min }} - {{ (float)$guide->berat_maks }} kg
                            </td>
                            <td class="py-3.5 px-4 text-on-surface">
                                {{ (float)$guide->tinggi_min }} - {{ (float)$guide->tinggi_maks }} cm
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('produk.index', ['size' => $guide->ukuran]) }}" class="px-3 py-1 rounded-lg bg-surface-container hover:bg-primary hover:text-white text-[11px] font-semibold text-primary transition-all inline-flex items-center gap-1">
                                    <span>Lihat Produk</span>
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tips Pengukuran -->
        <div class="p-4 rounded-2xl bg-surface-container-low text-xs space-y-2 mt-4 text-on-surface-variant">
            <h4 class="font-bold text-on-surface text-xs flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-sm">lightbulb</span>
                <span>Tips Memilih Ukuran yang Nyaman:</span>
            </h4>
            <ul class="list-disc list-inside space-y-1 text-[11px] leading-relaxed">
                <li>Jika ukuran si kecil berada di ambang batas maksimum berat atau panjang badan, kami menyarankan untuk memilih <strong>satu ukuran lebih besar</strong> agar pakaian tahan lebih lama mengingat bayi bertumbuh sangat cepat.</li>
                <li>Katun organik kami telah melalui proses <em>pre-washed</em> untuk meminimalkan penyusutan setelah dicuci.</li>
            </ul>
        </div>
    </div>

</div>
@endsection
