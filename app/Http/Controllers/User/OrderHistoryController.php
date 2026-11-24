<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderHistoryController extends Controller
{
    /**
     * Tampilkan riwayat transaksi pembelian milik pelanggan yang login (FRI-06).
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Query dasar seluruh pesanan milik pengguna
        $baseUserOrders = Order::where('user_id', $userId);

        // Ringkasan pesanan pengguna:
        // Jumlah total pesanan pengguna
        $totalPesanan = (clone $baseUserOrders)->count();

        // Total belanja: HANYA menghitung pesanan dengan status diproses, dikirim, dan selesai
        $totalBelanja = (clone $baseUserOrders)
            ->whereIn('status_pesanan', [
                Order::STATUS_DIPROSES,
                Order::STATUS_DIKIRIM,
                Order::STATUS_SELESAI,
            ])
            ->sum('total_harga');

        // Jumlah pesanan yang belum dibayar
        $pesananMenunggu = (clone $baseUserOrders)
            ->where('status_pesanan', Order::STATUS_MENUNGGU_PEMBAYARAN)
            ->count();

        // Query daftar pesanan dengan filter & pencarian
        $query = Order::where('user_id', $userId)
            ->with(['payment', 'items.variant', 'items.product'])
            ->latest('id');

        // Filter status pesanan (Semua, Menunggu Pembayaran, Menunggu Verifikasi, Diproses, Dikirim, Selesai, Dibatalkan)
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status_pesanan', $request->status);
        }

        // Pencarian nomor pesanan
        $search = $request->input('search', $request->input('q'));
        if (!empty($search)) {
            $query->where('nomor_pesanan', 'like', '%' . trim($search) . '%');
        }

        // Filter rentang tanggal pesanan
        $startDate = $request->input('tanggal_mulai', $request->input('dari_tanggal'));
        $endDate   = $request->input('tanggal_akhir', $request->input('sampai_tanggal'));

        if (!empty($startDate)) {
            $query->whereDate('tanggal_pesanan', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('tanggal_pesanan', '<=', $endDate);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('User.riwayat.index', compact(
            'orders',
            'totalPesanan',
            'totalBelanja',
            'pesananMenunggu'
        ));
    }
}

