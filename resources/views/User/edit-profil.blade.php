@extends('User.layout')

@section('title', 'Baby Corner - Profil Saya')

@section('content')
<div class="w-full max-w-3xl px-4 lg:px-8 mx-auto flex flex-col gap-8 pb-16">
    
    <!-- Page Title Section -->
    <div class="text-center space-y-1">
        <h1 class="text-3xl font-bold text-on-surface tracking-tight">Profil Saya</h1>
        <p class="text-xs text-on-surface-variant">Informasi data akun dan si kecil untuk kebutuhan pesanan Anda.</p>
    </div>

    <!-- Profile Form Card -->
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest rounded-3xl p-6 sm:p-10 shadow-sm border border-surface-container space-y-8">
        @csrf

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
                
                <!-- Hidden Inputs -->
                <input type="file" id="photo-input" name="photo" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewPhoto(event)" />
                <input type="hidden" name="remove_photo" id="remove-photo-input" value="0" />

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

        <!-- 4. Action Buttons -->
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
@endpush
