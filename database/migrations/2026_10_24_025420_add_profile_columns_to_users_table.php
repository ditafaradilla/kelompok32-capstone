<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('baby_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('baby_age')->nullable();
            $table->string('baby_weight')->nullable();
            $table->string('baby_height')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'photo', 'phone', 'address', 'baby_name', 
                'gender', 'baby_age', 'baby_weight', 'baby_height'
            ]);
        });
    }
};
