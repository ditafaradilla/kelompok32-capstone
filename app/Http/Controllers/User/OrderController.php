<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Tampilkan halaman instruksi pembayaran dan unggah bukti transfer / QRIS.
     */
    public function pembayaran(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        $order->load(['payment', 'items.variant', 'items.product']);

        return view('User.pesanan.pembayaran', compact('order'));
    }

    /**
     * Proses unggah atau perbarui bukti pembayaran oleh pelanggan.
     */
    public function uploadBukti(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        $request->validate([
            'bukti_bayar' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:3072',
        ], [
            'bukti_bayar.required' => 'File bukti pembayaran wajib diunggah.',
            'bukti_bayar.file'     => 'File bukti pembayaran tidak valid.',
            'bukti_bayar.mimes'    => 'Format file harus berupa JPG, PNG, WebP, atau PDF.',
            'bukti_bayar.max'      => 'Ukuran file bukti pembayaran maksimal 3 MB.',
        ]);

        $payment = $order->payment;
        if (!$payment) {
            return back()->with('error', 'Informasi pembayaran tidak ditemukan.');
        }

        // Hapus file bukti lama jika ada
        if (!empty($payment->bukti_bayar) && Storage::disk('public')->exists($payment->bukti_bayar)) {
            Storage::disk('public')->delete($payment->bukti_bayar);
        }

        $path = $request->file('bukti_bayar')->store('payment-proofs', 'public');

        $payment->bukti_bayar = $path;
        $payment->status_pembayaran = Payment::STATUS_MENUNGGU_VERIFIKASI;
        $payment->tanggal_bayar = now();
        $payment->save();

        $order->status_pesanan = Order::STATUS_MENUNGGU_VERIFIKASI;
        $order->save();

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Mohon menunggu verifikasi dari admin.');
    }

    /**
     * Tampilkan detail pesanan pelanggan dan status terkininya.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        $order->load(['payment', 'items.variant', 'items.product']);

        return view('User.pesanan.show', compact('order'));
    }

    /**
     * Batalkan pesanan oleh pelanggan selama masih menunggu pembayaran, kembalikan stok ke toko.
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        if ($order->status_pesanan !== Order::STATUS_MENUNGGU_PEMBAYARAN) {
            return back()->with('error', 'Pesanan ini tidak dapat dibatalkan karena sudah diproses atau status tidak sesuai.');
        }

        DB::transaction(function () use ($order) {
            $order->load('items');

            // Kembalikan stok item pesanan ke lokasi toko
            foreach ($order->items as $item) {
                $stockRecord = ProductStock::where('product_variant_id', $item->product_variant_id)
                    ->whereHas('location', fn($q) => $q->where('type', 'toko'))
                    ->orderByDesc('quantity')
                    ->lockForUpdate()
                    ->first();

                if ($stockRecord) {
                    $stockRecord->quantity += $item->jumlah;
                    $stockRecord->save();
                } else {
                    $storeLocation = Location::where('type', 'toko')->first();
                    if ($storeLocation) {
                        ProductStock::create([
                            'product_variant_id' => $item->product_variant_id,
                            'location_id'        => $storeLocation->id,
                            'quantity'           => $item->jumlah,
                        ]);
                    }
                }
            }

            $order->status_pesanan = Order::STATUS_DIBATALKAN;
            $order->save();

            if ($order->payment) {
                $order->payment->status_pembayaran = Payment::STATUS_DITOLAK;
                $order->payment->save();
            }
        });

        return back()->with('success', 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan ke toko.');
    }
}

