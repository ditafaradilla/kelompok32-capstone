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
        // 1. Tambah kolom kurir dan nomor_resi ke tabel orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('kurir')->nullable()->after('status_pesanan');
            $table->string('nomor_resi')->nullable()->after('kurir');
        });

        // 2. Tabel order_status_histories untuk mencatat rekam jejak pelacakan pesanan
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['kurir', 'nomor_resi']);
        });
    }
};

