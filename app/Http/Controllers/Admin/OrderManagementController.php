<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends Controller
{
    /**
     * Tampilkan daftar pesanan pelanggan (dengan pencarian dan filter status).
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'payment', 'items.variant', 'items.product'])->latest('tanggal_pesanan');

        // Filter status pesanan
        if ($request->filled('status')) {
            $query->where('status_pesanan', $request->status);
        }

        // Pencarian nomor pesanan atau nama penerima
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                  ->orWhere('nama_penerima', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Hitung statistik untuk badge ringkasan
        $counts = [
            'total'                => Order::count(),
            'menunggu_pembayaran' => Order::where('status_pesanan', Order::STATUS_MENUNGGU_PEMBAYARAN)->count(),
            'menunggu_verifikasi' => Order::where('status_pesanan', Order::STATUS_MENUNGGU_VERIFIKASI)->count(),
            'diproses'             => Order::where('status_pesanan', Order::STATUS_DIPROSES)->count(),
            'selesai'              => Order::where('status_pesanan', Order::STATUS_SELESAI)->count(),
            'dibatalkan'           => Order::where('status_pesanan', Order::STATUS_DIBATALKAN)->count(),
        ];

        return view('Admin.pesanan.index', compact('orders', 'counts'));
    }

    /**
     * Tampilkan detail pesanan pelanggan, bukti pembayaran, dan data pengiriman.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'payment', 'items.variant', 'items.product']);

        return view('Admin.pesanan.show', compact('order'));
    }

    /**
     * Verifikasi bukti pembayaran, ubah status ke diproses, dan catat transaksi pemasukan (idempotent).
     */
    public function verify(Order $order)
    {
        $payment = $order->payment;
        if (!$payment || $payment->status_pembayaran !== Payment::STATUS_MENUNGGU_VERIFIKASI) {
            return back()->with('error', 'Pesanan tidak dalam status menunggu verifikasi pembayaran.');
        }

        DB::transaction(function () use ($order, $payment) {
            // 1. Perbarui status pembayaran & pesanan
            $payment->status_pembayaran = Payment::STATUS_LUNAS;
            $payment->save();

            $order->status_pesanan = Order::STATUS_DIPROSES;
            $order->save();

            // 2. Catat tepat 1 transaksi pemasukan di tabel transactions (idempotent)
            $exists = Transaction::where('reference', $order->nomor_pesanan)->exists();
            if (!$exists) {
                $method = ($payment->metode_pembayaran === Payment::METODE_QRIS) ? 'QRIS' : 'Transfer Bank';

                Transaction::create([
                    'transaction_date' => date('Y-m-d'),
                    'type'             => Transaction::TYPE_INCOME,
                    'category'         => 'Penjualan Online',
                    'amount'           => $order->total_harga,
                    'payment_status'   => Transaction::STATUS_PAID,
                    'payment_method'   => $method,
                    'reference'        => $order->nomor_pesanan,
                    'description'      => "Penjualan online pesanan #{$order->nomor_pesanan} ({$order->nama_penerima})",
                    'created_by'       => Auth::id(),
                ]);
            }
        });

        return back()->with('success', "Pesanan #{$order->nomor_pesanan} berhasil diverifikasi dan diproses. Pemasukan telah dicatat di pembukuan.");
    }

    /**
     * Tolak bukti pembayaran dengan catatan admin, kembalikan status ke menunggu pembayaran.
     */
    public function reject(Request $request, Order $order)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ], [
            'catatan_admin.required' => 'Alasan / catatan penolakan bukti pembayaran wajib diisi.',
            'catatan_admin.max'      => 'Catatan admin maksimal 500 karakter.',
        ]);

        $payment = $order->payment;
        if (!$payment) {
            return back()->with('error', 'Informasi pembayaran tidak ditemukan.');
        }

        $payment->status_pembayaran = Payment::STATUS_DITOLAK;
        $payment->catatan_admin = $request->catatan_admin;
        $payment->save();

        $order->status_pesanan = Order::STATUS_MENUNGGU_PEMBAYARAN;
        $order->save();

        return back()->with('success', "Bukti pembayaran pesanan #{$order->nomor_pesanan} ditolak. Pesanan dikembalikan ke status menunggu pembayaran.");
    }
}

