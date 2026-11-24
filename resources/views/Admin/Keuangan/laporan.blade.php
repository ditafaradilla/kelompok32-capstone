@extends('Admin.keuangan.layout')
@section('title', 'Laporan Keuangan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Transaction::rupiah($n);
    $field = 'h-10 px-3 rounded-xl bg-surface-container-low text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40';
    $max = max(1, $timeline->max('income') ?? 0, $timeline->max('expense') ?? 0);
    $totalIncomeCat = max(1, $incomeCats->sum('total'));
    $totalExpenseCat = max(1, $expenseCats->sum('total'));
@endphp

<div class="flex flex-col gap-6">
    <div class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container">
        <h1 class="text-headline-md">Laporan Keuangan</h1>
        <p class="text-on-surface-variant">Ringkasan pemasukan, pengeluaran, dan selisih berdasarkan periode. Hanya transaksi <strong>lunas</strong> yang dihitung sebagai uang masuk/keluar.</p>
    </div>

    {{-- Pilih periode --}}
    <form method="GET" action="{{ route('admin.keuangan.laporan') }}"
          class="flex flex-wrap items-end gap-3 bg-surface-container-lowest p-4 rounded-xl border border-surface-container">
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Dari
            <input type="date" name="from" value="{{ $from }}" class="{{ $field }}">
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Sampai
            <input type="date" name="to" value="{{ $to }}" class="{{ $field }}">
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Rincian
            <select name="group" class="{{ $field }}">
                <option value="harian" @selected($group === 'harian')>Per hari</option>
                <option value="bulanan" @selected($group === 'bulanan')>Per bulan</option>
            </select>
        </label>
        <button class="px-5 h-10 rounded-full bg-primary text-on-primary text-label-md hover:opacity-90">Tampilkan</button>
        <div class="flex gap-2 ml-auto">
            @foreach ($presets as $name => $p)
                <a href="{{ route('admin.keuangan.laporan', ['from' => $p['from'], 'to' => $p['to'], 'group' => $name === 'Tahun ini' ? 'bulanan' : 'harian']) }}"
                   class="px-4 h-10 inline-flex items-center rounded-full bg-surface-container text-on-surface text-label-md hover:bg-surface-container-low">{{ $name }}</a>
            @endforeach
        </div>
    </form>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-label-md">{{ $errors->first() }}</div>
    @endif

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Total pemasukan</div>
            <div class="text-headline-sm text-secondary mt-1">{{ $rp($summary['income']) }}</div>
        </div>
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Total pengeluaran</div>
            <div class="text-headline-sm text-error mt-1">{{ $rp($summary['expense']) }}</div>
        </div>
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">{{ $summary['balance'] >= 0 ? 'Selisih (surplus)' : 'Selisih (defisit)' }}</div>
            <div class="text-headline-sm mt-1 {{ $summary['balance'] >= 0 ? 'text-secondary' : 'text-error' }}">{{ $rp($summary['balance']) }}</div>
        </div>
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Belum lunas (tidak masuk total)</div>
            <div class="text-body-md mt-1">Piutang: <strong>{{ $rp($summary['receivable']) }}</strong></div>
            <div class="text-body-md">Utang: <strong>{{ $rp($summary['payable']) }}</strong></div>
        </div>
    </div>

    {{-- Rincian per periode --}}
    <div class="bg-surface-container-lowest rounded-xl border border-surface-container overflow-x-auto">
        <div class="px-5 py-4 text-title-md">Rincian {{ $group === 'bulanan' ? 'per bulan' : 'per hari' }}</div>
        <table class="w-full text-body-md">
            <thead class="bg-surface-container-low text-left text-label-sm uppercase text-outline">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3 w-1/3">Pemasukan vs Pengeluaran</th>
                    <th class="px-4 py-3 text-right">Pemasukan</th>
                    <th class="px-4 py-3 text-right">Pengeluaran</th>
                    <th class="px-4 py-3 text-right">Selisih</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($timeline as $row)
                <tr class="border-t border-surface-container">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $row['label'] }}</td>
                    <td class="px-4 py-3">
                        <div class="h-2 rounded-full bg-secondary mb-1" style="width: {{ round($row['income'] / $max * 100) }}%"></div>
                        <div class="h-2 rounded-full bg-error" style="width: {{ round($row['expense'] / $max * 100) }}%"></div>
                    </td>
                    <td class="px-4 py-3 text-right text-secondary">{{ $rp($row['income']) }}</td>
                    <td class="px-4 py-3 text-right text-error">{{ $rp($row['expense']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold {{ $row['balance'] >= 0 ? 'text-secondary' : 'text-error' }}">{{ $rp($row['balance']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-on-surface-variant">Belum ada transaksi lunas pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Per kategori --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @foreach ([['Pemasukan per kategori', $incomeCats, $totalIncomeCat, 'bg-secondary'], ['Pengeluaran per kategori', $expenseCats, $totalExpenseCat, 'bg-error']] as [$title, $cats, $total, $bar])
            <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
                <div class="text-title-md mb-4">{{ $title }}</div>
                @forelse ($cats as $c)
                    <div class="mb-4">
                        <div class="flex justify-between text-body-md">
                            <span>{{ $c->category }}</span>
                            <span class="font-semibold">{{ $rp($c->total) }} <span class="text-outline font-normal">({{ round($c->total / $total * 100) }}%)</span></span>
                        </div>
                        <div class="h-2 rounded-full bg-surface-container mt-1">
                            <div class="h-2 rounded-full {{ $bar }}" style="width: {{ round($c->total / $total * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-on-surface-variant">Belum ada data.</div>
                @endforelse
            </div>
        @endforeach
    </div>
</div>
@endsection
