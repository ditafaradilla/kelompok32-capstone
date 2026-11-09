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
        Schema::create('size_guides', function (Blueprint $table) {
            $table->id();
            $table->string('ukuran');
            $table->integer('usia_min_bulan');
            $table->integer('usia_maks_bulan');
            $table->decimal('berat_min', 5, 2);
            $table->decimal('berat_maks', 5, 2);
            $table->decimal('tinggi_min', 5, 1);
            $table->decimal('tinggi_maks', 5, 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_guides');
    }
};

