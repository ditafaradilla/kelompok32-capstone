
@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $maxTrend = max($trend['values'] ?: [1]);
    $maxChannel = max($channel ?: [1]);
@endphp

<div class="space-y-6">

    {{-- JUDUL DASHBOARD --}}
    <div>
        <h2 class="text-headline-md font-bold text-on-surface">
            Dashboard Analisis Penjualan
        </h2>

        <p class="text-body-md text-on-surface-variant mt-1">
            Ringkasan dan pemantauan performa penjualan Baby Corner.
        </p>
    </div>

    {{-- FILTER PERIODE --}}
    @include('Admin.BI.period', [
        'action' => $dashboardAction,
        'from' => $from,
        'to' => $to,
        'presets' => $presets,
        'group' => $group
    ])

    {{-- KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
            <p class="text-label-md text-on-surface-variant">
                Total Penjualan
            </p>

            <h2 class="text-headline-md font-bold mt-2">
                {{ $formatRupiah($kpi['total']) }}
            </h2>
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
            <p class="text-label-md text-on-surface-variant">
                Jumlah Transaksi
            </p>

            <h2 class="text-headline-md font-bold mt-2">
                {{ number_format($kpi['count'], 0, ',', '.') }}
            </h2>
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
            <p class="text-label-md text-on-surface-variant">
                Rata-rata Penjualan
            </p>

            <h2 class="text-headline-md font-bold mt-2">
                {{ $formatRupiah($kpi['average']) }}
            </h2>
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">
            <p class="text-label-md text-on-surface-variant">
                Pertumbuhan Penjualan
            </p>

            <h2 class="text-headline-md font-bold mt-2">
                @if ($kpi['growth'] !== null)
                    {{ number_format($kpi['growth'], 2, ',', '.') }}%
                @else
                    -
                @endif
            </h2>

            <p class="text-xs text-on-surface-variant mt-1">
                Dibandingkan periode sebelumnya
            </p>
        </div>

    </div>

    {{-- GRAFIK TREN PENJUALAN --}}
    <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">

        <h2 class="text-title-lg font-semibold mb-4">
            Tren Penjualan
        </h2>

        @if (count($trend['values']) > 0 && max($trend['values']) > 0)

            <div class="space-y-3">

                @foreach ($trend['labels'] as $i => $label)

                    @php
                        $value = $trend['values'][$i] ?? 0;
                        $width = ($value / $maxTrend) * 100;
                    @endphp

                    <div>

                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $label }}</span>
                            <span>{{ $formatRupiah($value) }}</span>
                        </div>

                        <div class="w-full h-3 bg-surface-container rounded-full overflow-hidden">
                            <div
                                class="h-3 bg-primary rounded-full"
                                style="width: {{ $width }}%">
                            </div>
                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <p class="text-sm text-on-surface-variant">
                Belum terdapat data penjualan pada periode ini.
            </p>

        @endif

    </div>

    {{-- PENJUALAN BERDASARKAN KANAL --}}
    <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">

        <h2 class="text-title-lg font-semibold mb-4">
            Penjualan Berdasarkan Kanal
        </h2>

        @if (collect($channel)->sum() > 0)

            <div class="space-y-4">

                @foreach ($channel as $name => $value)

                    @php
                        $width = ($value / $maxChannel) * 100;
                    @endphp

                    <div>

                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $name }}</span>
                            <span>{{ $formatRupiah($value) }}</span>
                        </div>

                        <div class="w-full h-3 bg-surface-container rounded-full overflow-hidden">
                            <div
                                class="h-3 bg-secondary rounded-full"
                                style="width: {{ $width }}%">
                            </div>
                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <p class="text-sm text-on-surface-variant">
                Belum terdapat data penjualan berdasarkan kanal.
            </p>

        @endif

    </div>


{{-- ANALISIS KINERJA BAZAR --}}
<div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5 space-y-5">

    <div>
        <h2 class="text-title-lg font-semibold">
            Analisis Kinerja Bazar
        </h2>

        <p class="text-sm text-on-surface-variant mt-1">
            Ringkasan pendapatan, pengeluaran, dan keuntungan setiap bazar.
        </p>
    </div>

    {{-- BAZAR PALING MENGUNTUNGKAN --}}
    @if ($topBazaar)

        <div class="rounded-xl bg-primary-container/30 border border-primary/20 p-5">

            <p class="text-sm text-on-surface-variant">
                Bazar Paling Menguntungkan
            </p>

            <h3 class="text-lg font-bold mt-1">
                {{ $topBazaar->name }}
            </h3>

            <p class="text-2xl font-bold text-primary mt-3">
                {{ $formatRupiah($topBazaar->net) }}
            </p>

            <p class="text-sm text-on-surface-variant mt-1">
                {{ $topBazaar->net >= 0 ? 'Laba bersih' : 'Kerugian' }}
            </p>

        </div>

    @else

        <div class="rounded-xl bg-surface-container p-4">
            <p class="text-sm text-on-surface-variant">
                Belum terdapat data kinerja bazar pada periode ini.
            </p>
        </div>

    @endif

    {{-- TABEL KINERJA SETIAP BAZAR --}}
    <div>

        <h3 class="text-base font-semibold mb-3">
            Rincian Kinerja Bazar
        </h3>

        @if ($events->isNotEmpty())

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left">

                    <thead>
                        <tr class="border-b border-outline-variant text-on-surface-variant">
                            <th class="py-3 pr-4">Nama Bazar</th>
                            <th class="py-3 pr-4">Penjualan</th>
                            <th class="py-3 pr-4">Pengeluaran</th>
                            <th class="py-3 pr-4">Laba/Rugi</th>
                            <th class="py-3">ROI</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($events as $event)

                            <tr class="border-b border-outline-variant/50">

                                <td class="py-3 pr-4 font-medium">
                                    {{ $event->name }}
                                </td>

                                <td class="py-3 pr-4 whitespace-nowrap">
                                    {{ $formatRupiah($event->income) }}
                                </td>

                                <td class="py-3 pr-4 whitespace-nowrap">
                                    {{ $formatRupiah($event->expense) }}
                                </td>

                                <td class="py-3 pr-4 whitespace-nowrap font-semibold
                                    {{ $event->net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $formatRupiah($event->net) }}
                                </td>

                                <td class="py-3 whitespace-nowrap">
                                    @if ($event->roi !== null)
                                        {{ number_format($event->roi, 1, ',', '.') }}%
                                    @else
                                        -
                                    @endif
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <p class="text-sm text-on-surface-variant">
                Belum ada data bazar yang tercatat pada periode ini.
            </p>

        @endif

    </div>

    {{-- PENJUALAN BAZAR TANPA NAMA --}}
    @if ($untagged > 0)

        <div class="rounded-lg border border-outline-variant p-4">

            <p class="text-sm font-semibold">
                Penjualan Bazar Tanpa Nama Bazar
            </p>

            <p class="text-lg font-bold mt-1">
                {{ $formatRupiah($untagged) }}
            </p>

            <p class="text-xs text-on-surface-variant mt-1">
                Penjualan ini belum dapat dimasukkan ke perhitungan
                kinerja bazar tertentu karena nama bazar belum diisi.
            </p>

        </div>

    @endif

</div>

    {{-- PRODUK TERLARIS --}}
    <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-5">

        <h2 class="text-title-lg font-semibold mb-4">
            Produk Terlaris
        </h2>

        @if (!$productsOn)

            @include('Admin.BI.need_data', [
                'title' => 'Data produk belum tersedia'
            ])

        @elseif ($productError)

            @include('Admin.BI.need_data', [
                'title' => 'Data produk belum dapat ditampilkan',
                'error' => $productError
            ])

        @elseif ($topProducts && $topProducts->isNotEmpty())

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left">

                    <thead>
                        <tr class="border-b border-outline-variant text-on-surface-variant">
                            <th class="py-3 pr-4">No.</th>
                            <th class="py-3 pr-4">Nama Produk</th>
                            <th class="py-3 pr-4">Jumlah Terjual</th>
                            <th class="py-3 pr-4">Total Penjualan</th>
                            <th class="py-3">Stok</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($topProducts as $index => $product)

                            <tr class="border-b border-outline-variant/50">

                                <td class="py-3 pr-4">
                                    {{ $index + 1 }}
                                </td>

                                <td class="py-3 pr-4">
                                    {{ $product->name }}
                                </td>

                                <td class="py-3 pr-4">
                                    {{ number_format($product->qty, 0, ',', '.') }}
                                </td>

                                <td class="py-3 pr-4">
                                    {{ $formatRupiah($product->revenue) }}
                                </td>

                                <td class="py-3">
                                    {{ $product->stock ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <p class="text-sm text-on-surface-variant">
                Belum terdapat data produk terjual pada periode ini.
            </p>

        @endif

    </div>

</div>
