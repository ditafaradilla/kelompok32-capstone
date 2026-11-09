<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat tabel product_variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku')->nullable()->unique();
            $table->string('ukuran')->nullable();
            $table->string('warna')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'ukuran', 'warna']);
        });

        // 2. Buat 1 varian default untuk setiap produk lama yang ada
        if (Schema::hasTable('products')) {
            $existingProducts = DB::table('products')->get();
            foreach ($existingProducts as $prod) {
                DB::table('product_variants')->updateOrInsert(
                    [
                        'product_id' => $prod->id,
                        'ukuran'     => $prod->size,
                        'warna'      => $prod->color,
                    ],
                    [
                        'sku'        => $prod->sku,
                        'created_at' => $prod->created_at ?? now(),
                        'updated_at' => $prod->updated_at ?? now(),
                    ]
                );
            }
        }

        // 3. Ubah product_stocks agar merujuk ke product_variant_id
        if (Schema::hasTable('product_stocks')) {
            Schema::rename('product_stocks', 'product_stocks_old');

            Schema::create('product_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
                $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
                $table->integer('quantity')->default(0);
                $table->timestamps();

                $table->unique(['product_variant_id', 'location_id']);
            });

            // Pindahkan data stok lama ke tabel baru berdasarkan varian produk pertama
            $oldStocks = DB::table('product_stocks_old')->get();
            foreach ($oldStocks as $old) {
                $variant = DB::table('product_variants')
                    ->where('product_id', $old->product_id)
                    ->first();

                if ($variant) {
                    DB::table('product_stocks')->updateOrInsert(
                        [
                            'product_variant_id' => $variant->id,
                            'location_id'        => $old->location_id,
                        ],
                        [
                            'quantity'   => $old->quantity,
                            'created_at' => $old->created_at ?? now(),
                            'updated_at' => $old->updated_at ?? now(),
                        ]
                    );
                }
            }

            Schema::dropIfExists('product_stocks_old');
        }

        // 4. Tambah product_variant_id pada purchase_order_items
        if (Schema::hasTable('purchase_order_items')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->foreignId('product_variant_id')
                      ->nullable()
                      ->after('product_id')
                      ->constrained('product_variants')
                      ->onDelete('cascade');
            });

            // Isi product_variant_id dari data lama
            $poItems = DB::table('purchase_order_items')->get();
            foreach ($poItems as $item) {
                $variant = DB::table('product_variants')
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($variant) {
                    DB::table('purchase_order_items')
                        ->where('id', $item->id)
                        ->update(['product_variant_id' => $variant->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Kembalikan purchase_order_items
        if (Schema::hasColumn('purchase_order_items', 'product_variant_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            });
        }

        // 2. Kembalikan product_stocks ke format semula
        if (Schema::hasTable('product_stocks')) {
            Schema::rename('product_stocks', 'product_stocks_variant');

            Schema::create('product_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
                $table->integer('quantity')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'location_id']);
            });

            $variantStocks = DB::table('product_stocks_variant')->get();
            foreach ($variantStocks as $vStock) {
                $variant = DB::table('product_variants')->where('id', $vStock->product_variant_id)->first();
                if ($variant) {
                    DB::table('product_stocks')->updateOrInsert(
                        [
                            'product_id'  => $variant->product_id,
                            'location_id' => $vStock->location_id,
                        ],
                        [
                            'quantity'   => $vStock->quantity,
                            'created_at' => $vStock->created_at ?? now(),
                            'updated_at' => $vStock->updated_at ?? now(),
                        ]
                    );
                }
            }

            Schema::dropIfExists('product_stocks_variant');
        }

        // 3. Hapus product_variants
        Schema::dropIfExists('product_variants');
    }
};
