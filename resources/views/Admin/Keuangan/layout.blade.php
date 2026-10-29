<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title') - Baby Corner Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Token warna/font disalin dari beranda-admin.blade.php agar tampilan konsisten.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "surface": "#f9f9ff", "background": "#f9f9ff",
                        "surface-container": "#e7eeff", "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f0f3ff",
                        "on-surface": "#111c2c", "on-surface-variant": "#40484d",
                        "outline": "#71787e", "outline-variant": "#c0c7ce",
                        "primary": "#216586", "on-primary": "#ffffff",
                        "primary-container": "#6aa6ca", "on-primary-container": "#003a52",
                        "primary-fixed": "#c5e7ff", "on-primary-fixed": "#001e2d",
                        "secondary": "#2f694a", "secondary-container": "#b2f1c8", "on-secondary-container": "#356f50",
                        "tertiary-container": "#d18f75", "on-tertiary-container": "#562916",
                        "error": "#ba1a1a", "error-container": "#ffdad6", "on-error-container": "#93000a"
                    },
                    borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
                    spacing: { "space-xs": "0.25rem", "space-sm": "0.5rem", "space-md": "1rem", "space-lg": "1.5rem" },
                    fontFamily: {
                        "body-md": ["Plus Jakarta Sans"], "headline-md": ["Plus Jakarta Sans"],
                        "headline-sm": ["Plus Jakarta Sans"], "title-md": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"], "label-sm": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "headline-md": ["24px", { lineHeight: "32px", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "headline-sm": ["20px", { lineHeight: "28px", fontWeight: "600" }],
                        "title-md": ["18px", { lineHeight: "26px", fontWeight: "600" }],
                        "body-md": ["14px", { lineHeight: "22px", fontWeight: "400" }],
                        "label-md": ["13px", { lineHeight: "18px", letterSpacing: "0.01em", fontWeight: "600" }],
                        "label-sm": ["11px", { lineHeight: "16px", letterSpacing: "0.02em", fontWeight: "600" }]
                    }
                }
            }
        }
    </script>
    <style>::-webkit-scrollbar { display: none; } html, body { margin: 0; padding: 0; }</style>
</head>
<body class="bg-surface font-body-md text-on-surface antialiased">

@include('Admin.partials.sidebar')

<div class="pl-72 flex flex-col min-h-screen">
    <header class="fixed top-0 left-72 right-0 h-20 bg-surface/85 backdrop-blur-xl z-40 shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-20 w-full px-8 flex items-center justify-between">
            <div class="text-title-md font-semibold">@yield('title')</div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col text-right">
                    <span class="text-label-md text-on-surface">{{ Auth::user()->name }}</span>
                    <span class="text-label-sm text-outline uppercase">Admin Baby Corner</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full pt-28 bg-surface px-8 pb-12">
        @if (session('success'))
            <div class="mb-6 flex items-center gap-2 rounded-xl bg-secondary-container text-on-secondary-container px-4 py-3 text-label-md">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="w-full bg-surface-container-low py-4">
        <div class="px-8 text-on-surface-variant text-label-sm">© 2026 Baby Corner. Backoffice E-Commerce Baju Bayi.</div>
    </footer>
</div>
</body>
</html>