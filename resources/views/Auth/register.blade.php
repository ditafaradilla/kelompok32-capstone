<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby Corner - Register</title>
    
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
                <a href="{{ url('/') }}" class="text-xs text-on-surface-variant hover:text-on-surface px-4 py-1.5 transition-colors">Login</a>
                <a href="#" class="px-4 py-1.5 bg-secondary-container text-on-secondary-container font-semibold rounded-full text-xs">Register</a>
                
            </nav>

            <!-- Right: Back to Shop & User Profile Icon -->
            <div class="flex items-center gap-3">
                
                
            </div>

        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="w-full pt-20 pb-12 flex-1 bg-surface flex flex-col items-center justify-center">
        <div class="w-full max-w-[1240px] px-4 sm:px-8 mx-auto flex flex-col">
            
            <!-- Breadcrumb & SSL Security Badge Row -->
            <div class="flex items-center justify-between mb-4 text-xs">
                <div class="flex items-center gap-1 text-on-surface-variant">
                    <span class="material-symbols-outlined text-sm text-primary">home</span>
                    <span>Home / <span class="text-on-surface font-semibold">Account Registration</span></span>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed/30 text-secondary text-[11px] font-semibold">
                    
                    
                </div>
            </div>

            <!-- MAIN CARD CONTAINER -->
            <div class="w-full bg-surface-container-lowest rounded-3xl shadow-[0_16px_40px_-12px_rgba(33,101,134,0.08)] overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                
                <!-- LEFT PANEL: BENEFITS & PERKS -->
                <div class="lg:col-span-5 relative flex flex-col justify-between p-6 sm:p-8 bg-gradient-to-br from-surface-container to-surface-variant/70 border-r border-surface-container">
                    
                    <div>
                        <!-- Top Privilege Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white shadow-sm mb-5">
                            <span class="material-symbols-outlined text-primary text-xs">verified</span>
                            <span class="text-[10px] uppercase font-bold text-primary tracking-wider">NEW PARENT PRIVILEGES</span>
                        </div>

                        <!-- Mini Nursery Banner Image -->
                        <div class="relative rounded-2xl overflow-hidden mb-6 shadow-md h-36">
                            <img src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&q=80&w=800" class="w-full h-full object-cover" alt="Nursery">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-3">
                                <span class="text-white text-xs font-semibold">Organic & Tested Materials</span>
                            </div>
                        </div>

                        <!-- Heading & Subtitle -->
                        <h2 class="text-xl font-bold text-on-surface mb-1">Begin Your Parenting Journey With Us</h2>
                        <p class="text-xs text-on-surface-variant mb-6">Create your free Baby Corner account to unlock member discounts, nursery registry, and fast order tracking.</p>

                        <!-- Benefit Cards List -->
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white/80 shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-secondary-fixed/40 text-secondary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-sm">redeem</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-on-surface">Welcome Gift Voucher</h4>
                                    <p class="text-[11px] text-on-surface-variant">Enjoy an instant 15% OFF on your first nursery or nursing bundle.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white/80 shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-sm">local_shipping</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-on-surface">Priority Express Dispatch</h4>
                                    <p class="text-[11px] text-on-surface-variant">Real-time SMS updates and doorstep sanitized delivery within 24 hours.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white/80 shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-tertiary-fixed/40 text-tertiary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-sm">child_friendly</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-on-surface">Milestone Care Advice</h4>
                                    <p class="text-[11px] text-on-surface-variant">Age-specific developmental toys & pediatric safety guides tailored to baby.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial avatars footer inside left panel -->
                    <div class="pt-4 border-t border-surface-container-highest flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full bg-primary text-white text-[10px] flex items-center justify-center font-bold border border-white">MA</div>
                                <div class="w-6 h-6 rounded-full bg-secondary text-white text-[10px] flex items-center justify-center font-bold border border-white">RI</div>
                                <div class="w-6 h-6 rounded-full bg-tertiary text-white text-[10px] flex items-center justify-center font-bold border border-white">SJ</div>
                            </div>
                            <div>
                                <div class="flex text-amber-500 text-[10px]">
                                    ★★★★★ <span class="text-on-surface font-bold ml-1">4.9/5</span>
                                </div>
                                <p class="text-[10px] text-on-surface-variant">Trusted by 25,000+ Happy Families</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-secondary text-lg">favorite</span>
                    </div>

                </div>

                <!-- RIGHT PANEL: REGISTRATION FORM -->
                <div class="lg:col-span-7 flex flex-col justify-center p-6 sm:p-10 bg-surface-container-lowest">
                    <div class="max-w-[520px] w-full mx-auto">
                        
                        <!-- Header & Step Indicator -->
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-on-surface tracking-tight">Join Baby Corner</h2>
                                <p class="text-xs text-on-surface-variant mt-0.5">Create your account to start shopping and tracking your orders.</p>
                            </div>
                            
                            
                        </div>

                        <!-- Registration Form -->
                        <form class="space-y-4" method="POST" action="{{ Route::has('register') ? route('register') : '#' }}">
                            @csrf

                            <!-- Full Name -->
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1">Full Name (Nama Lengkap) *</label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">person</span>
                                    <input type="text" name="name" placeholder="e.g. Maya Anderson" required autofocus class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                </div>
                                <span class="text-[10px] text-outline float-right mt-0.5">First & Last name</span>
                            </div>

                            <!-- Email & Phone Number Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Email Address *</label>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">mail</span>
                                        <input type="email" name="email" placeholder="mama@example.com" required class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-xs font-semibold text-on-surface">Phone Number *</label>
                                
                                    </div>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3.5 text-xs text-on-surface-variant font-medium flex items-center gap-1">🇮🇩 +62</span>
                                        <input type="tel" name="phone" placeholder="812-3456-7890" required class="w-full h-11 pl-16 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                    </div>
                                </div>
                            </div>

                            <!-- Password & Confirm Password Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Password (Kata Sandi) *</label>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">lock</span>
                                        <input type="password" name="password" placeholder="Min 8 characters" required class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                        <span class="material-symbols-outlined absolute right-3.5 text-outline text-sm cursor-pointer">visibility</span>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 text-[10px] text-outline">
                                        <span>Strength</span>
                                        <div class="w-24 h-1 bg-surface-container rounded-full overflow-hidden">
                                            <div class="w-1/2 h-full bg-secondary"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Confirm Password *</label>
                                    <div class="relative flex items-center">
                                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">lock_reset</span>
                                        <input type="password" name="password_confirmation" placeholder="Repeat your password" required class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                        <span class="material-symbols-outlined absolute right-3.5 text-outline text-sm cursor-pointer">visibility</span>
                                    </div>
                                    
                                </div>
                            </div>


                            <!-- Terms & Conditions Checkbox -->
                            <div class="flex items-start gap-2 pt-1">
                                <input type="checkbox" id="terms" name="terms" required class="w-4 h-4 mt-0.5 rounded text-primary focus:ring-primary accent-primary shrink-0" />
                                <label for="terms" class="text-[11px] text-on-surface-variant leading-relaxed select-none">
                                    I agree to Baby Corner's <a href="#" class="text-primary underline">Terms of Service</a> and <a href="#" class="text-primary underline">Privacy Policy</a>, and wish to receive comforting parenting tips, pediatric reminders, & exclusive member offers.
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="w-full h-11 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-semibold text-xs flex items-center justify-center gap-2 shadow-md transition-all mt-3">
                                <span>Create Account / Daftar Sekarang</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>
                        </form>

                        <!-- Login Switch Link -->
                        <div class="mt-6 text-center">
                            <p class="text-xs text-on-surface-variant">
                                Already have a Baby Corner account? 
                                <a href="{{ url('/') }}" class="text-primary font-bold hover:underline">Sign in here</a>
                            </p>
                        </div>

                    </div>
                </div>

            </div>

            <!-- 4 Trust Badges Footer Grid Below Card -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-lowest shadow-xs">
                    <span class="material-symbols-outlined text-secondary text-xl">verified_user</span>
                    <div>
                        <h5 class="text-xs font-bold text-on-surface">100% Authentic</h5>
                        <p class="text-[10px] text-on-surface-variant">Certified Nursery Gear</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-lowest shadow-xs">
                    <span class="material-symbols-outlined text-primary text-xl">local_shipping</span>
                    <div>
                        <h5 class="text-xs font-bold text-on-surface">Safe Delivery</h5>
                        <p class="text-[10px] text-on-surface-variant">Sterilized & Packaged</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-lowest shadow-xs">
                    <span class="material-symbols-outlined text-tertiary text-xl">sync</span>
                    <div>
                        <h5 class="text-xs font-bold text-on-surface">Easy 30-Day Return</h5>
                        <p class="text-[10px] text-on-surface-variant">Peace of mind for parents</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-lowest shadow-xs">
                    <span class="material-symbols-outlined text-secondary text-xl">support_agent</span>
                    <div>
                        <h5 class="text-xs font-bold text-on-surface">Parent Concierge</h5>
                        <p class="text-[10px] text-on-surface-variant">24/7 Caring Support</p>
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