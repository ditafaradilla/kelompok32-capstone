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

    <!-- ASIDE SIDEBAR -->
    <aside class="fixed left-0 top-0 bottom-0 w-72 bg-surface-container-lowest z-50 flex flex-col justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="flex flex-col">
            <div class="h-20 px-6 flex items-center justify-between border-b border-surface-container-low">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold tracking-tight text-primary">Baby Corner</span>
                    <span class="px-2 py-0.5 rounded-full bg-surface-container text-on-primary-container text-xs font-semibold">Backoffice</span>
                </div>
            </div>
            
            <div class="px-4 py-4">
                <div class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-outline">Modul Inventaris & Produk</div>
                <nav class="flex flex-col gap-1.5">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-primary-container text-on-primary-container font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">dashboard</span>
                        <span>Dashboard</span>
                    </a>

                    <!-- Fitur 8: Kelola Produk & Pemisahan Stok -->
                    <a href="{{ route('admin.produk.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.produk.*') ? 'bg-primary-container text-on-primary-container font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">styler</span>
                        <span>Kelola Produk & Stok</span>
                    </a>

                    <!-- Fitur 9: Pencatatan Lokasi dan Stok -->
                    <a href="{{ route('admin.lokasi.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.lokasi.*') ? 'bg-primary-container text-on-primary-container font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">warehouse</span>
                        <span>Lokasi & Stok Toko</span>
                    </a>

                    <!-- Fitur 10: Pemesanan Barang ke Pemasok -->
                    <a href="{{ route('admin.pemesanan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.pemesanan.*') ? 'bg-primary-container text-on-primary-container font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                        <span>Pemesanan Pemasok</span>
                    </a>

                    <!-- Fitur 11: Penerimaan Barang & Peringatan Stok -->
                    <a href="{{ route('admin.penerimaan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.penerimaan.*') ? 'bg-primary-container text-on-primary-container font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                        <span>Penerimaan Barang</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Tombol Logout -->
        <div class="p-4 border-t border-surface-container-low">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-error-container text-on-error-container text-xs font-semibold hover:opacity-90 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    <span>Keluar Admin</span>
                </button>
            </form>
        </div>
    </aside>

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
