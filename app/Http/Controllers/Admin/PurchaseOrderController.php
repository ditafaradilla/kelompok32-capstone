<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Location;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Tampilkan Daftar Catatan Pemesanan Barang ke Pemasok (Fitur 10)
     */
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'targetLocation', 'items.variant.product', 'items.product'])
                               ->latest()
                               ->paginate(10);

        return view('Admin.pemesanan.index', compact('orders'));
    }

    /**
     * Form Buat Catatan Pemesanan Barang Baru ke Pemasok
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $variants = ProductVariant::with('product')->get();
        $locations = Location::all();

        return view('Admin.pemesanan.create', compact('suppliers', 'variants', 'locations'));
    }

    /**
     * Simpan Catatan Pemesanan Barang ke Pemasok per Varian
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'                => 'required|exists:suppliers,id',
            'order_date'                 => 'required|date',
            'target_location_id'         => 'required|exists:locations,id',
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.unit_cost'          => 'required|numeric|min:0',
        ]);

        // Auto-generate nomor PO: PO-YYYYMM-XXXX
        $poNumber = 'PO-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $totalAmount += ($item['quantity'] * $item['unit_cost']);
        }

        $order = PurchaseOrder::create([
            'po_number'          => $poNumber,
            'supplier_id'        => $validated['supplier_id'],
            'target_location_id' => $validated['target_location_id'],
            'order_date'         => $validated['order_date'],
            'status'             => 'pending',
            'notes'              => $validated['notes'] ?? null,
            'total_amount'       => $totalAmount,
        ]);

        foreach ($validated['items'] as $item) {
            $variant = ProductVariant::findOrFail($item['product_variant_id']);
            PurchaseOrderItem::create([
                'purchase_order_id'  => $order->id,
                'product_id'         => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity_ordered'   => $item['quantity'],
                'quantity_received'  => 0,
                'unit_cost'          => $item['unit_cost'],
                'subtotal'           => $item['quantity'] * $item['unit_cost'],
            ]);
        }

        return redirect()->route('admin.pemesanan.show', $order->id)
                         ->with('success', 'Catatan pemesanan barang ke pemasok (' . $poNumber . ') berhasil dibuat!');
    }

    /**
     * Detail Catatan Pemesanan
     */
    public function show($id)
    {
        $order = PurchaseOrder::with(['supplier', 'targetLocation', 'items.variant.product', 'items.product'])->findOrFail($id);

        return view('Admin.pemesanan.show', compact('order'));
    }
}
