<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Kategori Produk (opsional/helper)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 2. Tabel Produk (Fitur 8 & 11)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->string('category')->default('Baju Bayi');
            $table->string('variants')->nullable(); // contoh: "0-3 Bln / Biru Muda", "Ukuran S, M, L"
            $table->string('size')->nullable();     // varian ukuran
            $table->string('color')->nullable();    // varian warna
            $table->decimal('price', 12, 2)->default(0);      // harga jual
            $table->decimal('cost_price', 12, 2)->default(0); // harga modal / beli dari pemasok
            $table->integer('min_stock')->default(5);         // batas minimum untuk peringatan stok habis/menipis
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Lokasi Toko & Gudang (Fitur 9)
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // contoh: "Toko Utama (Etalase)", "Gudang Pusat", "Cabang Toko A"
            $table->string('code')->unique()->nullable(); // e.g. "TKO-01", "GDG-01"
            $table->string('type')->default('toko'); // 'toko' atau 'gudang'
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Pemisahan Stok Produk per Lokasi (Fitur 8 & 9)
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'location_id']);
        });

        // 5. Tabel Pemasok / Supplier (Fitur 10)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 6. Tabel Pemesanan Barang ke Pemasok (Purchase Order) (Fitur 10 & 11)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // e.g. "PO-202611-001"
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('target_location_id')->nullable()->constrained('locations')->nullOnDelete(); // Gudang/toko tujuan penerimaan
            $table->date('order_date');
            $table->date('received_date')->nullable();
            $table->string('status')->default('pending'); // 'pending' (menunggu barang datang), 'received' (barang sudah datang), 'cancelled'
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamps();
        });

        // 7. Tabel Item Pemesanan & Penerimaan Barang (Fitur 10 & 11)
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0); // diisi saat barang datang di Fitur 11
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
