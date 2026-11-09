<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\LocationStockController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReceivingController;

use App\Http\Controllers\Admin\BazaarAnalysisController;
use App\Http\Controllers\Admin\FinanceReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ForecastController;
use App\Http\Controllers\Admin\SalesDashboardController;
use App\Http\Controllers\User\CatalogController;
use App\Http\Controllers\User\SizeGuideController;
use App\Http\Middleware\EnsureUserIsAdmin;

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

Route::get('/', function () {
    return view('Auth.welcome');
});

Route::get('/login', function () {
    return view('Auth.welcome');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    return back()->withErrors([
        'email' => 'Email atau password yang Anda masukkan salah.',
    ])->onlyInput('email');
});

Route::get('/register', function () {
    return view('Auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'user',
    ]);

    return redirect()->to('/')->with('success', 'Registrasi berhasil! Silakan masuk.');
});


/*
|--------------------------------------------------------------------------
| 2. USER DASHBOARD & CATALOG ROUTES (Persiapan B)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CatalogController::class, 'home'])->name('dashboard');
    Route::get('/produk', [CatalogController::class, 'index'])->name('produk.index');
    Route::get('/produk/{product}', [CatalogController::class, 'show'])->name('produk.show');
    Route::get('/panduan-ukuran', [SizeGuideController::class, 'index'])->name('panduan.ukuran');
});


/*
|--------------------------------------------------------------------------
| 3. ADMIN BACKOFFICE & INVENTORY ROUTES (Fitur 8, 9, 10, 11)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 3. ADMIN BACKOFFICE & INVENTORY ROUTES (Fitur 8, 9, 10, 11)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Halaman Dashboard Admin
    Route::get('/dashboard', [
        SalesDashboardController::class,
        'home'
    ])->name('dashboard');

    // Fitur 8: Kelola Produk dan Pemisahan Stok Toko
    Route::resource('produk', ProductController::class);

    // Fitur 9: Pencatatan Lokasi dan Stok
    Route::get('/lokasi-stok', [LocationStockController::class, 'index'])->name('lokasi.index');
    Route::post('/lokasi-stok/lokasi', [LocationStockController::class, 'storeLocation'])->name('lokasi.store');
    Route::post('/lokasi-stok/update-stok', [LocationStockController::class, 'updateStock'])->name('lokasi.updateStock');

    // Fitur 10: Pemesanan Barang ke Pemasok
    Route::get('/pemesanan-barang', [PurchaseOrderController::class, 'index'])->name('pemesanan.index');
    Route::get('/pemesanan-barang/buat', [PurchaseOrderController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan-barang', [PurchaseOrderController::class, 'store'])->name('pemesanan.store');
    Route::get('/pemesanan-barang/{id}', [PurchaseOrderController::class, 'show'])->name('pemesanan.show');

    // Fitur 11: Penerimaan Barang & Peringatan Stok Habis
    Route::get('/penerimaan-barang', [ReceivingController::class, 'index'])->name('penerimaan.index');
    Route::get('/penerimaan-barang/{id}', [ReceivingController::class, 'show'])->name('penerimaan.show');
    Route::post('/penerimaan-barang/{id}', [ReceivingController::class, 'store'])->name('penerimaan.store');
});


/*
|--------------------------------------------------------------------------
| 4. EDIT PROFILE ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/edit-profil', function () {
    return view('User.edit-profil');
})->name('profile.edit')->middleware('auth');

Route::post('/dashboard/edit-profil', function (Request $request) {
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
        'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
    ]);

    $user = Auth::user();

    if ($request->boolean('remove_photo') || $request->input('remove_photo') === '1') {
        if (!empty($user->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
        }
        $user->photo = null;
    } elseif ($request->hasFile('photo')) {
        if (!empty($user->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
        }

        $path = $request->file('photo')->store('profiles', 'public');
        $user->photo = $path;
    }

    $user->name        = $request->name;
    $user->email       = $request->email;
    $user->phone       = $request->phone;
    $user->address     = $request->address;
    $user->baby_name   = $request->baby_name;
    $user->gender      = $request->gender;
    $user->baby_age    = $request->baby_age;
    $user->baby_weight = $request->baby_weight;
    $user->baby_height = $request->baby_height;

    $user->save();

    // Sinkronisasi sementara ke tabel babies (FRI-01)
    if ($request->filled('baby_name')) {
        $months = 0;
        if (!empty($request->baby_age) && preg_match('/(\d+)/', $request->baby_age, $matches)) {
            $months = (int) $matches[1];
        }

        $birthDate = \Carbon\Carbon::now()->subMonths($months)->toDateString();
        if ($request->filled('tanggal_lahir')) {
            $birthDate = $request->tanggal_lahir;
        }

        $cleanWeight = !empty($request->baby_weight) ? (float) str_replace(',', '.', preg_replace('/[^\d.,]/', '', $request->baby_weight)) : 5.0;
        $cleanHeight = !empty($request->baby_height) ? (float) str_replace(',', '.', preg_replace('/[^\d.,]/', '', $request->baby_height)) : 55.0;
        $gender = ($request->gender === 'perempuan') ? 'perempuan' : 'laki-laki';

        \App\Models\Baby::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_bayi'     => $request->baby_name,
                'tanggal_lahir' => $birthDate,
                'jenis_kelamin' => $gender,
                'berat_badan'   => $cleanWeight,
                'tinggi_badan'  => $cleanHeight,
            ]
        );
    }

    // Validasi redirect hanya ke URL internal (tolak redirect eksternal)
    $redirectUrl = $request->input('redirect');
    if ($redirectUrl && is_string($redirectUrl)) {
        $parsed = parse_url($redirectUrl);
        if (!isset($parsed['host']) && str_starts_with($redirectUrl, '/') && !str_starts_with($redirectUrl, '//')) {
            return redirect($redirectUrl)->with('success', 'Profil dan data si kecil berhasil disimpan!');
        }
    }

    return redirect()->route('profile.edit')->with('success', 'Perubahan profil berhasil disimpan!');
})->name('profile.update')->middleware('auth');


/*
|--------------------------------------------------------------------------
| 5. LOGOUT ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
})->name('logout')->middleware('auth');


/*
|--------------------------------------------------------------------------
| 6. KEUANGAN - PEMBUKUAN & LAPORAN KEUANGAN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin/keuangan')
    ->name('admin.keuangan.')
    ->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/laporan', [FinanceReportController::class, 'index'])->name('laporan');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::patch('/{transaction}/lunas', [TransactionController::class, 'markPaid'])->name('lunas');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });


/*
|--------------------------------------------------------------------------
| 7. BUSINESS INTELLIGENCE - BAZAR & FORECASTING
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin/analisis')
    ->name('admin.bi.')
    ->group(function () {

        // Analisis Bazar & Produk
        Route::get('/bazar', [
            BazaarAnalysisController::class,
            'index'
        ])->name('bazar');

        // Analisis Forecast
        Route::get('/forecast', [
            ForecastController::class,
            'index'
        ])->name('forecast');

    });