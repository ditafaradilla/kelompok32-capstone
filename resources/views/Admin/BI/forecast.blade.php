@extends('Admin.keuangan.layout')
@section('title', 'Forecasting & Kebutuhan Stok')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Transaction::rupiah($n);
    $field = 'h-10 px-3 rounded-xl bg-surface-container-low text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container/40';
@endphp

<div class="flex flex-col gap-6">
    <div class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container">
        <h1 class="text-headline-md">Forecasting Penjualan &amp; Kebutuhan Stok</h1>
        <p class="text-on-surface-variant">Prediksi sederhana dari riwayat penjualan mingguan (minggu penuh Senin–Minggu). Ini perkiraan, bukan kepastian.</p>
    </div>

    <form method="GET" action="{{ route('admin.bi.forecast') }}" class="flex flex-wrap items-end gap-3 bg-surface-container-lowest p-4 rounded-xl border border-surface-container">
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Riwayat dipakai
            <select name="weeks" class="{{ $field }}">
                @foreach ([8, 12, 24, 52] as $w)<option value="{{ $w }}" @selected($weeks === $w)>{{ $w }} minggu terakhir</option>@endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1 text-label-sm text-outline uppercase">Prediksi ke depan
            <select name="horizon" class="{{ $field }}">
                @foreach ([2, 4, 8, 12] as $h)<option value="{{ $h }}" @selected($horizon === $h)>{{ $h }} minggu</option>@endforeach
            </select>
        </label>
        <button class="px-5 h-10 rounded-full bg-primary text-on-primary text-label-md hover:opacity-90">Hitung</button>
    </form>
    @if ($errors->any())
        <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-label-md">{{ $errors->first() }}</div>
    @endif

    @if (! $enough)
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-10 text-center text-on-surface-variant">
            Riwayat penjualan belum cukup. Dibutuhkan minimal <b>{{ $minWeeks }} minggu penuh</b> yang memiliki penjualan lunas (tersedia: {{ $history['count'] }} minggu).
            Catat penjualan di Pembukuan, atau pilih riwayat yang lebih panjang.
        </div>
    @else
        @php
            $maTotal = array_sum($result['moving_average']);
            $lnTotal = array_sum($result['linear']);
            $avgWeek = intdiv(array_sum($history['values']), $history['count']);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
                <div class="text-label-sm uppercase text-outline">Rata-rata penjualan / minggu (riwayat)</div>
                <div class="text-headline-sm mt-1">{{ $rp($avgWeek) }}</div>
                <div class="text-label-sm text-outline mt-1">{{ $history['count'] }} minggu, mulai {{ \Carbon\Carbon::parse($history['from'])->format('d/m/Y') }}</div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
                <div class="text-label-sm uppercase text-outline">Prediksi {{ $horizon }} minggu (rata-rata bergerak)</div>
                <div class="text-headline-sm text-primary mt-1">{{ $rp($maTotal) }}</div>
                <div class="text-label-sm text-outline mt-1">Rata-rata {{ $result['window'] }} minggu terakhir, lebih stabil</div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
                <div class="text-label-sm uppercase text-outline">Prediksi {{ $horizon }} minggu (tren linear)</div>
                <div class="text-headline-sm text-secondary mt-1">{{ $rp($lnTotal) }}</div>
                <div class="text-label-sm text-outline mt-1">Mengikuti arah naik/turun riwayat</div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
                <div class="text-label-sm uppercase text-outline">Kesalahan uji (MAPE)</div>
                <div class="text-headline-sm mt-1">{{ $result['mape'] !== null ? $result['mape'] . '%' : '-' }}</div>
                <div class="text-label-sm text-outline mt-1">Makin kecil makin akurat. Penjualan yang naik-turun membuatnya besar.</div>
            </div>
        </div>

        <div class="bg-surface-container-lowest p-5 rounded-xl border border-surface-container">
            <div class="text-title-md mb-3">Riwayat dan prediksi penjualan mingguan</div>
            <div class="h-80"><canvas id="forecastChart"></canvas></div>
            <p class="text-label-sm text-outline mt-2">Label tanggal = hari Senin awal minggu. Garis putus-putus adalah prediksi.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-surface-container-lowest rounded-xl border border-surface-container overflow-x-auto">
                <div class="px-5 py-4 text-title-md">Prediksi per minggu</div>
                <table class="w-full text-body-md">
                    <thead class="bg-surface-container-low text-left text-label-sm uppercase text-outline">
                        <tr><th class="px-4 py-3">Minggu mulai</th><th class="px-4 py-3 text-right">Rata-rata bergerak</th><th class="px-4 py-3 text-right">Tren linear</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($futureLabels as $i => $label)
                        <tr class="border-t border-surface-container">
                            <td class="px-4 py-3">{{ $label }}</td>
                            <td class="px-4 py-3 text-right">{{ $rp($result['moving_average'][$i]) }}</td>
                            <td class="px-4 py-3 text-right">{{ $rp($result['linear'][$i]) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
                <div class="text-title-md mb-2">Anggaran belanja stok (perkiraan)</div>
                @if ($budget['ratio'] !== null)
                    <div class="text-headline-sm text-primary">{{ $rp($budget['budget']) }}</div>
                    <p class="text-on-surface-variant mt-2">
                        Pada riwayat {{ $history['count'] }} minggu, pembelian stok {{ $rp($budget['purchases']) }} adalah
                        <b>{{ $budget['ratio'] }}%</b> dari penjualan {{ $rp($budget['sales']) }}.
                        Rasio itu dikalikan prediksi penjualan {{ $horizon }} minggu (rata-rata bergerak).
                    </p>
                    <p class="text-label-sm text-outline mt-2">Dihitung dari Pembukuan (kategori Pembelian Stok / Barang). Ini panduan anggaran, bukan jumlah barang per produk.</p>
                @else
                    <p class="text-on-surface-variant">Belum bisa dihitung karena belum ada penjualan pada riwayat.</p>
                @endif
            </div>
        </div>

        {{-- Rekomendasi stok per produk --}}
        @if ($stock !== null)
            <div class="bg-surface-container-lowest rounded-xl border border-surface-container overflow-x-auto">
                <div class="px-5 py-4">
                    <div class="text-title-md">Rekomendasi stok per produk ({{ $horizon }} minggu ke depan)</div>
                    <p class="text-label-sm text-outline">Kebutuhan = rata-rata terjual per minggu × {{ $horizon }}. Target = kebutuhan + cadangan 20%. Produk tanpa penjualan pada riwayat tidak ikut dihitung.</p>
                </div>
                <table class="w-full text-body-md">
                    <thead class="bg-surface-container-low text-left text-label-sm uppercase text-outline">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-right">Stok</th>
                            <th class="px-4 py-3 text-right">Terjual / minggu</th>
                            <th class="px-4 py-3 text-right">Kebutuhan</th>
                            <th class="px-4 py-3 text-right">Target stok</th>
                            <th class="px-4 py-3 text-right">Perlu restok</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($stock as $s)
                        <tr class="border-t border-surface-container">
                            <td class="px-4 py-3 font-semibold">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-right">{{ $s->stock ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ $s->weekly }}</td>
                            <td class="px-4 py-3 text-right">{{ $s->demand }}</td>
                            <td class="px-4 py-3 text-right">{{ $s->target }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $s->restock ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-label-sm {{ $s->status === 'Kritis' ? 'bg-error-container text-on-error-container' : ($s->status === 'Segera restok' ? 'bg-tertiary-container text-on-tertiary-container' : 'bg-secondary-container text-on-secondary-container') }}">{{ $s->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">Belum ada penjualan produk pada riwayat.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @else
            @include('Admin.bi.need_data', ['title' => 'Rekomendasi stok per produk', 'error' => $productError])
        @endif
    @endif
</div>

@if ($enough)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels: @json($chart['labels']),
            datasets: [
                { label: 'Riwayat', data: @json($chart['history']), borderColor: '#216586', backgroundColor: 'rgba(106,166,202,0.2)', fill: true, tension: 0.25, pointRadius: 2 },
                { label: 'Prediksi rata-rata bergerak', data: @json($chart['ma']), borderColor: '#d18f75', borderDash: [6, 4], tension: 0, pointRadius: 3 },
                { label: 'Prediksi tren linear', data: @json($chart['linear']), borderColor: '#2f694a', borderDash: [2, 4], tension: 0, pointRadius: 3 }
            ]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { tooltip: { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': Rp ' + Number(c.parsed.y).toLocaleString('id-ID'); } } } },
            scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); } } } }
        }
    });
</script>
@endif
@endsection