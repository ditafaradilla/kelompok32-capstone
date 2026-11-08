
@extends('Admin.keuangan.layout')
@section('title', 'Analisis Bazar & Produk')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Transaction::rupiah($n);
    $totalChannel = max(1, array_sum($channel));
    $best = $events->first();
    $maxBar = max(1, $events->max('income') ?? 0, $events->max('expense') ?? 0);
@endphp

<div class="flex flex-col gap-6">
    <div class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container">
        <h1 class="text-headline-md">Analisis Kinerja Bazar &amp; Produk</h1>
        <p class="text-on-surface-variant">
            Bazar dikelompokkan dari kolom <b>Nama bazar</b> di Pembukuan.
            Laba bazar = penjualan lunas − seluruh pengeluaran bazar
            (termasuk yang belum dibayar).
        </p>
    </div>

    {{-- Filter periode --}}
    @include('Admin.BI.period', [
        'action' => route('admin.bi.bazar'),
        'from' => $from,
        'to' => $to,
        'presets' => $presets
    ])

    {{-- Perbandingan saluran --}}
    <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
        <div class="text-title-md mb-4">Perbandingan penjualan per saluran</div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($channel as $name => $total)
                <div class="p-4 rounded-xl {{ $name === 'Penjualan Bazar' ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-surface-container-low' }}">
                    <div class="text-label-sm uppercase">{{ $name }}</div>
                    <div class="text-headline-sm mt-1">{{ $rp($total) }}</div>
                    <div class="text-label-sm mt-1">
                        {{ round($total / $totalChannel * 100) }}% dari total penjualan
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Kinerja per bazar --}}
    @if ($events->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-10 text-center text-on-surface-variant">
            Belum ada transaksi yang memiliki <b>Nama bazar</b> pada periode ini.
            Isi kolom "Nama bazar" saat mencatat penjualan dan biaya bazar di Pembukuan.
        </div>
    @else
        @if ($best && $best->net > 0)
            <div class="rounded-xl bg-secondary-container text-on-secondary-container px-4 py-3 text-label-md">
                Bazar paling menguntungkan:
                <b>{{ $best->name }}</b> dengan laba {{ $rp($best->net) }}
                {{ $best->roi !== null ? '(ROI ' . $best->roi . '%)' : '' }}.
            </div>
        @endif

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container overflow-x-auto">
            <div class="px-5 py-4 text-title-md">Kinerja per bazar</div>

            <table class="w-full text-body-md">
                <thead class="bg-surface-container-low text-left text-label-sm uppercase text-outline">
                    <tr>
                        <th class="px-4 py-3">Bazar</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 w-1/4">Penjualan vs pengeluaran</th>
                        <th class="px-4 py-3 text-right">Penjualan</th>
                        <th class="px-4 py-3 text-right">Pengeluaran</th>
                        <th class="px-4 py-3 text-right">Laba / rugi</th>
                        <th class="px-4 py-3 text-right">ROI</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($events as $e)
                        <tr class="border-t border-surface-container">
                            <td class="px-4 py-3 font-semibold">
                                {{ $e->name }}
                                <div class="text-label-sm text-outline font-normal">
                                    {{ $e->trx }} transaksi
                                </div>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($e->start)->format('d/m/Y') }}
                                @if ($e->end !== $e->start)
                                    – {{ \Carbon\Carbon::parse($e->end)->format('d/m/Y') }}
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="h-2 rounded-full bg-secondary mb-1"
                                    style="width: {{ round($e->income / $maxBar * 100) }}%">
                                </div>
                                <div class="h-2 rounded-full bg-error"
                                    style="width: {{ round($e->expense / $maxBar * 100) }}%">
                                </div>
                            </td>

                            <td class="px-4 py-3 text-right text-secondary">
                                {{ $rp($e->income) }}
                            </td>

                            <td class="px-4 py-3 text-right text-error">
                                {{ $rp($e->expense) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold {{ $e->net >= 0 ? 'text-secondary' : 'text-error' }}">
                                {{ $rp($e->net) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ $e->roi !== null ? $e->roi . '%' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Penjualan bazar tanpa nama bazar --}}
    @if ($untagged > 0)
        <div class="rounded-xl bg-tertiary-container text-on-tertiary-container px-4 py-3 text-label-md">
            Ada penjualan bazar {{ $rp($untagged) }} yang belum diberi
            <b>Nama bazar</b>, sehingga belum masuk tabel di atas.
            Lengkapi lewat menu Pembukuan.
        </div>
    @endif

    {{-- Produk terlaris --}}
    @if ($topProducts !== null)
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
            <div class="text-title-md mb-1">10 produk terlaris pada periode ini</div>

            <p class="text-label-sm text-outline mb-4">
                Semua saluran penjualan. Produk per bazar membutuhkan kolom bazar/acara pada data pesanan.
            </p>

            @forelse ($topProducts as $i => $p)
                <div class="flex justify-between py-2 border-t border-surface-container text-body-md">
                    <span>{{ $i + 1 }}. {{ $p->name }}</span>
                    <span class="font-semibold">
                        {{ number_format($p->qty, 0, ',', '.') }} pcs · {{ $rp($p->revenue) }}
                    </span>
                </div>
            @empty
                <div class="text-on-surface-variant">
                    Belum ada penjualan produk pada periode ini.
                </div>
            @endforelse
        </div>
    @else
        @include('Admin.BI.need_data', [
            'title' => 'Produk terlaris & produk per bazar',
            'error' => $productError,
            'extra' => 'Untuk produk per bazar: pesanan perlu mencatat bazar/acara asalnya (mis. kolom nama bazar).'
        ])
    @endif
</div>
@endsection
