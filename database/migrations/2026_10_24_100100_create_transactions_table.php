<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('type', 10);                          // income | expense
            $table->string('category', 60);
            $table->unsignedBigInteger('amount');                // Rupiah, bilangan bulat (tanpa desimal)
            $table->string('payment_status', 15)->default('lunas'); // lunas | belum_lunas
            $table->string('payment_method', 30)->nullable();
            $table->string('reference', 100)->nullable();        // no. nota / no. pesanan
            $table->string('event_name', 100)->nullable();       // nama bazar (opsional)
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transaction_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};