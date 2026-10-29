<!DOCTYPE html>
<html lang="id" style="">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="web_dashboard" name="shell-type">
    <title>Baby Corner - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;1,400&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
            main>:first-child { margin-top: 0 !important; }
            main>:last-child { margin-bottom: 0 !important; }
        }
        ::-webkit-scrollbar { display: none; }
    </style>
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
                    spacing: {
                        "space-xs": "0.25rem",
                        "space-lg": "1.5rem",
                        "space-sm": "0.5rem",
                        "space-md": "1rem",
                        "space-xl": "2.5rem",
                        margin: "2rem",
                        "gutter-mobile": "1rem",
                        gutter: "1.5rem",
                        "margin-mobile": "1rem"
                    },
                    fontFamily: {
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "headline-sm": ["Plus Jakarta Sans"],
                        "display-hero": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "title-md": ["Plus Jakarta Sans"],
                        "display-hero-mobile": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "headline-lg-mobile": ["26px", { lineHeight: "34px", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "headline-sm": ["20px", { lineHeight: "28px", fontWeight: "600" }],
                        "display-hero": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "body-lg": ["16px", { lineHeight: "26px", fontWeight: "400" }],
                        "body-md": ["14px", { lineHeight: "22px", fontWeight: "400" }],
                        "title-md": ["18px", { lineHeight: "26px", fontWeight: "600" }],
                        "display-hero-mobile": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "700" }],
                        "label-md": ["13px", { lineHeight: "18px", letterSpacing: "0.01em", fontWeight: "600" }],
                        "headline-lg": ["36px", { lineHeight: "44px", letterSpacing: "-0.015em", fontWeight: "700" }],
                        "label-sm": ["11px", { lineHeight: "16px", letterSpacing: "0.02em", fontWeight: "600" }],
                        "headline-md": ["24px", { lineHeight: "32px", letterSpacing: "-0.01em", fontWeight: "600" }]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface font-body-md text-on-surface antialiased">

    <!-- ASIDE SIDEBAR (dipindah ke partial bersama) -->
    @include('Admin.partials.sidebar')

    <!-- WRAPPER MAIN & HEADER -->
    <div class="pl-72 flex flex-col min-h-screen">
        
        <!-- HEADER -->
        <header class="fixed top-0 left-72 right-0 h-20 bg-surface/85 backdrop-blur-xl z-40 shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
            <div class="h-20 w-full px-8 flex items-center justify-between gap-space-lg">
                <div class="flex-1 max-w-lg">
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-outline text-[20px]">search</span>
                        <input class="w-full h-11 pl-11 pr-4 bg-surface-container-lowest rounded-xl font-body-md text-body-md text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary-container/40 transition-all" placeholder="Cari pesanan, nama pakaian bayi, atau SKU..." type="search">
                    </div>
                </div>
                <div class="flex items-center gap-space-md">
                    <button class="flex items-center gap-2 px-space-md h-10 rounded-full bg-primary-container text-on-primary font-label-md text-label-md hover:bg-primary transition-all shadow-[0_2px_8px_-2px_rgba(74,85,104,0.04)]" type="button">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Tambah Produk</span>
                    </button>
                    <button aria-label="Notifikasi Pesanan" class="relative w-10 h-10 rounded-full bg-surface-container-lowest flex items-center justify-center text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all" type="button">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-tertiary-container"></span>
                    </button>
                    <div class="flex items-center gap-3 pl-2">
                        <div class="flex flex-col text-right">
                            <span class="font-label-md text-label-md font-semibold text-on-surface">{{ Auth::user()->name ?? 'Bunda Sarah' }}</span>
                            <span class="font-label-sm text-label-sm text-outline uppercase">Admin Baby Corner</span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 w-full pt-28 bg-surface px-8 pb-12">
            <div class="flex flex-col w-full gap-8">
                
                <!-- Welcome Banner Card -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-container">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-2.5 h-2.5 rounded-full bg-secondary"></span>
                            <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Sistem Backoffice Aktif</span>
                        </div>
                        <h1 class="font-headline-md text-headline-md text-on-surface font-semibold tracking-tight">Selamat Datang, {{ Auth::user()->name ?? 'Admin Baby Corner' }}</h1>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">Pantau performa penjualan busana bayi, inventaris stok katun organik, dan pesanan masuk hari ini.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-surface-container-low text-on-surface font-label-md text-label-md">
                            <span class="material-symbols-outlined text-[18px] text-primary">calendar_today</span>
                            <span>{{ date('d M Y') }}</span>
                        </div>
                    </div>
                </div>

              

        <!-- FOOTER -->
        <footer class="w-full bg-surface-container-low py-space-md">
            <div class="w-full px-8 flex flex-col md:flex-row items-center justify-between gap-2 text-on-surface-variant font-label-sm text-label-sm">
                <div>© 2026 Baby Corner. Hak Cipta Dilindungi. Backoffice E-Commerce Baju Bayi.</div>
                <div class="flex items-center gap-space-md"></div>
            </div>
        </footer>

    </div>

</body>
</html>