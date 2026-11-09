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
        // 1. Tabel orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nomor_pesanan')->unique();
            $table->dateTime('tanggal_pesanan');
            $table->unsignedBigInteger('total_harga');
            $table->text('alamat_pengiriman');
            $table->string('nama_penerima');
            $table->string('telepon_penerima');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('ongkir')->default(0);
            $table->string('status_pesanan')->default('menunggu_pembayaran');
            $table->timestamps();
        });

        // 2. Tabel order_items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('jumlah');
            $table->unsignedBigInteger('harga_satuan');
            $table->string('nama_produk');
            $table->string('varian_label');
            $table->timestamps();
        });

        // 3. Tabel payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
            $table->string('metode_pembayaran'); // transfer_bank | qris
            $table->unsignedBigInteger('jumlah_bayar');
            $table->string('status_pembayaran')->default('belum_bayar'); // belum_bayar | menunggu_verifikasi | lunas | ditolak
            $table->dateTime('tanggal_bayar')->nullable();
            $table->string('bukti_bayar')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

