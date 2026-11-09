<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman formulir checkout dan ringkasan pesanan.
     */
    public function index(CartService $cartService)
    {
        $user = Auth::user();
        $summary = $cartService->summary($user);

        if ($summary['items']->isEmpty()) {
            return redirect()->route('keranjang.index')
                             ->with('error', 'Keranjang belanja Anda masih kosong. Silakan pilih produk terlebih dahulu.');
        }

        if ($summary['has_stock_issue']) {
            return redirect()->route('keranjang.index')
                             ->with('error', 'Terdapat produk yang melebihi stok toko yang tersedia. Harap sesuaikan jumlah terlebih dahulu.');
        }

        return view('User.checkout', compact('summary', 'user'));
    }

    /**
     * Proses pembuatan pesanan baru, pengurangan stok toko, dan inisialisasi pembayaran.
     */
    public function store(Request $request, CartService $cartService)
    {
        $validated = $request->validate([
            'nama_penerima'      => 'required|string|max:255',
            'telepon_penerima'   => 'required|string|max:20',
            'alamat_pengiriman'  => 'required|string',
            'metode_pembayaran'  => 'required|in:transfer_bank,qris',
            'catatan'            => 'nullable|string|max:500',
        ], [
            'nama_penerima.required'     => 'Nama penerima wajib diisi.',
            'telepon_penerima.required'  => 'Nomor telepon penerima wajib diisi.',
            'alamat_pengiriman.required' => 'Alamat pengiriman wajib diisi.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran.in'       => 'Pilihan metode pembayaran tidak valid.',
        ]);

        try {
            $order = DB::transaction(function () use ($validated, $cartService) {
                $user = Auth::user();

                // Kunci dan ambil item keranjang
                $cartItems = CartItem::with(['variant.product'])
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Keranjang belanja Anda kosong.');
                }

                $totalHarga = 0;

                // 1. Verifikasi dan kunci stok seluruh item di lokasi toko
                foreach ($cartItems as $item) {
                    $variant = ProductVariant::with(['product'])
                        ->lockForUpdate()
                        ->find($item->product_variant_id);

                    if (!$variant) {
                        throw new \Exception('Varian produk tidak ditemukan.');
                    }

                    $storeStocks = ProductStock::where('product_variant_id', $variant->id)
                        ->whereHas('location', fn($q) => $q->where('type', 'toko'))
                        ->lockForUpdate()
                        ->get();

                    $availableQty = $storeStocks->sum('quantity');

                    if ($availableQty < $item->jumlah) {
                        $prodName = $variant->product?->name ?? 'Produk';
                        $label = ($variant->ukuran ?: '-') . '/' . ($variant->warna ?: '-');
                        throw new \Exception("Stok untuk produk '{$prodName}' ({$label}) tidak mencukupi (tersedia {$availableQty} pcs, diminta {$item->jumlah} pcs).");
                    }

                    // Hitung total harga murni dari database
                    $unitPrice = (int) ($variant->product?->price ?? 0);
                    $totalHarga += ($unitPrice * $item->jumlah);
                }

                // 2. Buat record Order
                $order = Order::create([
                    'user_id'           => $user->id,
                    'nomor_pesanan'     => Order::generateNomorPesanan(),
                    'tanggal_pesanan'   => now(),
                    'total_harga'       => $totalHarga,
                    'alamat_pengiriman' => $validated['alamat_pengiriman'],
                    'nama_penerima'     => $validated['nama_penerima'],
                    'telepon_penerima'  => $validated['telepon_penerima'],
                    'catatan'           => $validated['catatan'] ?? null,
                    'ongkir'            => 0,
                    'status_pesanan'    => Order::STATUS_MENUNGGU_PEMBAYARAN,
                ]);

                // 3. Simpan item pesanan & potong stok dari lokasi toko terbesar
                foreach ($cartItems as $item) {
                    $variant = $item->variant;
                    $product = $variant->product;
                    $unitPrice = (int) $product->price;
                    $variantLabel = ($variant->ukuran ?: '-') . ' / ' . ($variant->warna ?: '-');

                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_variant_id' => $variant->id,
                        'product_id'         => $product->id,
                        'jumlah'             => $item->jumlah,
                        'harga_satuan'       => $unitPrice,
                        'nama_produk'        => $product->name,
                        'varian_label'       => $variantLabel,
                    ]);

                    // Pengurangan stok toko (mulai dari lokasi dengan stok terbanyak)
                    $remainingToDeduct = $item->jumlah;
                    $stockRecords = ProductStock::where('product_variant_id', $variant->id)
                        ->whereHas('location', fn($q) => $q->where('type', 'toko'))
                        ->orderByDesc('quantity')
                        ->lockForUpdate()
                        ->get();

                    foreach ($stockRecords as $stockRecord) {
                        if ($remainingToDeduct <= 0) {
                            break;
                        }
                        $deduct = min($stockRecord->quantity, $remainingToDeduct);
                        $stockRecord->quantity -= $deduct;
                        $stockRecord->save();
                        $remainingToDeduct -= $deduct;
                    }
                }

                // 4. Inisialisasi record Payment (status belum_bayar)
                Payment::create([
                    'order_id'          => $order->id,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'jumlah_bayar'      => $totalHarga,
                    'status_pembayaran' => Payment::STATUS_BELUM_BAYAR,
                ]);

                // 5. Kosongkan keranjang belanja pengguna
                $cartService->clear($user);

                return $order;
            });

            return redirect()->route('pesanan.pembayaran', $order->id)
                             ->with('success', 'Pesanan Anda berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            return redirect()->route('keranjang.index')
                             ->with('error', $e->getMessage());
        }
    }
}

