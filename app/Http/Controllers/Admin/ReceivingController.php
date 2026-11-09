<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductStock;
use App\Models\Product;
use Illuminate\Http\Request;

class ReceivingController extends Controller
{
    /**
     * Tampilkan Daftar Catatan Pesanan untuk Penerimaan Barang (Fitur 11)
     */
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'targetLocation', 'items.variant.product', 'items.product'])
                               ->latest()
                               ->paginate(10);

        return view('Admin.penerimaan.index', compact('orders'));
    }

    /**
     * Tampilkan Detail Catatan Pesanan & Form Penerimaan Barang di Bawahnya (Fitur 11)
     */
    public function show($id)
    {
        $order = PurchaseOrder::with(['supplier', 'targetLocation', 'items.variant.product', 'items.product'])->findOrFail($id);

        return view('Admin.penerimaan.form', compact('order'));
    }

    /**
     * Simpan Data Penerimaan Barang, Tambah Stok Varian Produk & Cek Peringatan Stok Minimum (Fitur 11)
     */
    public function store(Request $request, $id)
    {
        $order = PurchaseOrder::with(['items.variant.product', 'items.product'])->findOrFail($id);

        $validated = $request->validate([
            'received_date' => 'required|date',
            'items'         => 'required|array',
            'items.*.received_qty' => 'required|integer|min:0',
        ]);

        $targetLocationId = $order->target_location_id;
        $lowStockAlerts = [];

        foreach ($order->items as $item) {
            $receivedQty = (int) ($validated['items'][$item->id]['received_qty'] ?? 0);

            // Update jumlah barang yang diterima pada item
            $item->quantity_received += $receivedQty;
            $item->save();

            // Tambah stok varian produk ke lokasi tujuan
            if ($targetLocationId && $receivedQty > 0 && $item->product_variant_id) {
                $stock = ProductStock::firstOrCreate(
                    ['product_variant_id' => $item->product_variant_id, 'location_id' => $targetLocationId],
                    ['quantity' => 0]
                );
                $stock->increment('quantity', $receivedQty);
            }

            // Cek Peringatan Stok Minimum (Diagram 4: Apakah stok mencapai batas minimum?)
            $product = $item->product ? $item->product->fresh() : ($item->variant?->product->fresh());
            if ($product && $product->total_stock <= $product->min_stock) {
                $lowStockAlerts[] = [
                    'name'        => $product->name,
                    'total_stock' => $product->total_stock,
                    'min_stock'   => $product->min_stock,
                    'is_empty'    => $product->total_stock <= 0,
                ];
            }
        }

        // Tandai pesanan sebagai diterima ('received')
        $order->status = 'received';
        $order->received_date = $validated['received_date'];
        $order->save();

        // Redirect ke detail hasil penerimaan barang
        return redirect()->route('admin.penerimaan.show', $order->id)
                         ->with('success', 'Penerimaan barang berhasil diproses! Stok produk otomatis bertambah.')
                         ->with('low_stock_alerts', $lowStockAlerts);
    }
}
