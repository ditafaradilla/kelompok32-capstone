<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Location;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tampilkan Halaman Daftar Produk & Pemisahan Stok
     */
    public function index(Request $request)
    {
        $query = Product::with(['stocks.location']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->latest()->paginate(10);
        $locations = Location::all();
        $categories = Product::select('category')->distinct()->pluck('category');

        // Statistik Stok untuk Banner Ringkasan
        $totalProducts = Product::count();
        $allProducts = Product::with('stocks')->get();
        $lowStockCount = $allProducts->filter->is_low_stock->count();
        $outOfStockCount = $allProducts->filter->is_out_of_stock->count();

        return view('Admin.produk.index', compact(
            'products',
            'locations',
            'categories',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Form Tambah Produk Baru
     */
    public function create()
    {
        $locations = Location::all();
        return view('Admin.produk.create', compact('locations'));
    }

    /**
     * Simpan Data Produk Baru & Alokasi Stok Awal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'         => 'nullable|string|max:50|unique:products,sku',
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'size'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:50',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'min_stock'   => 'required|integer|min:0',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stocks'      => 'nullable|array',
            'stocks.*'    => 'nullable|integer|min:0',
        ]);

        // Auto-generate SKU jika dikosongkan
        if (empty($validated['sku'])) {
            $validated['sku'] = 'BC-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['category']), 0, 3)) . '-' . rand(100, 999);
        }

        // Susun string varian
        $variantParts = array_filter([$validated['size'] ?? null, $validated['color'] ?? null]);
        $validated['variants'] = !empty($variantParts) ? implode(' / ', $variantParts) : null;

        // Upload Foto
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create($validated);

        // Simpan Alokasi Stok Awal per Lokasi (Fitur 8: Pemisahan Stok Toko & Gudang)
        if (!empty($validated['stocks'])) {
            foreach ($validated['stocks'] as $locationId => $qty) {
                ProductStock::create([
                    'product_id'  => $product->id,
                    'location_id' => $locationId,
                    'quantity'    => (int) $qty,
                ]);
            }
        }

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk "' . $product->name . '" berhasil ditambahkan beserta alokasi stok!');
    }

    /**
     * Form Edit Produk & Stok
     */
    public function edit($id)
    {
        $product = Product::with('stocks')->findOrFail($id);
        $locations = Location::all();
        return view('Admin.produk.edit', compact('product', 'locations'));
    }

    /**
     * Update Data Produk & Stok per Lokasi
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'sku'         => 'required|string|max:50|unique:products,sku,' . $product->id,
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'size'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:50',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'min_stock'   => 'required|integer|min:0',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stocks'      => 'nullable|array',
            'stocks.*'    => 'nullable|integer|min:0',
        ]);

        $variantParts = array_filter([$validated['size'] ?? null, $validated['color'] ?? null]);
        $validated['variants'] = !empty($variantParts) ? implode(' / ', $variantParts) : null;

        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($validated);

        // Update stok per lokasi
        if (isset($validated['stocks'])) {
            foreach ($validated['stocks'] as $locationId => $qty) {
                ProductStock::updateOrCreate(
                    ['product_id' => $product->id, 'location_id' => $locationId],
                    ['quantity'   => (int) $qty]
                );
            }
        }

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Perubahan data produk dan stok berhasil disimpan!');
    }

    /**
     * Hapus Produk
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->stocks()->delete();
        $product->delete();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil dihapus dari sistem.');
    }
}
