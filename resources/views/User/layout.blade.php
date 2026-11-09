<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Baby Corner - Pakaian Bayi Katun Organik')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- Tailwind CSS Script -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-fixed-dim": "#97d4ae",
                        "background": "#f9f9ff",
                        "surface-tint": "#216586",
                        "on-error-container": "#93000a",
                        "on-secondary-fixed": "#002111",
                        "on-background": "#111c2c",
                        "outline-variant": "#c0c7ce",
                        "outline": "#71787e",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-container": "#356f50",
                        "on-tertiary-fixed-variant": "#6b3a25",
                        "surface-container-high": "#dee8ff",
                        "secondary": "#2f694a",
                        "surface-variant": "#d8e3fa",
                        "secondary-fixed": "#b2f1c8",
                        "primary-fixed-dim": "#92cef3",
                        "on-tertiary": "#ffffff",
                        "secondary-container": "#b2f1c8",
                        "on-secondary": "#ffffff",
                        "on-surface-variant": "#40484d",
                        "inverse-primary": "#92cef3",
                        "surface-container": "#e7eeff",
                        "on-primary-fixed-variant": "#004c6a",
                        "error": "#ba1a1a",
                        "on-surface": "#111c2c",
                        "on-secondary-fixed-variant": "#125134",
                        "inverse-surface": "#263142",
                        "surface-bright": "#f9f9ff",
                        "primary": "#216586",
                        "on-error": "#ffffff",
                        "surface": "#f9f9ff",
                        "tertiary-container": "#d18f75",
                        "tertiary-fixed": "#ffdbce",
                        "on-tertiary-fixed": "#351001",
                        "on-primary-container": "#003a52",
                        "surface-container-low": "#f0f3ff",
                        "surface-dim": "#cfdaf1",
                        "surface-container-highest": "#d8e3fa",
                        "primary-fixed": "#c5e7ff",
                        "inverse-on-surface": "#ebf1ff",
                        "on-tertiary-container": "#562916",
                        "tertiary": "#87503a",
                        "tertiary-fixed-dim": "#feb69a",
                        "error-container": "#ffdad6",
                        "primary-container": "#6aa6ca",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#001e2d"
                    },
                    fontFamily: {
                        "body-md": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "title-md": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"]
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; overscroll-behavior: none; }
        ::-webkit-scrollbar { display: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col justify-between">

    <!-- HEADER / NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-20 max-w-[1280px] mx-auto px-4 lg:px-8 flex items-center justify-between gap-4">
            
            <!-- Left: Brand Logo & Links -->
            <div class="flex items-center gap-6 sm:gap-8">
                <a class="flex items-center gap-2 shrink-0" href="{{ route('dashboard') }}">
                    <span class="font-bold text-lg text-on-surface tracking-tight">Baby Corner</span>
                </a>
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="text-xs font-semibold {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' }} transition-colors">
                        Beranda
                    </a>
                    <a href="{{ route('produk.index') }}" class="text-xs font-semibold {{ request()->routeIs('produk.*') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' }} transition-colors">
                        Katalog Produk
                    </a>
                    <a href="{{ route('panduan.ukuran') }}" class="text-xs font-semibold {{ request()->routeIs('panduan.ukuran') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' }} transition-colors">
                        Panduan Ukuran
                    </a>
                </nav>
            </div>

            <!-- Right: Action Slots & User Profile -->
            <div class="flex items-center gap-2 sm:gap-3">
                
                {{-- Slot FRI-16: Tombol WhatsApp (Tanpa fungsi dulu) --}}
                <div class="relative">
                    <button type="button" class="w-9 h-9 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-secondary flex items-center justify-center transition-colors shadow-xs" title="Chat WhatsApp (Segera Hadir)">
                        <span class="material-symbols-outlined text-lg">chat</span>
                    </button>
                </div>

                {{-- Slot FRI-03: Ikon Keranjang Belanja (Tanpa fungsi dulu) --}}
                <div class="relative">
                    <button type="button" class="w-9 h-9 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary flex items-center justify-center transition-colors shadow-xs" title="Keranjang Belanja (Segera Hadir)">
                        <span class="material-symbols-outlined text-lg">shopping_bag</span>
                    </button>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-2 pl-1 sm:pl-2">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-semibold text-on-surface leading-tight max-w-[120px] truncate">
                            {{ $userName = Auth::user()->name ?? 'User' }}
                        </span>
                    </div>

                    @php
                        $userName = Auth::user()->name ?? 'User';
                        $words = explode(' ', trim($userName));
                        $initials = '';
                        if (count($words) >= 2) {
                            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                        } else {
                            $initials = strtoupper(substr($userName, 0, 2));
                        }
                    @endphp

                    <a href="{{ route('profile.edit') }}" class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xs hover:opacity-90 transition-opacity shadow-sm overflow-hidden shrink-0" title="Edit Profil">
                        @if(!empty(Auth::user()->photo))
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto" class="w-full h-full object-cover" />
                        @else
                            {{ $initials }}
                        @endif
                    </a> 

                    <!-- Tombol Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-full bg-error-container text-error flex items-center justify-center hover:bg-error hover:text-on-error transition-all shadow-xs shrink-0" title="Keluar / Logout">
                            <span class="material-symbols-outlined text-sm">logout</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- Mobile Navigation Bar Under Header -->
        <div class="md:hidden flex items-center justify-around border-t border-surface-container bg-surface/95 px-4 py-2 text-xs">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-1 font-semibold {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-on-surface-variant' }}">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>Beranda</span>
            </a>
            <a href="{{ route('produk.index') }}" class="flex items-center gap-1 font-semibold {{ request()->routeIs('produk.*') ? 'text-primary' : 'text-on-surface-variant' }}">
                <span class="material-symbols-outlined text-sm">grid_view</span>
                <span>Katalog</span>
            </a>
            <a href="{{ route('panduan.ukuran') }}" class="flex items-center gap-1 font-semibold {{ request()->routeIs('panduan.ukuran') ? 'text-primary' : 'text-on-surface-variant' }}">
                <span class="material-symbols-outlined text-sm">straighten</span>
                <span>Panduan Ukuran</span>
            </a>
        </div>
    </header>

    <!-- FLASH MESSAGES -->
    <div class="w-full pt-28 md:pt-24">
        @if(session('success'))
            <div class="max-w-[1280px] mx-auto px-4 lg:px-8 mb-4">
                <div class="p-4 rounded-2xl bg-secondary-container/60 border border-secondary text-secondary text-xs font-semibold flex items-center gap-2 shadow-xs">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="max-w-[1280px] mx-auto px-4 lg:px-8 mb-4">
                <div class="p-4 rounded-2xl bg-error-container border border-error text-error text-xs font-semibold flex items-center gap-2 shadow-xs">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- MAIN CONTENT -->
    <main class="w-full flex-1 bg-background flex flex-col items-center">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="w-full bg-surface-container-low py-12 border-t border-surface-container mt-12">
        <div class="max-w-[1280px] mx-auto px-4 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3">
                <span class="font-bold text-base text-on-surface tracking-tight block">Baby Corner</span>
                <p class="text-xs text-on-surface-variant leading-relaxed">
                    Pakaian bayi berbahan 100% katun organik bersertifikasi. Menghadirkan kelembutan alami dan kenyamanan bernapas untuk kulit si kecil sejak hari pertama.
                </p>
            </div>
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-on-surface uppercase tracking-wider">Belanja</h4>
                <ul class="space-y-1.5 text-xs text-on-surface-variant">
                    <li><a href="{{ route('produk.index') }}" class="hover:text-on-surface transition-colors">Koleksi Bayi Baru Lahir</a></li>
                    <li><a href="{{ route('produk.index') }}" class="hover:text-on-surface transition-colors">Baju Tidur Organik</a></li>
                    <li><a href="{{ route('produk.index') }}" class="hover:text-on-surface transition-colors">Aksesoris & Topi Bayi</a></li>
                    <li><a href="{{ route('produk.index') }}" class="hover:text-on-surface transition-colors">Paket Bundling Lahiran</a></li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-on-surface uppercase tracking-wider">Bantuan</h4>
                <ul class="space-y-1.5 text-xs text-on-surface-variant">
                    <li><a href="{{ route('panduan.ukuran') }}" class="hover:text-on-surface transition-colors">Panduan Ukuran</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Status Pengiriman</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Kebijakan Pengembalian</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Pusat Bantuan & FAQ</a></li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-on-surface uppercase tracking-wider">Tentang Baby Corner</h4>
                <ul class="space-y-1.5 text-xs text-on-surface-variant">
                    <li><a href="#" class="hover:text-on-surface transition-colors">Filosofi Organik</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Standar & Sertifikasi</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Hubungi Kami</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-[1280px] mx-auto px-4 lg:px-8 mt-10 pt-6 border-t border-surface-container flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-on-surface-variant">
            <p>© 2026 Baby Corner. Hak cipta dilindungi undang-undang. Dirancang dengan kelembutan untuk ibu & buah hati.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-on-surface transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-on-surface transition-colors">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

