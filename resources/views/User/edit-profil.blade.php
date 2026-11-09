@extends('User.layout')

@section('title', 'Baby Corner - Profil & Data Bayi')

@section('content')
<div class="w-full max-w-3xl px-4 lg:px-8 mx-auto flex flex-col gap-8 pb-16">
    
    <!-- Page Header -->
    <div class="text-center space-y-1">
        <h1 class="text-3xl font-bold text-on-surface tracking-tight">Profil &amp; Data Bayi</h1>
        <p class="text-xs text-on-surface-variant">Kelola data akun, kata sandi, dan informasi si kecil untuk rekomendasi ukuran pesanan Anda.</p>
    </div>

    @php
        $baby = $user->baby ?? Auth::user()->baby;
        $nameVal = $user->name ?? Auth::user()->name ?? 'User';
        $words = explode(' ', trim($nameVal));
        $initials = '';
        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $initials = strtoupper(substr($nameVal, 0, 2));
        }
        $redirectParam = request('redirect');
    @endphp

    <!-- ========================================== -->
    <!-- KARTU 1: DATA AKUN & PROFIL PELANGGAN     -->
    <!-- ========================================== -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 shadow-sm border border-surface-container space-y-6">
        <div class="flex items-center gap-2 border-b border-surface-container pb-4">
            <span class="material-symbols-outlined text-primary text-xl">person</span>
            <h2 class="text-base font-bold text-on-surface">Data Akun &amp; Profil</h2>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($redirectParam)
                <input type="hidden" name="redirect" value="{{ $redirectParam }}" />
            @endif

            <!-- Foto Profil -->
            <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-surface-container-low border border-surface-container">
                <div class="relative shrink-0">
                    <div id="profile-avatar-container" class="w-20 h-20 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xl shadow-xs overflow-hidden">
                        <span id="profile-initials" class="{{ !empty($user->photo) ? 'hidden' : '' }}">{{ $initials }}</span>
                        <img id="profile-preview" src="{{ !empty($user->photo) ? asset('storage/' . $user->photo) : '' }}" alt="Pratinjau Foto" class="w-full h-full object-cover {{ empty($user->photo) ? 'hidden' : '' }}" />
                    </div>
                </div>
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left space-y-1">
                    <h3 class="text-sm font-bold text-on-surface">{{ $user->name }}</h3>
                    <p class="text-[11px] text-on-surface-variant">Format JPG, PNG, atau WebP (maks. 3 MB)</p>
                    
                    <input type="file" id="photo-input" name="photo" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewPhoto(event)" />
                    <input type="hidden" name="remove_photo" id="remove-photo-input" value="0" />

                    <div class="flex items-center gap-3 pt-1">
                        <button type="button" onclick="document.getElementById('photo-input').click()" class="text-primary font-semibold text-xs hover:underline cursor-pointer">
                            Ganti Foto
                        </button>
                        <button type="button" id="btn-delete-photo" onclick="deletePhoto()" {{ empty($user->photo) ? 'disabled' : '' }} class="{{ empty($user->photo) ? 'text-outline/40 cursor-not-allowed' : 'text-outline hover:text-error cursor-pointer' }} font-semibold text-xs transition-colors">
                            Hapus Foto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Data Pribadi -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Lengkap</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">badge</span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Masukkan nama lengkap Anda..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Alamat Email</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">mail</span>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="Masukkan alamat email..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Nomor Telepon</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">call</span>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789" class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Alamat Rumah</label>
                    <div class="relative flex items-start">
                        <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-sm">home</span>
                        <textarea name="address" rows="3" placeholder="Masukkan alamat lengkap pengiriman..." class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0 resize-none">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="px-6 h-10 rounded-full bg-primary hover:bg-surface-tint text-on-primary font-semibold text-xs flex items-center justify-center shadow-xs transition-all cursor-pointer">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- KARTU 2: UBAH KATA SANDI                   -->
    <!-- ========================================== -->
    <div class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 shadow-sm border border-surface-container space-y-6">
        <div class="flex items-center gap-2 border-b border-surface-container pb-4">
            <span class="material-symbols-outlined text-primary text-xl">lock_reset</span>
            <h2 class="text-base font-bold text-on-surface">Ubah Kata Sandi</h2>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1.5">Kata Sandi Saat Ini</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">key</span>
                    <input type="password" name="current_password" required placeholder="Masukkan kata sandi lama Anda..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Kata Sandi Baru</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">lock</span>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">check_circle</span>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi kata sandi baru..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="px-6 h-10 rounded-full bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-semibold text-xs flex items-center justify-center transition-all cursor-pointer">
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- KARTU 3: DATA BAYI (TERPISAH)             -->
    <!-- ========================================== -->
    <div id="kartu-data-bayi" class="bg-surface-container-lowest rounded-3xl p-6 sm:p-8 shadow-sm border border-surface-container space-y-6">
        <div class="flex items-center justify-between border-b border-surface-container pb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">child_care</span>
                <h2 class="text-base font-bold text-on-surface">Data Bayi</h2>
            </div>
            @if($baby)
                <span class="px-2.5 py-1 rounded-full bg-secondary-container/50 text-secondary text-[11px] font-bold">
                    Terdaftar
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full bg-surface-container text-on-surface-variant text-[11px] font-medium">
                    Belum Diisi
                </span>
            @endif
        </div>

        @if($baby)
            <!-- Ringkasan Tampilan Data Bayi -->
            <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold text-on-surface">{{ $baby->nama_bayi }}</span>
                        @if($baby->jenis_kelamin === 'laki-laki')
                            <span class="px-2 py-0.5 rounded-md bg-primary-fixed text-on-primary-fixed text-[11px] font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">male</span> Laki-laki
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-tertiary-fixed text-on-tertiary-fixed text-[11px] font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">female</span> Perempuan
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-on-surface-variant font-medium">
                        Usia: <strong class="text-on-surface font-semibold">{{ $baby->usia_bulan }} Bulan</strong>
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 text-xs border-t border-surface-container/60">
                    <div>
                        <span class="text-[10px] text-outline block">Tanggal Lahir</span>
                        <span class="font-medium text-on-surface">{{ $baby->tanggal_lahir ? $baby->tanggal_lahir->format('d M Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-outline block">Berat Badan</span>
                        <span class="font-medium text-on-surface">{{ number_format($baby->berat_badan, 2) }} kg</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-outline block">Tinggi Badan</span>
                        <span class="font-medium text-on-surface">{{ number_format($baby->tinggi_badan, 1) }} cm</span>
                    </div>
                </div>
            </div>

            <!-- Form Edit Data Bayi -->
            <form action="{{ route('bayi.update', $baby) }}" method="POST" class="space-y-5 pt-2">
                @csrf
                @method('PUT')
                @if($redirectParam)
                    <input type="hidden" name="redirect" value="{{ $redirectParam }}" />
                @endif

                <h3 class="text-xs font-bold text-outline uppercase tracking-wider">Ubah Data Bayi</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Panggilan Si Kecil *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">sentiment_satisfied</span>
                            <input type="text" name="nama_bayi" value="{{ old('nama_bayi', $baby->nama_bayi) }}" required placeholder="Nama panggilan..." class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Jenis Kelamin *</label>
                        <div class="grid grid-cols-2 gap-2 h-11">
                            <label class="flex items-center justify-center gap-2 rounded-xl {{ old('jenis_kelamin', $baby->jenis_kelamin) == 'laki-laki' ? 'bg-primary-fixed text-on-primary-fixed shadow-xs' : 'bg-surface-container-low text-on-surface-variant' }} hover:bg-surface-container cursor-pointer text-xs font-semibold select-none transition-all">
                                <input type="radio" name="jenis_kelamin" value="laki-laki" class="hidden baby-gender-radio" {{ old('jenis_kelamin', $baby->jenis_kelamin) == 'laki-laki' ? 'checked' : '' }} />
                                <span class="material-symbols-outlined text-sm">male</span>
                                <span>Laki-laki</span>
                            </label>
                            
                            <label class="flex items-center justify-center gap-2 rounded-xl {{ old('jenis_kelamin', $baby->jenis_kelamin) == 'perempuan' ? 'bg-tertiary-fixed text-on-tertiary-fixed shadow-xs' : 'bg-surface-container-low text-on-surface-variant' }} hover:bg-surface-container cursor-pointer text-xs font-semibold select-none transition-all">
                                <input type="radio" name="jenis_kelamin" value="perempuan" class="hidden baby-gender-radio" {{ old('jenis_kelamin', $baby->jenis_kelamin) == 'perempuan' ? 'checked' : '' }} />
                                <span class="material-symbols-outlined text-sm">female</span>
                                <span>Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Tanggal Lahir *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">calendar_today</span>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $baby->tanggal_lahir ? $baby->tanggal_lahir->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" min="{{ now()->subYears(5)->format('Y-m-d') }}" required class="w-full h-11 pl-10 pr-3 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Berat Badan (kg) *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">monitor_weight</span>
                            <input type="number" step="0.01" min="0.5" max="30" name="berat_badan" value="{{ old('berat_badan', $baby->berat_badan) }}" required placeholder="Contoh: 6.5" class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                            <span class="absolute right-3.5 text-xs text-outline font-medium">kg</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Tinggi Badan (cm) *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">height</span>
                            <input type="number" step="0.1" min="30" max="130" name="tinggi_badan" value="{{ old('tinggi_badan', $baby->tinggi_badan) }}" required placeholder="Contoh: 63" class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                            <span class="absolute right-3.5 text-xs text-outline font-medium">cm</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                    <button type="submit" class="px-6 h-10 rounded-full bg-secondary hover:bg-secondary/90 text-on-secondary font-semibold text-xs flex items-center justify-center shadow-xs transition-all cursor-pointer">
                        Simpan Perubahan Data Bayi
                    </button>
                </div>
            </form>

            <!-- Tombol Hapus Data Bayi Terpisah -->
            <div class="pt-4 border-t border-surface-container flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-error">Hapus Data Bayi</p>
                    <p class="text-[11px] text-on-surface-variant">Setelah dihapus, rekomendasi ukuran otomatis di halaman produk akan dinonaktifkan.</p>
                </div>
                <form action="{{ route('bayi.destroy', $baby) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data si kecil? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    @if($redirectParam)
                        <input type="hidden" name="redirect" value="{{ $redirectParam }}" />
                    @endif
                    <button type="submit" class="px-4 h-9 rounded-full border border-error text-error hover:bg-error hover:text-on-error font-semibold text-xs transition-all cursor-pointer flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">delete</span>
                        <span>Hapus data bayi</span>
                    </button>
                </form>
            </div>

        @else
            <!-- Form Tambah Data Bayi Baru (Belum ada data bayi) -->
            <div class="p-4 rounded-2xl bg-surface-container-low border border-secondary/20 mb-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-secondary text-lg mt-0.5">info</span>
                <div class="text-xs text-on-surface-variant">
                    <strong class="text-on-surface font-semibold block mb-0.5">Dapatkan Rekomendasi Ukuran Otomatis</strong>
                    Isi data si kecil di bawah ini agar sistem Baby Corner dapat merekomendasikan ukuran baju yang pas dan akurat saat berbelanja.
                </div>
            </div>

            <form action="{{ route('bayi.store') }}" method="POST" class="space-y-5">
                @csrf
                @if($redirectParam)
                    <input type="hidden" name="redirect" value="{{ $redirectParam }}" />
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Panggilan Si Kecil *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">sentiment_satisfied</span>
                            <input type="text" name="nama_bayi" value="{{ old('nama_bayi') }}" required placeholder="Contoh: Adiba" class="w-full h-11 pl-10 pr-4 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Jenis Kelamin *</label>
                        <div class="grid grid-cols-2 gap-2 h-11">
                            <label class="flex items-center justify-center gap-2 rounded-xl {{ old('jenis_kelamin', 'laki-laki') == 'laki-laki' ? 'bg-primary-fixed text-on-primary-fixed shadow-xs' : 'bg-surface-container-low text-on-surface-variant' }} hover:bg-surface-container cursor-pointer text-xs font-semibold select-none transition-all">
                                <input type="radio" name="jenis_kelamin" value="laki-laki" class="hidden baby-gender-radio" {{ old('jenis_kelamin', 'laki-laki') == 'laki-laki' ? 'checked' : '' }} />
                                <span class="material-symbols-outlined text-sm">male</span>
                                <span>Laki-laki</span>
                            </label>
                            
                            <label class="flex items-center justify-center gap-2 rounded-xl {{ old('jenis_kelamin') == 'perempuan' ? 'bg-tertiary-fixed text-on-tertiary-fixed shadow-xs' : 'bg-surface-container-low text-on-surface-variant' }} hover:bg-surface-container cursor-pointer text-xs font-semibold select-none transition-all">
                                <input type="radio" name="jenis_kelamin" value="perempuan" class="hidden baby-gender-radio" {{ old('jenis_kelamin') == 'perempuan' ? 'checked' : '' }} />
                                <span class="material-symbols-outlined text-sm">female</span>
                                <span>Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Tanggal Lahir *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">calendar_today</span>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" max="{{ date('Y-m-d') }}" min="{{ now()->subYears(5)->format('Y-m-d') }}" required class="w-full h-11 pl-10 pr-3 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Berat Badan (kg) *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">monitor_weight</span>
                            <input type="number" step="0.01" min="0.5" max="30" name="berat_badan" value="{{ old('berat_badan') }}" required placeholder="Contoh: 6.5" class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                            <span class="absolute right-3.5 text-xs text-outline font-medium">kg</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1.5">Tinggi Badan (cm) *</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-outline text-sm">height</span>
                            <input type="number" step="0.1" min="30" max="130" name="tinggi_badan" value="{{ old('tinggi_badan') }}" required placeholder="Contoh: 63" class="w-full h-11 pl-10 pr-10 bg-surface-container-low text-on-surface text-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-primary border-0" />
                            <span class="absolute right-3.5 text-xs text-outline font-medium">cm</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="submit" class="px-6 h-10 rounded-full bg-secondary hover:bg-secondary/90 text-on-secondary font-semibold text-xs flex items-center justify-center shadow-xs transition-all cursor-pointer">
                        Simpan Data Bayi
                    </button>
                </div>
            </form>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('profile-preview');
                const initialsSpan = document.getElementById('profile-initials');
                const deleteBtn = document.getElementById('btn-delete-photo');
                const removeInput = document.getElementById('remove-photo-input');

                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                initialsSpan.classList.add('hidden');
                if (removeInput) removeInput.value = "0";

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
        const removeInput = document.getElementById('remove-photo-input');

        photoInput.value = "";
        previewImg.src = "";
        previewImg.classList.add('hidden');
        initialsSpan.classList.remove('hidden');
        if (removeInput) removeInput.value = "1";

        deleteBtn.setAttribute('disabled', 'true');
        deleteBtn.className = "text-outline/40 cursor-not-allowed font-semibold text-xs transition-colors";
    }

    document.querySelectorAll('.baby-gender-radio').forEach((radio) => {
        radio.addEventListener('change', function() {
            const parentContainer = this.closest('.grid');
            if (!parentContainer) return;

            parentContainer.querySelectorAll('label').forEach((label) => {
                label.classList.remove('bg-primary-fixed', 'text-on-primary-fixed', 'bg-tertiary-fixed', 'text-on-tertiary-fixed', 'shadow-xs');
                label.classList.add('bg-surface-container-low', 'text-on-surface-variant');
            });
            
            const activeLabel = this.closest('label');
            activeLabel.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
            if (this.value === 'laki-laki') {
                activeLabel.classList.add('bg-primary-fixed', 'text-on-primary-fixed', 'shadow-xs');
            } else {
                activeLabel.classList.add('bg-tertiary-fixed', 'text-on-tertiary-fixed', 'shadow-xs');
            }
        });
    });
</script>
@endpush
