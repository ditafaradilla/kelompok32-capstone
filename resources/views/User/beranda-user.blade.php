<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby Corner - Beranda Utama</title>
    
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; overscroll-behavior: none; }
        ::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col justify-between">

    <!-- HEADER / NAVBAR (Tanpa Search Bar) -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-20 max-w-[1280px] mx-auto px-4 lg:px-8 flex items-center justify-between gap-4">
            
            <!-- Left: Brand Logo & Links -->
            <div class="flex items-center gap-8">
                <a class="flex items-center gap-2" href="{{ route('dashboard') }}">
                    <span class="font-bold text-lg text-on-surface tracking-tight">Baby Corner</span>
                </a>
                <nav class="hidden lg:flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-primary transition-colors">Beranda</a>
                    <a href="#" class="text-xs text-on-surface-variant hover:text-on-surface transition-colors">Katalog Produk</a>
                    <a href="#" class="text-xs text-on-surface-variant hover:text-on-surface transition-colors">Panduan Ukuran</a>
                </nav>
            </div>

            <!-- Right: User Profile with Dynamic Initials  -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 pl-2">
                    <div class="flex flex-col text-right">
                        <span class="text-xs font-semibold text-on-surface leading-tight">
                            {{ $userName = Auth::user()->name ?? 'User' }}
                        </span>
                        
                    </div>
                    <a href="{{ route('profile.edit') }}" class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xs hover:opacity-90 transition-opacity shadow-sm" title="Edit Profil">
                        @php
                            $words = explode(' ', trim($userName));
                            $initials = '';
                            if (count($words) >= 2) {
                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($userName, 0, 2));
                            }
                        @endphp
                        {{ $initials }}
                    </a>
                    <!-- Tombol Logout (Form POST demi keamanan Laravel) -->
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="w-8 h-8 rounded-full bg-error-container text-error flex items-center justify-center hover:bg-error hover:text-on-error transition-all shadow-xs" title="Keluar / Logout">
                <span class="material-symbols-outlined text-sm">logout</span>
            </button>
        </form>
    </div>
</div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="w-full pt-24 pb-16 flex-1 bg-background flex flex-col items-center">
        <div class="w-full max-w-[1280px] px-4 lg:px-8 mx-auto flex flex-col gap-8">
            
            <!-- HERO BANNER SECTION -->
            <div class="w-full bg-gradient-to-r from-primary-fixed/40 via-surface-variant/50 to-secondary-fixed/30 rounded-3xl p-6 sm:p-10 shadow-sm relative overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                
                <div class="lg:col-span-7 z-10 space-y-4">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 shadow-xs text-primary text-[11px] font-semibold">
                        <span class="material-symbols-outlined text-xs">verified</span>
                        <span>100% Katun Organik Bersertifikasi OEKO-TEX</span>
                    </div>
                    
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-on-surface tracking-tight leading-tight">
                        Sentuhan Lembut untuk Awal Kehidupan Si Kecil
                    </h1>
                    
                    <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed max-w-lg">
                        Bahan 100% katun organik bersertifikasi OEKO-TEX, ekstra lembut, higienis, dan sejuk untuk menjaga kenyamanan kulit paling sensitif.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <a href="#" class="px-5 py-2.5 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-semibold text-xs shadow-md transition-all flex items-center gap-2">
                            <span>Belanja Sekarang</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                        
                    </div>

                    <div class="flex items-center gap-2 pt-2 text-[11px] text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary text-sm">local_shipping</span>
                        
                    </div>
                </div>

                <div class="lg:col-span-5 z-10 flex justify-center">
                    <div class="relative w-full max-w-md rounded-2xl overflow-hidden shadow-lg h-64 sm:h-72 bg-white">
                        <img src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&q=80&w=800" class="w-full h-full object-cover" alt="Nursery Room">
                        <div class="absolute bottom-3 left-3 right-3 bg-white/90 backdrop-blur-md p-3 rounded-xl shadow-sm flex items-center justify-between text-xs font-semibold">
                            <span class="text-on-surface">⭐ 4.9/5 • Bunda Percaya</span>
                            <span class="text-primary">Kualitas & Keamanan Anti Alergi</span>
                        </div>
                    </div>
                </div>

                <!-- Decorative blur element -->
                <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-secondary-fixed/20 blur-3xl pointer-events-none"></div>
            </div>

            <!-- SEARCH & FILTER SECTION (DIPINDAH KE ATAS SEBELUM PRODUK KOSONG) -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-surface-container-lowest p-4 rounded-2xl shadow-sm border border-surface-container">
                <!-- Search Input -->
                <div class="relative w-full md:w-80">
                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">search</span>
                    <input type="text" placeholder="Cari katun organik..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                </div>

                <!-- Age Filter Pills & Sorting -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                        <button class="px-3 py-2 rounded-xl bg-primary text-on-primary font-semibold text-xs shrink-0">Semua</button>
                        <button class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs shrink-0 transition-colors">0-3 Bulan</button>
                        <button class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs shrink-0 transition-colors">3-6 Bulan</button>
                        <button class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs shrink-0 transition-colors">6-12 Bulan</button>
                        <button class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs shrink-0 transition-colors">1-3 Tahun</button>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="relative">
                        <select class="h-11 pl-3 pr-8 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 appearance-none cursor-pointer font-medium">
                            <option>Terbaru</option>
                            <option>Terlama</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-2.5 top-3 text-outline text-sm pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>

            <!-- KATALOG PRODUK KOSONG / PLACEHOLDER (DIKELOLA DARI ADMIN) -->
            <div class="w-full bg-surface-container-lowest rounded-3xl p-16 text-center shadow-sm border border-surface-container flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">styler</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-1">Belum Ada Produk</h3>
                
                
            </div>

            <!-- TRUST BADGES SECTION -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-surface-container-lowest p-6 rounded-3xl shadow-sm border border-surface-container">
                <div class="flex items-center gap-4 p-3">
                    <div class="w-12 h-12 rounded-2xl bg-secondary-fixed/40 text-secondary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">eco</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-on-surface">100% Katun Organik</h4>
                        <p class="text-[11px] text-on-surface-variant">Sertifikasi OEKO-TEX Standard 100 bebas pestisida & zat kimia berbahaya untuk kulit si kecil.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-3">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">clean_hands</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-on-surface">Higienis & Tersegel</h4>
                            <p class="text-[11px] text-on-surface-variant">Dikemas dalam steril-bag berstandar medical-grade sebelum dikirim ke rumah Anda.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="w-full bg-surface-container-low py-12 border-t border-surface-container">
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
                    <li><a href="#" class="hover:text-on-surface transition-colors">Koleksi Bayi Baru Lahir</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Baju Tidur Organik</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Aksesoris & Topi Bayi</a></li>
                    <li><a href="#" class="hover:text-on-surface transition-colors">Paket Bundling Lahiran</a></li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-on-surface uppercase tracking-wider">Bantuan</h4>
                <ul class="space-y-1.5 text-xs text-on-surface-variant">
                    <li><a href="#" class="hover:text-on-surface transition-colors">Panduan Ukuran</a></li>
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

</body>
</html>

