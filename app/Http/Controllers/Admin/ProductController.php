<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Location;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tampilkan Halaman Daftar Produk & Pemisahan Stok
     */
    public function index(Request $request)
    {
        $query = Product::with(['variants.stocks.location']);

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
        $allProducts = Product::with('variants.stocks')->get();
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
        $sizeGuides = \App\Models\SizeGuide::all();
        return view('Admin.produk.create', compact('locations', 'sizeGuides'));
    }

    /**
     * Simpan Data Produk Baru & Varian beserta Alokasi Stok Awal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'         => 'nullable|string|max:50|unique:products,sku',
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'min_stock'   => 'required|integer|min:0',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Varian produk (repeater)
            'variants'               => 'required|array|min:1',
            'variants.*.ukuran'      => 'nullable|string|max:50',
            'variants.*.warna'       => 'nullable|string|max:50',
            'variants.*.sku'         => 'nullable|string|max:50',
            'variants.*.stocks'      => 'nullable|array',
            'variants.*.stocks.*'    => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // Auto-generate SKU produk jika kosong
            if (empty($validated['sku'])) {
                $categoryClean = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['category']), 0, 3));
                $validated['sku'] = 'BC-' . ($categoryClean ?: 'PRD') . '-' . rand(100, 999);
            }

            // Upload Foto
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('products', 'public');
            }

            $variantsInput = $validated['variants'];
            unset($validated['variants']);

            if (empty($validated['size']) && !empty($variantsInput[0]['ukuran'])) {
                $validated['size'] = $variantsInput[0]['ukuran'];
            }
            if (empty($validated['color']) && !empty($variantsInput[0]['warna'])) {
                $validated['color'] = $variantsInput[0]['warna'];
            }

            $product = Product::create($validated);

            // Simpan setiap varian dan alokasi stok per lokasi
            $variantCounter = 1;
            foreach ($variantsInput as $varData) {
                $varSku = !empty($varData['sku']) ? $varData['sku'] : ($product->sku . '-' . $variantCounter);
                $variantCounter++;

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku'        => $varSku,
                    'ukuran'     => $varData['ukuran'] ?? null,
                    'warna'      => $varData['warna'] ?? null,
                ]);

                if (!empty($varData['stocks'])) {
                    foreach ($varData['stocks'] as $locationId => $qty) {
                        ProductStock::create([
                            'product_variant_id' => $variant->id,
                            'location_id'        => $locationId,
                            'quantity'           => (int) ($qty ?? 0),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk "' . $validated['name'] . '" dan seluruh variannya berhasil ditambahkan!');
    }

    /**
     * Form Edit Produk & Stok
     */
    public function edit($id)
    {
        $product = Product::with(['variants.stocks'])->findOrFail($id);
        $locations = Location::all();
        $sizeGuides = \App\Models\SizeGuide::all();
        return view('Admin.produk.edit', compact('product', 'locations', 'sizeGuides'));
    }

    /**
     * Update Data Produk & Varian Stok per Lokasi
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'sku'         => 'required|string|max:50|unique:products,sku,' . $product->id,
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'min_stock'   => 'required|integer|min:0',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Varian produk
            'variants'               => 'required|array|min:1',
            'variants.*.id'          => 'nullable|integer',
            'variants.*.ukuran'      => 'nullable|string|max:50',
            'variants.*.warna'       => 'nullable|string|max:50',
            'variants.*.sku'         => 'nullable|string|max:50',
            'variants.*.stocks'      => 'nullable|array',
            'variants.*.stocks.*'    => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $product, $validated) {
            if ($request->hasFile('photo')) {
                if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                    Storage::disk('public')->delete($product->photo);
                }
                $validated['photo'] = $request->file('photo')->store('products', 'public');
            }

            $variantsInput = $validated['variants'];
            unset($validated['variants']);

            if (!empty($variantsInput[0]['ukuran'])) {
                $validated['size'] = $variantsInput[0]['ukuran'];
            }
            if (!empty($variantsInput[0]['warna'])) {
                $validated['color'] = $variantsInput[0]['warna'];
            }

            $product->update($validated);

            // Kelola varian: update or create, dan hapus yang tidak dikirim
            $keptVariantIds = [];
            $variantCounter = 1;

            foreach ($variantsInput as $varData) {
                $varSku = !empty($varData['sku']) ? $varData['sku'] : ($product->sku . '-' . $variantCounter);
                $variantCounter++;

                $variant = null;
                if (!empty($varData['id'])) {
                    $variant = ProductVariant::where('product_id', $product->id)->find($varData['id']);
                }

                if ($variant) {
                    $variant->update([
                        'sku'    => $varSku,
                        'ukuran' => $varData['ukuran'] ?? null,
                        'warna'  => $varData['warna'] ?? null,
                    ]);
                } else {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku'        => $varSku,
                        'ukuran'     => $varData['ukuran'] ?? null,
                        'warna'      => $varData['warna'] ?? null,
                    ]);
                }

                $keptVariantIds[] = $variant->id;

                // Update stok varian per lokasi
                if (isset($varData['stocks'])) {
                    foreach ($varData['stocks'] as $locationId => $qty) {
                        ProductStock::updateOrCreate(
                            ['product_variant_id' => $variant->id, 'location_id' => $locationId],
                            ['quantity'           => (int) ($qty ?? 0)]
                        );
                    }
                }
            }

            // Hapus varian yang dihapus dari form
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $keptVariantIds)
                ->delete();
        });

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Perubahan data produk dan varian stok berhasil disimpan!');
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

        // Variannya akan cascade delete beserta stocksnya
        $product->delete();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil dihapus dari sistem.');
    }
}
