{{-- Form pilih periode + tombol periode cepat. Variabel: $action, $from, $to, $presets, (opsional) $group --}}
@php $field = 'h-10 px-3 rounded-xl bg-surface-container-low text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40'; @endphp
<form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-3 bg-surface-container-lowest p-4 rounded-xl border border-surface-container">
    <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Dari
        <input type="date" name="from" value="{{ $from }}" class="{{ $field }}">
    </label>
    <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Sampai
        <input type="date" name="to" value="{{ $to }}" class="{{ $field }}">
    </label>
    @isset($group)
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Rincian grafik
            <select name="group" class="{{ $field }}">
                <option value="harian" @selected($group === 'harian')>Per hari</option>
                <option value="bulanan" @selected($group === 'bulanan')>Per bulan</option>
            </select>
        </label>
    @endisset
    <button class="px-5 h-10 rounded-full bg-primary text-on-primary text-label-md hover:opacity-90">Tampilkan</button>
    <div class="flex flex-wrap gap-2 ml-auto">
        @foreach ($presets as $name => $p)
            <a href="{{ $action }}?from={{ $p['from'] }}&to={{ $p['to'] }}"
               class="px-4 h-10 inline-flex items-center rounded-full bg-surface-container text-on-surface text-label-md hover:bg-surface-container-low">{{ $name }}</a>
        @endforeach
    </div>
</form>
@if ($errors->any())
    <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-label-md">{{ $errors->first() }}</div>
@endif