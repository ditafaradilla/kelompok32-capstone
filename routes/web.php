<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes - Baby Corner
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 1. AUTHENTICATION ROUTES (Login & Register)
|--------------------------------------------------------------------------
*/

// Tampilkan Halaman Utama
Route::get('/', function () {
    return view('Auth.welcome');
});

// Tampilkan Halaman Login & Memberikan Nama Route 'login'
Route::get('/login', function () {
    return view('Auth.welcome');
})->name('login');

// Proses Aksi Login (POST) dengan Auth::attempt() & Pengecekan Role Admin/User
Route::post('/login', function (Request $request) {
    // 1. Validasi input login
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // 2. Coba proses autentikasi
    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $request->session()->regenerate();

        // 3. Cek Role (Admin atau User biasa)
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    // Jika gagal login, kembali ke halaman sebelumnya dengan pesan error
    return back()->withErrors([
        'email' => 'Email atau password yang Anda masukkan salah.',
    ])->onlyInput('email');
});

// Tampilkan Halaman Registrasi
Route::get('/register', function () {
    return view('Auth.register');
})->name('register');

// Proses Simpan Data Registrasi ke Database (phpMyAdmin)
Route::post('/register', function (Request $request) {
    // 1. Validasi input dari form register
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    // 2. Simpan data baru ke tabel 'users' di database
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password), // Enkripsi password
        'role' => 'user', // Default role sebagai user biasa
    ]);

    // 3. Arahkan kembali ke halaman utama/login dengan pesan sukses
    return redirect()->to('/')->with('success', 'Registrasi berhasil! Silakan masuk.');
});


/*
|--------------------------------------------------------------------------
| 2. USER DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/

// Halaman Dashboard Pengguna / User
Route::get('/dashboard', function () {
    return view('User.beranda-user');
})->name('dashboard')->middleware('auth');


/*
|--------------------------------------------------------------------------
| 3. ADMIN DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/

// Halaman Dashboard Admin / Backoffice
Route::get('/admin/dashboard', function () {
    return view('Admin.beranda-admin');
})->name('admin.dashboard')->middleware('auth');


/*
|--------------------------------------------------------------------------
| 4. EDIT PROFILE ROUTES
|--------------------------------------------------------------------------
*/

// Halaman Edit Profil Pengguna (Tampilkan Form - GET)
Route::get('/dashboard/edit-profil', function () {
    return view('User.edit-profil');
})->name('profile.edit')->middleware('auth');

// Proses Simpan Perubahan Profil (Terima Data Form & Update Database - POST)
Route::post('/dashboard/edit-profil', function (Request $request) {
    // 1. Validasi input form profil
    $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        'phone'       => 'nullable|string|max:20',
        'address'     => 'nullable|string',
        'baby_name'   => 'nullable|string|max:255',
        'gender'      => 'nullable|string|in:laki-laki,perempuan',
        'baby_age'    => 'nullable|string|max:50',
        'baby_weight' => 'nullable|string|max:50',
        'baby_height' => 'nullable|string|max:50',
        'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072', // Maksimal 3MB
    ]);

    // 2. Ambil user yang sedang aktif login
    $user = Auth::user();

    // 3. Handle upload foto profil jika ada file yang dikirim
    if ($request->hasFile('photo')) {
        // Hapus foto lama jika ada
        if (!empty($user->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
        }
        $path = $request->file('photo')->store('profiles', 'public');
        $user->photo = $path;
    }

    // 4. Update data ke database
    $user->name         = $request->name;
    $user->email        = $request->email;
    $user->phone        = $request->phone;
    $user->address      = $request->address;
    $user->baby_name    = $request->baby_name;
    $user->gender       = $request->gender;
    $user->baby_age     = $request->baby_age;
    $user->baby_weight  = $request->baby_weight;
    $user->baby_height  = $request->baby_height;

    $user->save();

    // 5. Redirect kembali dengan pesan sukses
    return redirect()->route('profile.edit')->with('success', 'Perubahan profil berhasil disimpan!');
})->name('profile.update')->middleware('auth');

/*
|--------------------------------------------------------------------------
| 5. LOGOUT ROUTES
|--------------------------------------------------------------------------
*/

// Proses Logout Pengguna
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
})->name('logout')->middleware('auth');