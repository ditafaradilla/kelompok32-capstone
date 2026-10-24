<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby Corner - Profil Saya</title>
    
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

    <!-- HEADER / NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-20 max-w-[1280px] mx-auto px-4 lg:px-8 flex items-center justify-between gap-4">
            
            <!-- Left: Brand Logo & Links -->
            <div class="flex items-center gap-8">
                <a class="flex items-center gap-2" href="{{ route('dashboard') }}">
                    <span class="font-bold text-lg text-on-surface tracking-tight">Baby Corner</span>
                </a>
                <nav class="hidden lg:flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="text-xs text-on-surface-variant hover:text-on-surface transition-colors">Beranda</a>
                    <a href="#" class="text-xs text-on-surface-variant hover:text-on-surface transition-colors">Katalog Produk</a>
                    <a href="#" class="text-xs text-on-surface-variant hover:text-on-surface transition-colors">Panduan Ukuran</a>
                </nav>
            </div>

            <!-- Right: Icons & User Profile with Dynamic Initials -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 pl-2">
                    <div class="flex flex-col text-right">
                        <span class="text-xs font-semibold text-on-surface leading-tight">
                            {{ Auth::user()->name ?? 'User' }}
                        </span>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xs hover:opacity-90 transition-opacity shadow-sm overflow-hidden" title="Profil Saya">
                        @php
                            $nameVal = Auth::user()->name ?? 'User';
                            $words = explode(' ', trim($nameVal));
                            $initials = '';
                            if (count($words) >= 2) {
                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($nameVal, 0, 2));
                            }
                        @endphp
                        @if(!empty(Auth::user()->photo))
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto" class="w-full h-full object-cover" />
                        @else
                            {{ $initials }}
                        @endif
                    </a>
                </div>
            </div>

        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="w-full pt-28 pb-16 flex-1 bg-background flex flex-col items-center">
        <div class="w-full max-w-3xl px-4 lg:px-8 mx-auto flex flex-col gap-8">
            
            <!-- Page Title Section -->
            <div class="text-center space-y-1">
                <h1 class="text-3xl font-bold text-on-surface tracking-tight">Profil Saya</h1>
                <p class="text-xs text-on-surface-variant">Informasi data akun dan si kecil untuk kebutuhan pesanan Anda.</p>
            </div>

            @if(session('success'))
                <div class="p-4 rounded-xl bg-green-100 text-green-700 text-xs font-semibold text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Profile Form Card -->
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest rounded-3xl p-6 sm:p-10 shadow-sm border border-surface-container space-y-8">
                @csrf

                <!-- 1. Foto Profil -->
                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-surface-container-low border border-surface-container">
                    <div class="relative shrink-0">
                        <!-- Profile Avatar / Image Container -->
                        <div id="profile-avatar-container" class="w-20 h-20 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xl shadow-xs overflow-hidden">
                            <span id="profile-initials" class="{{ !empty(Auth::user()->photo) ? 'hidden' : '' }}">{{ $initials }}</span>
                            <img id="profile-preview" src="{{ !empty(Auth::user()->photo) ? asset('storage/' . Auth::user()->photo) : '' }}" alt="Pratinjau Foto" class="w-full h-full object-cover {{ empty(Auth::user()->photo) ? 'hidden' : '' }}" />
                        </div>
                    </div>
                    <div class="flex flex-col items-center sm:items-start text-center sm:text-left space-y-1">
                        <h3 class="text-sm font-bold text-on-surface">{{ Auth::user()->name ?? 'User' }}</h3>
                        <p class="text-[11px] text-on-surface-variant">Format JPG, PNG atau WebP (maks. 3 MB)</p>
                        
                        <!-- Hidden File Input -->
                        <input type="file" id="photo-input" name="photo" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewPhoto(event)" />

                        <div class="flex items-center gap-3 pt-1">
                            <button type="button" onclick="document.getElementById('photo-input').click()" class="text-primary font-semibold text-xs hover:underline cursor-pointer">Ganti Foto</button>
                            <button type="button" id="btn-delete-photo" onclick="deletePhoto()" {{ empty(Auth::user()->photo) ? 'disabled' : '' }} class="{{ empty(Auth::user()->photo) ? 'text-outline/40 cursor-not-allowed' : 'text-outline hover:text-error cursor-pointer' }} font-semibold text-xs transition-colors">Hapus</button>
                        </div>
                    </div>
                </div>

                <!-- 2. Data Pribadi -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-2 text-outline">
                        <span class="material-symbols-outlined text-primary text-base">person</span>
                        Data Pribadi
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Lengkap</label>
                            <div class="relative flex items-center">
                                <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">badge</span>
                                <input type="text" name="name" value="{{ old('name', Auth::user()->name ?? '') }}" placeholder="Masukkan nama orang tua..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1.5">Alamat Email</label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">mail</span>
                                    <input type="email" name="email" value="{{ old('email', Auth::user()->email ?? '') }}" placeholder="Masukkan alamat email..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1.5">Nomor Telepon</label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">call</span>
                                    <input type="tel" name="phone" value="{{ old('phone', Auth::user()->phone ?? '') }}" placeholder="Masukkan nomor telepon..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Alamat Rumah</label>
                            <div class="relative flex items-start">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">home</span>
                                <textarea name="address" rows="3" placeholder="Masukkan alamat lengkap rumah..." class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 resize-none">{{ old('address', Auth::user()->address ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Informasi Si Kecil -->
                <div class="space-y-4 pt-4 border-t border-surface-container">
                    <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-2 text-outline">
                        <span class="material-symbols-outlined text-secondary text-base">child_care</span>
                        Informasi Si Kecil
                    </h3>

                    <div class="p-4 rounded-2xl bg-surface-container-low space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Panggilan Si Kecil</label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">sentiment_satisfied</span>
                                    <input type="text" name="baby_name" value="{{ old('baby_name', Auth::user()->baby_name ?? '') }}" placeholder="Masukkan nama panggilan si kecil..." class="w-full h-11 pl-10 pr-4 bg-surface-container-lowest text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1.5">Jenis Kelamin</label>
                                <div class="grid grid-cols-2 gap-2 h-11">
                                    
                                    <!-- Opsi Laki-laki -->
                                    <label class="flex items-center justify-center gap-2 rounded-xl {{ (old('gender', Auth::user()->gender ?? '') == 'laki-laki') ? 'bg-primary-fixed text-on-primary-fixed shadow-xs' : 'bg-surface-container-lowest text-on-surface-variant' }} hover:bg-surface-container cursor-pointer text-xs font-semibold select-none transition-all">
                                        <input type="radio" name="gender" value="laki-laki" class="hidden peer" {{ (old('gender', Auth::user()->gender ?? '') == 'laki-laki') ? 'checked' : '' }} />
                                        <span class="material-symbols-outlined text-sm">male</span>
                                        <span>Laki-laki</span>
                                    </label>
                                    
                                    <!-- Opsi Perempuan -->
                                    <label class="flex items-center justify-center gap-2 rounded-xl {{ (old('gender', Auth::user()->gender ?? '') == 'perempuan') ? 'bg-tertiary-fixed text-on-tertiary-fixed shadow-xs' : 'bg-surface-container-lowest text-on-surface-variant' }} hover:bg-surface-container cursor-pointer text-xs font-semibold select-none transition-all">
                                        <input type="radio" name="gender" value="perempuan" class="hidden peer" {{ (old('gender', Auth::user()->gender ?? '') == 'perempuan') ? 'checked' : '' }} />
                                        <span class="material-symbols-outlined text-sm">female</span>
                                        <span>Perempuan</span>
                                    </label>

                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Usia Saat Ini</label>
                            <div class="relative flex items-center">
                                <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">calendar_month</span>
                                <select name="baby_age" class="w-full h-11 pl-10 pr-8 bg-surface-container-lowest text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 appearance-none">
                                    <option value="" disabled {{ empty(old('baby_age', Auth::user()->baby_age ?? '')) ? 'selected' : '' }}>Pilih usia si kecil</option>
                                    <option value="0 -3 Bulan" {{ (old('baby_age', Auth::user()->baby_age ?? '') == '0 -3 Bulan') ? 'selected' : '' }}>0 -3 Bulan</option>
                                    <option value="4 - 6 Bulan" {{ (old('baby_age', Auth::user()->baby_age ?? '') == '4 - 6 Bulan') ? 'selected' : '' }}>4 - 6 Bulan</option>
                                    <option value="6 - 12 Bulan" {{ (old('baby_age', Auth::user()->baby_age ?? '') == '6 - 12 Bulan') ? 'selected' : '' }}>6 - 12 Bulan</option>
                                    <option value="1 - 3 Tahun" {{ (old('baby_age', Auth::user()->baby_age ?? '') == '1 - 3 Tahun') ? 'selected' : '' }}>1 - 3 Tahun</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 text-outline text-sm pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-semibold text-on-surface">Berat Badan</label>
                                    <span class="text-[10px] text-outline">Opsional</span>
                                </div>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">monitor_weight</span>
                                    <input type="text" name="baby_weight" value="{{ old('baby_weight', Auth::user()->baby_weight ?? '') }}" placeholder="Contoh: 6.5" class="w-full h-11 pl-10 pr-10 bg-surface-container-lowest text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                    <span class="absolute right-3.5 text-xs text-outline font-medium">kg</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-semibold text-on-surface">Tinggi Badan</label>
                                    <span class="text-[10px] text-outline">Opsional</span>
                                </div>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">height</span>
                                    <input type="text" name="baby_height" value="{{ old('baby_height', Auth::user()->baby_height ?? '') }}" placeholder="Contoh: 65" class="w-full h-11 pl-10 pr-10 bg-surface-container-lowest text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                                    <span class="absolute right-3.5 text-xs text-outline font-medium">cm</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Action Buttons (Centered at Bottom) -->
                <div class="flex items-center justify-center gap-3 pt-6">
                    <a href="{{ route('dashboard') }}" class="px-8 h-11 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface font-semibold text-xs flex items-center justify-center transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-10 h-11 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-semibold text-xs flex items-center justify-center shadow-md transition-all cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

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
            <p>© 2025 Baby Corner. Hak cipta dilindungi undang-undang. Dirancang dengan kelembutan untuk ibu & buah hati.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-on-surface transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-on-surface transition-colors">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

    <!-- JavaScript untuk Interaksi Preview Foto & Tombol Hapus -->
    <script>
        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('profile-preview');
                    const initialsSpan = document.getElementById('profile-initials');
                    const deleteBtn = document.getElementById('btn-delete-photo');

                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    initialsSpan.classList.add('hidden');

                    deleteBtn.removeAttribute('disabled');
                    deleteBtn.className = "text-outline hover:text-error font-semibold text-xs transition-colors cursor-pointer";
                }
                reader.readAsDataURL(file);
            }
        }

        function deletePhoto() {
            const photoInput = document.getElementById('photo-input');
            const previewImg = document.getElementById('profile-preview');
            const initialsSpan = document.getElementById('profile-initials');
            const deleteBtn = document.getElementById('btn-delete-photo');

            photoInput.value = "";
            previewImg.src = "";
            previewImg.classList.add('hidden');
            initialsSpan.classList.remove('hidden');

            deleteBtn.setAttribute('disabled', 'true');
            deleteBtn.className = "text-outline/40 cursor-not-allowed font-semibold text-xs transition-colors";
        }
    </script>

    <!-- Script agar card pilihan jenis kelamin langsung berganti warna saat diklik -->
    <script>
        document.querySelectorAll('input[name="gender"]').forEach((radio) => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('input[name="gender"]').forEach((el) => {
                    let parentLabel = el.closest('label');
                    parentLabel.classList.remove('bg-primary-fixed', 'text-on-primary-fixed', 'bg-tertiary-fixed', 'text-on-tertiary-fixed', 'shadow-xs');
                    parentLabel.classList.add('bg-surface-container-lowest', 'text-on-surface-variant');
                });
                
                let activeLabel = this.closest('label');
                activeLabel.classList.remove('bg-surface-container-lowest', 'text-on-surface-variant');
                if(this.value === 'laki-laki') {
                    activeLabel.classList.add('bg-primary-fixed', 'text-on-primary-fixed', 'shadow-xs');
                } else {
                    activeLabel.classList.add('bg-tertiary-fixed', 'text-on-tertiary-fixed', 'shadow-xs');
                }
            });
        });
    </script>

</body>
</html>