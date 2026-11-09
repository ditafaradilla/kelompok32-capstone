<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class LocationStockController extends Controller
{
    /**
     * Tampilkan Halaman Daftar Lokasi & Stok per Varian Lokasi (Fitur 9)
     */
    public function index(Request $request)
    {
        $locations = Location::with(['stocks.variant.product'])->get();

        // Lokasi yang sedang dipilih untuk "Kelola Stok"
        $selectedLocationId = $request->get('location_id', $locations->first()->id ?? null);
        $selectedLocation = $selectedLocationId ? Location::with('stocks.variant.product')->find($selectedLocationId) : null;
        
        // Seluruh varian produk yang aktif
        $allVariants = ProductVariant::with('product')->get();

        return view('Admin.lokasi-stok.index', compact('locations', 'selectedLocation', 'allVariants'));
    }

    /**
     * Simpan Lokasi Baru (Toko / Gudang)
     */
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:50|unique:locations,code',
            'type'    => 'required|in:toko,gudang',
            'address' => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $prefix = $validated['type'] === 'toko' ? 'TKO' : 'GDG';
            $validated['code'] = $prefix . '-' . str_pad(Location::where('type', $validated['type'])->count() + 1, 2, '0', STR_PAD_LEFT);
        }

        $location = Location::create($validated);

        return redirect()->route('admin.lokasi.index', ['location_id' => $location->id])
                         ->with('success', 'Lokasi baru "' . $location->name . '" berhasil ditambahkan!');
    }

    /**
     * Kelola / Perbarui Stok Varian Produk pada Lokasi Tertentu
     */
    public function updateStock(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'stocks'      => 'required|array',
            'stocks.*'    => 'required|integer|min:0',
        ]);

        $location = Location::findOrFail($validated['location_id']);

        foreach ($validated['stocks'] as $variantId => $qty) {
            ProductStock::updateOrCreate(
                ['product_variant_id' => $variantId, 'location_id' => $location->id],
                ['quantity'           => (int) $qty]
            );
        }

        return redirect()->route('admin.lokasi.index', ['location_id' => $location->id])
                         ->with('success', 'Stok varian produk di lokasi "' . $location->name . '" berhasil diperbarui!');
    }
}
