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
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\User\CatalogController;
use App\Http\Controllers\User\SizeGuideController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\BabyController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\OrderController;
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

    // Keranjang Belanja (FRI-03)
    Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('keranjang.store');
    Route::delete('/keranjang', [CartController::class, 'clear'])->name('keranjang.clear');
    Route::patch('/keranjang/{item}', [CartController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{item}', [CartController::class, 'destroy'])->name('keranjang.destroy');

    // Checkout & Pemesanan Pelanggan (FRI-04)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan/{order}/pembayaran', [OrderController::class, 'pembayaran'])->name('pesanan.pembayaran');
    Route::post('/pesanan/{order}/pembayaran', [OrderController::class, 'uploadBukti'])->name('pesanan.uploadBukti');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('pesanan.show');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('pesanan.cancel');
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

    // Fitur FRI-04: Pengelolaan Pesanan Pelanggan & Verifikasi Pembayaran
    Route::get('/pesanan', [OrderManagementController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{order}', [OrderManagementController::class, 'show'])->name('pesanan.show');
    Route::post('/pesanan/{order}/verifikasi', [OrderManagementController::class, 'verify'])->name('pesanan.verify');
    Route::post('/pesanan/{order}/tolak', [OrderManagementController::class, 'reject'])->name('pesanan.reject');
});


/*
|--------------------------------------------------------------------------
| 4. PROFILE & BABY ROUTES (FRI-02)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/edit-profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/dashboard/edit-profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/dashboard/edit-profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Data Bayi
    Route::post('/bayi', [BabyController::class, 'store'])->name('bayi.store');
    Route::put('/bayi/{baby}', [BabyController::class, 'update'])->name('bayi.update');
    Route::delete('/bayi/{baby}', [BabyController::class, 'destroy'])->name('bayi.destroy');
});


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