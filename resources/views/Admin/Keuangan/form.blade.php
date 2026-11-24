@extends('Admin.keuangan.layout')
@section('title', $transaction->exists ? 'Ubah Transaksi' : 'Catat Transaksi')

@section('content')
@php
    $field = 'w-full h-11 px-3 rounded-xl bg-surface-container-low text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40';
    $label = 'flex flex-col gap-1 text-label-md text-on-surface';
    $action = $transaction->exists ? route('admin.keuangan.update', $transaction) : route('admin.keuangan.store');
@endphp

<div class="max-w-3xl flex flex-col gap-6">
    <div class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container">
        <h1 class="text-headline-md">{{ $transaction->exists ? 'Ubah Transaksi' : 'Catat Transaksi Baru' }}</h1>
        <p class="text-on-surface-variant">Isi data dengan teliti. Nominal cukup angka, contoh: 150000 atau 150.000.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-label-md">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container grid grid-cols-1 md:grid-cols-2 gap-5">
        @csrf
        @if ($transaction->exists) @method('PUT') @endif

        <label class="{{ $label }}">Tanggal
            <input type="date" name="transaction_date" max="{{ now()->toDateString() }}" required
                   value="{{ old('transaction_date', substr((string) $transaction->transaction_date, 0, 10)) }}" class="{{ $field }}">
        </label>

        <label class="{{ $label }}">Jenis transaksi
            <select name="type" id="type" required class="{{ $field }}">
                <option value="income" @selected(old('type', $transaction->type) === 'income')>Pemasukan</option>
                <option value="expense" @selected(old('type', $transaction->type) === 'expense')>Pengeluaran</option>
            </select>
        </label>

        <label class="{{ $label }}">Kategori
            <select name="category" id="category" required class="{{ $field }}"></select>
        </label>

        <label class="{{ $label }}">Nominal (Rp)
            <input type="text" inputmode="numeric" name="amount" required placeholder="150000"
                   value="{{ old('amount', $transaction->amount) }}" class="{{ $field }}">
        </label>

        <label class="{{ $label }}">Status pembayaran
            <select name="payment_status" required class="{{ $field }}">
                <option value="lunas" @selected(old('payment_status', $transaction->payment_status) === 'lunas')>Lunas</option>
                <option value="belum_lunas" @selected(old('payment_status', $transaction->payment_status) === 'belum_lunas')>Belum lunas (piutang / utang)</option>
            </select>
        </label>

        <label class="{{ $label }}">Metode pembayaran
            <select name="payment_method" class="{{ $field }}">
                <option value="">-- Tidak diisi --</option>
                @foreach ($methods as $m)
                    <option value="{{ $m }}" @selected(old('payment_method', $transaction->payment_method) === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </label>

        <label class="{{ $label }}">No. referensi (nota / pesanan)
            <input type="text" name="reference" maxlength="100" value="{{ old('reference', $transaction->reference) }}" class="{{ $field }}">
        </label>

        <label class="{{ $label }}">Nama bazar (jika terkait bazar)
            <input type="text" name="event_name" maxlength="100" value="{{ old('event_name', $transaction->event_name) }}" class="{{ $field }}">
        </label>

        <label class="{{ $label }} md:col-span-2">Keterangan
            <textarea name="description" rows="3" maxlength="1000" class="w-full px-3 py-2 rounded-xl bg-surface-container-low text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40">{{ old('description', $transaction->description) }}</textarea>
        </label>

        <div class="md:col-span-2 flex gap-2">
            <button class="px-6 h-11 rounded-full bg-primary text-on-primary text-label-md hover:opacity-90">Simpan</button>
            <a href="{{ route('admin.keuangan.index') }}" class="px-6 h-11 inline-flex items-center rounded-full bg-surface-container text-on-surface text-label-md">Batal</a>
        </div>
    </form>
</div>

<script>
    // Kategori berubah mengikuti jenis transaksi (pemasukan / pengeluaran)
    const categories = @json($categories);
    const selectedCategory = @json(old('category', $transaction->category));
    const typeEl = document.getElementById('type');
    const catEl = document.getElementById('category');

    function fillCategories(keep) {
        catEl.innerHTML = '<option value="">-- Pilih kategori --</option>';
        (categories[typeEl.value] || []).forEach(function (c) {
            const o = document.createElement('option');
            o.value = c; o.textContent = c;
            if (c === keep) o.selected = true;
            catEl.appendChild(o);
        });
    }
    typeEl.addEventListener('change', function () { fillCategories(''); });
    fillCategories(selectedCategory);
</script>
@endsection
