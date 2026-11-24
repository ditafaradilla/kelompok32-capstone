<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Baby Corner - @yield('title', 'Admin Backoffice')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;1,400&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
        }
        ::-webkit-scrollbar { display: none; }
    </style>
    
    <!-- Tailwind CSS Script -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container": "#e7eeff",
                        "surface-container-lowest": "#ffffff",
                        "on-surface": "#111c2c",
                        "tertiary-fixed-dim": "#feb69a",
                        "outline-variant": "#c0c7ce",
                        "on-secondary-container": "#356f50",
                        "on-background": "#111c2c",
                        "on-secondary-fixed": "#002111",
                        "surface": "#f9f9ff",
                        "on-tertiary-fixed-variant": "#6b3a25",
                        "on-primary-fixed-variant": "#004c6a",
                        "inverse-surface": "#263142",
                        "primary-fixed-dim": "#92cef3",
                        "surface-variant": "#d8e3fa",
                        "on-primary": "#ffffff",
                        "surface-bright": "#f9f9ff",
                        "on-surface-variant": "#40484d",
                        "surface-container-low": "#f0f3ff",
                        "primary": "#216586",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#c5e7ff",
                        "surface-container-high": "#dee8ff",
                        "primary-container": "#6aa6ca",
                        "on-tertiary-container": "#562916",
                        "on-error-container": "#93000a",
                        "on-secondary": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "on-primary-container": "#003a52",
                        "surface-container-highest": "#d8e3fa",
                        "surface-tint": "#216586",
                        "background": "#f9f9ff",
                        "on-primary-fixed": "#001e2d",
                        "inverse-on-surface": "#ebf1ff",
                        "secondary-fixed-dim": "#97d4ae",
                        "inverse-primary": "#92cef3",
                        "tertiary-container": "#d18f75",
                        "on-secondary-fixed-variant": "#125134",
                        "secondary": "#2f694a",
                        "tertiary": "#87503a",
                        "on-error": "#ffffff",
                        "surface-dim": "#cfdaf1",
                        "tertiary-fixed": "#ffdbce",
                        "error": "#ba1a1a",
                        "outline": "#71787e",
                        "on-tertiary-fixed": "#351001",
                        "secondary-fixed": "#b2f1c8",
                        "secondary-container": "#b2f1c8"
                    },
                    borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
                    fontFamily: {
                        "sans": ["Plus Jakarta Sans", "sans-serif"]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface font-sans text-on-surface antialiased">

    @include('Admin.Partials.sidebar')

    <!-- WRAPPER MAIN & HEADER -->
    <div class="pl-72 flex flex-col min-h-screen">
        
        <!-- HEADER -->
        <header class="fixed top-0 left-72 right-0 h-20 bg-surface/90 backdrop-blur-xl z-40 border-b border-surface-container shadow-[0_1px_8px_rgba(0,0,0,0.03)]">
            <div class="h-20 w-full px-8 flex items-center justify-between gap-6">
                <div>
                    <h2 class="text-base font-bold text-on-surface">@yield('page-title', 'Backoffice Toko Baju Bayi')</h2>
                    <p class="text-xs text-on-surface-variant">@yield('page-subtitle', 'Kelola produk, alokasi stok toko/gudang, pemesanan dan penerimaan supplier')</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 pl-2">
                        <div class="flex flex-col text-right">
                            <span class="text-xs font-semibold text-on-surface">{{ Auth::user()->name ?? 'Admin Baby Corner' }}</span>
                            <span class="text-[10px] text-outline uppercase font-medium">Administrator</span>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-primary text-on-primary flex items-center justify-center text-sm font-bold shadow-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 w-full pt-28 bg-surface px-8 pb-12">
            
            <!-- Flash Notification Success -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-secondary-container/80 border border-secondary text-on-secondary-container text-xs font-semibold flex items-center gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-secondary text-lg">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Flash Notification Error -->
            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-error-container/80 border border-error text-on-error-container text-xs font-semibold flex items-center gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-error text-lg">warning</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Low Stock Warnings from Session (Fitur 11) -->
            @if(session('low_stock_alerts') && count(session('low_stock_alerts')) > 0)
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs shadow-xs space-y-2">
                    <div class="flex items-center gap-2 font-bold text-amber-800">
                        <span class="material-symbols-outlined text-amber-600 text-base">notification_important</span>
                        <span>Peringatan Stok Minimum Terdeteksi:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 pl-2">
                        @foreach(session('low_stock_alerts') as $alert)
                            <li>
                                <strong>{{ $alert['name'] }}</strong>: Sisa stok saat ini 
                                <span class="font-bold {{ $alert['is_empty'] ? 'text-red-600' : 'text-amber-700' }}">{{ $alert['total_stock'] }}</span> 
                                (Batas minimum: {{ $alert['min_stock'] }}). 
                                {{ $alert['is_empty'] ? '🚨 Segera lakukan pemesanan ulang ke pemasok!' : '⚠️ Stok hampir habis.' }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="w-full bg-surface-container-low py-4 border-t border-surface-container">
            <div class="w-full px-8 flex flex-col md:flex-row items-center justify-between gap-2 text-on-surface-variant text-xs">
                <div>© 2026 Baby Corner. Hak Cipta Dilindungi. Backoffice E-Commerce Baju Bayi.</div>
                <div class="text-[11px] text-outline">Modul Fitur 8, 9, 10, 11</div>
            </div>
        </footer>

    </div>

    @yield('scripts')
</body>
</html>