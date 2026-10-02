<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby Corner - Login</title>
    
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
<body class="bg-surface text-on-surface antialiased min-h-screen flex flex-col justify-between">

    <!-- HEADER / NAVBAR -->
    <header class="fixed top-0 w-full z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-16 max-w-[1280px] mx-auto px-4 lg:px-8 flex items-center justify-between gap-4">
            
            <!-- Left: Brand Logo & Tagline -->
            <div class="flex items-center gap-4">
                <a class="flex items-center gap-2" href="{{ url('/') }}">
                    <span class="font-bold text-lg text-on-surface tracking-tight">Baby Corner</span>
                </a>
                <span class="hidden md:inline-block w-px h-4 bg-outline-variant/40 mx-1"></span>
                <span class="hidden md:inline-block text-xs text-on-surface-variant">Every Little Step, Gently Cared For</span>
            </div>

            <!-- Center: Navigation Links -->
            <nav class="hidden md:flex items-center gap-2">
                <a href="#" class="px-4 py-1.5 bg-secondary-container text-on-secondary-container font-semibold rounded-full text-xs">Login</a>
                <a href="{{ route('register') }}" class="text-xs text-on-surface-variant hover:text-on-surface px-4 py-1.5 transition-colors">Register</a>
                
            </nav>

            <!-- User Profile Icon -->
            <div class="flex items-center gap-3">
                
            </div>

        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="w-full pt-20 pb-12 flex-1 bg-surface flex flex-col items-center justify-center">
        <div class="w-full max-w-[1240px] px-4 sm:px-8 mx-auto flex items-center justify-center">
            
            <!-- MAIN CARD CONTAINER -->
            <div class="w-full bg-surface-container-lowest rounded-3xl shadow-[0_16px_40px_-12px_rgba(33,101,134,0.08)] overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
                
                <!-- LEFT PANEL: NURSERY SHOWCASE -->
                <div class="lg:col-span-6 relative flex flex-col justify-between p-6 sm:p-10 overflow-hidden bg-gradient-to-br from-surface-container to-surface-variant/70">
                    
                    <!-- Background Image & Gradient -->
                    <div class="absolute inset-0 z-0">
                        <img alt="Nursery room background" class="w-full h-full object-cover filter brightness-[0.95]" src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&q=80&w=1000">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    </div>

                    <!-- Top Badges -->
                    <div class="relative z-10 flex items-center justify-between gap-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/90 backdrop-blur-md shadow-sm">
                            <span class="material-symbols-outlined text-secondary text-sm">verified</span>
                            <span class="text-xs text-on-surface font-semibold">Parent-Verified Quality</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/80 backdrop-blur-md text-primary text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-secondary-fixed-dim animate-pulse"></span>
                            Spring Baby Fair Live
                        </div>
                    </div>

                    <!-- Middle Content & Testimonial Card -->
                    <div class="relative z-10 my-6">
                        <span class="text-[10px] uppercase tracking-widest text-primary-fixed-dim font-bold block mb-1">GENTLE CARE ESSENTIALS</span>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight mb-2">
                            Safe & Cozy Essentials for Your Little One
                        </h1>
                        <p class="text-xs sm:text-sm text-white/90 max-w-md mb-6">
                            Join over 25,000+ mindful parents who trust Baby Corner for certified cribs, organic sleepwear, and non-toxic nursery care.
                        </p>

                        <!-- Testimonial Box -->
                        <div class="p-4 rounded-2xl bg-white/95 backdrop-blur-md max-w-md shadow-md">
                            <div class="flex items-center gap-1 text-amber-500 mb-1">
                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="text-[11px] text-on-surface-variant font-medium ml-1">5.0 Star Certified Review</span>
                            </div>
                            <p class="text-xs text-on-surface italic mb-2">
                                “The best nursery shopping experience! Softest organic fabrics, quick doorstep delivery, and total peace of mind.”
                            </p>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-primary font-semibold">Sarah M. — Mom of 6-month-old Maya</span>
                                <span class="material-symbols-outlined text-secondary text-sm">favorite</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Security Chips -->
                    <div class="relative z-10 flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1 px-3 py-1 rounded-full bg-white/90 text-[11px] font-medium text-secondary shadow-sm">
                            <span class="material-symbols-outlined text-xs">shield</span> 100% Certified Safe
                        </div>
                        <div class="flex items-center gap-1 px-3 py-1 rounded-full bg-white/90 text-[11px] font-medium text-primary shadow-sm">
                            <span class="material-symbols-outlined text-xs">eco</span> BPA & Toxic Free
                        </div>
                        <div class="flex items-center gap-1 px-3 py-1 rounded-full bg-white/90 text-[11px] font-medium text-tertiary shadow-sm">
                            <span class="material-symbols-outlined text-xs">cycle</span> 30-Day Easy Returns
                        </div>
                    </div>

                </div>

                <!-- RIGHT PANEL: LOGIN FORM -->
                <div class="lg:col-span-6 flex flex-col justify-center p-6 sm:p-12 bg-surface-container-lowest">
                    <div class="max-w-[400px] w-full mx-auto">
                        
                        <!-- Header Text -->
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-on-surface tracking-tight">Welcome Back!</h2>
                            <p class="text-xs text-on-surface-variant mt-1">Please enter your details to access your nursery wishlist, registry, and active orders.</p>
                        </div>

                        <!-- Form Input -->
                        <form class="space-y-4" method="POST" action="{{ url('/login') }}">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1.5">Email or Username</label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">mail</span>
                                    <input type="email" name="email" placeholder="name@example.com" required autofocus class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-semibold text-on-surface">Password</label>
                                    <a href="#" class="text-xs text-primary hover:underline">Forgot Password?</a>
                                </div>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">lock</span>
                                    <input type="password" name="password" placeholder="••••••••" required class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                    <span class="material-symbols-outlined absolute right-3.5 text-outline text-sm cursor-pointer">visibility</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <input type="checkbox" id="remember" name="remember" checked class="w-4 h-4 rounded text-primary focus:ring-primary accent-primary" />
                                <label for="remember" class="text-xs text-on-surface-variant select-none">Remember me</label>
                            </div>

                            <button type="submit" class="w-full h-11 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-semibold text-xs flex items-center justify-center gap-2 shadow-md transition-all mt-3">
                                <span>Sign In </span>
                                
                            </button>
                        </form>

                        <!-- Bottom Register Switch & Security Note -->
                        <div class="mt-6 text-center">
                            <p class="text-xs text-on-surface-variant">
                                New to our nursery family? 
                                <a href="{{ route('register') }}" class="text-primary font-bold hover:underline">Create an account</a>
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-surface-container flex items-center justify-center gap-1.5 text-[11px] text-outline">
                            <span class="material-symbols-outlined text-xs text-secondary">verified_user</span>
                            <span>256-bit SSL encrypted & child-safe privacy guaranteed</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="w-full bg-surface-container-low py-6">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-on-surface-variant">
            <p>© 2026 Baby Corner. Nurturing with warmth and trust.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-on-surface transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-on-surface transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-on-surface transition-colors">Customer Care</a>
            </div>
        </div>
    </footer>

</body>
</html>