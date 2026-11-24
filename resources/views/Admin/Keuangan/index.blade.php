@extends('Admin.keuangan.layout')
@section('title', 'Pembukuan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Transaction::rupiah($n);
    $field = 'h-10 px-3 rounded-xl bg-surface-container-low text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40';
@endphp

<div class="flex flex-col gap-6">

    {{-- Judul --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-container">
        <div>
            <h1 class="text-headline-md text-on-surface">Pembukuan</h1>
            <p class="text-on-surface-variant">Catat pemasukan, pengeluaran, dan status pembayaran toko.</p>
        </div>
        <a href="{{ route('admin.keuangan.create') }}"
           class="inline-flex items-center gap-2 px-5 h-10 rounded-full bg-primary-container text-on-primary text-label-md hover:bg-primary transition-all">
            <span class="material-symbols-outlined text-[18px]">add</span> Catat Transaksi
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.keuangan.index') }}"
          class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 bg-surface-container-lowest p-4 rounded-xl border border-surface-container">
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Dari
            <input type="date" name="from" value="{{ $from }}" class="{{ $field }}">
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Sampai
            <input type="date" name="to" value="{{ $to }}" class="{{ $field }}">
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Jenis
            <select name="type" class="{{ $field }}">
                <option value="">Semua</option>
                <option value="income" @selected(request('type') === 'income')>Pemasukan</option>
                <option value="expense" @selected(request('type') === 'expense')>Pengeluaran</option>
            </select>
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Status
            <select name="status" class="{{ $field }}">
                <option value="">Semua</option>
                <option value="lunas" @selected(request('status') === 'lunas')>Lunas</option>
                <option value="belum_lunas" @selected(request('status') === 'belum_lunas')>Belum lunas</option>
            </select>
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Kategori
            <select name="category" class="{{ $field }}">
                <option value="">Semua</option>
                @foreach ($allCategories as $c)
                    <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Cari
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Keterangan / nota / bazar" class="{{ $field }}">
        </label>
        <div class="md:col-span-3 lg:col-span-6 flex gap-2">
            <button class="px-5 h-10 rounded-full bg-primary text-on-primary text-label-md hover:opacity-90">Terapkan</button>
            <a href="{{ route('admin.keuangan.index') }}" class="px-5 h-10 inline-flex items-center rounded-full bg-surface-container text-on-surface text-label-md">Reset</a>
        </div>
    </form>

    {{-- Unduh data --}}
    <details class="bg-surface-container-lowest rounded-xl border border-surface-container" @if ($errors->has('export') || $errors->has('export_from') || $errors->has('export_to')) open @endif>
        <summary class="cursor-pointer list-none flex items-center gap-2 px-5 py-3 text-label-md text-primary">
            <span class="material-symbols-outlined text-[18px]">download</span> Unduh data (CSV / Excel)
        </summary>
        <form method="GET" action="{{ route('admin.keuangan.export') }}" id="exportForm" class="px-5 pb-5 flex flex-col gap-3">
            {{-- filter yang sedang tampil ikut dikirim untuk pilihan pertama --}}
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <input type="hidden" name="type" value="{{ request('type') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="q" value="{{ request('q') }}">

            <label class="flex items-center gap-2 text-body-md">
                <input type="radio" name="scope" value="filter" checked>
                Sesuai filter yang sedang tampil ({{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }})
            </label>
            <label class="flex items-center gap-2 text-body-md">
                <input type="radio" name="scope" value="all"> Semua data (tanpa filter)
            </label>
            <label class="flex items-center gap-2 text-body-md">
                <input type="radio" name="scope" value="period"> Pilih periode sendiri
            </label>
            <div class="flex flex-wrap items-end gap-3 pl-6">
                <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Dari
                    <input type="date" name="export_from" value="{{ $from }}" class="{{ $field }}">
                </label>
                <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Sampai
                    <input type="date" name="export_to" value="{{ $to }}" class="{{ $field }}">
                </label>
            </div>
            <div>
                <button class="px-5 h-10 rounded-full bg-primary text-on-primary text-label-md hover:opacity-90">Unduh CSV</button>
            </div>
            <p class="text-label-sm text-outline">File CSV bisa dibuka langsung di Excel atau Google Sheets. Jika semua data tampil dalam satu kolom di Excel, gunakan Data &gt; From Text/CSV dan pilih pemisah titik koma (;).</p>
        </form>
    </details>
    <script>
        (function () {
            var form = document.getElementById('exportForm');
            function toggle() {
                var period = form.querySelector('input[name=scope]:checked').value === 'period';
                ['export_from', 'export_to'].forEach(function (n) {
                    var el = form.querySelector('[name=' + n + ']');
                    el.disabled = !period;
                    el.required = period;
                });
            }
            form.querySelectorAll('input[name=scope]').forEach(function (r) { r.addEventListener('change', toggle); });
            toggle();
        })();
    </script>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-label-md">{{ $errors->first() }}</div>
    @endif

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Pemasukan (lunas)</div>
            <div class="text-headline-sm text-secondary mt-1">{{ $rp($summary['income']) }}</div>
        </div>
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Pengeluaran (lunas)</div>
            <div class="text-headline-sm text-error mt-1">{{ $rp($summary['expense']) }}</div>
        </div>
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Selisih</div>
            <div class="text-headline-sm mt-1 {{ $summary['balance'] >= 0 ? 'text-secondary' : 'text-error' }}">{{ $rp($summary['balance']) }}</div>
        </div>
        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-label-sm uppercase text-outline">Belum lunas</div>
            <div class="text-body-md mt-1">Piutang: <strong>{{ $rp($summary['receivable']) }}</strong></div>
            <div class="text-body-md">Utang: <strong>{{ $rp($summary['payable']) }}</strong></div>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="bg-surface-container-lowest rounded-xl border border-surface-container overflow-x-auto">
        <table class="w-full text-body-md">
            <thead class="bg-surface-container-low text-left text-label-sm uppercase text-outline">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3 text-right">Nominal</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($transactions as $t)
                <tr class="border-t border-surface-container">
                    <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($t->transaction_date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="font-semibold">{{ $t->category }}</div>
                        <div class="text-label-sm text-outline">{{ $t->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</div>
                    </td>
                    <td class="px-4 py-3 max-w-xs">
                        <div>{{ $t->description ?: '-' }}</div>
                        @if ($t->reference)<div class="text-label-sm text-outline">Ref: {{ $t->reference }}</div>@endif
                        @if ($t->event_name)<div class="text-label-sm text-outline">Bazar: {{ $t->event_name }}</div>@endif
                    </td>
                    <td class="px-4 py-3">{{ $t->payment_method ?: '-' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap font-semibold {{ $t->type === 'income' ? 'text-secondary' : 'text-error' }}">
                        {{ $t->type === 'income' ? '+' : '-' }} {{ $rp($t->amount) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-label-sm {{ $t->payment_status === 'lunas' ? 'bg-secondary-container text-on-secondary-container' : 'bg-tertiary-container text-on-tertiary-container' }}">
                            {{ $t->payment_status === 'lunas' ? 'Lunas' : 'Belum lunas' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($t->payment_status !== 'lunas')
                                <form method="POST" action="{{ route('admin.keuangan.lunas', $t) }}">
                                    @csrf @method('PATCH')
                                    <button title="Tandai lunas" class="w-8 h-8 rounded-full hover:bg-surface-container text-secondary">
                                        <span class="material-symbols-outlined text-[18px]">task_alt</span>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.keuangan.edit', $t) }}" title="Ubah" class="w-8 h-8 rounded-full hover:bg-surface-container text-primary inline-flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.keuangan.destroy', $t) }}" onsubmit="return confirm('Hapus transaksi ini?')">
                                @csrf @method('DELETE')
                                <button title="Hapus" class="w-8 h-8 rounded-full hover:bg-error-container text-error">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">Belum ada transaksi pada periode / filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
@endsection