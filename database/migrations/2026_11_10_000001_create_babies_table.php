<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('babies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nama_bayi');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 1);
            $table->timestamps();
        });

        // Migrasi data lama dari kolom users ke tabel babies secara best-effort
        if (Schema::hasColumn('users', 'baby_name')) {
            $usersWithBabies = DB::table('users')
                ->whereNotNull('baby_name')
                ->where('baby_name', '!=', '')
                ->get();

            foreach ($usersWithBabies as $user) {
                // Parse usia bulan dari teks baby_age (e.g. "3 bulan", "6 - 12 Bulan", "4")
                $months = 0;
                if (!empty($user->baby_age) && preg_match('/(\d+)/', $user->baby_age, $matches)) {
                    $months = (int) $matches[1];
                }
                $tanggalLahir = Carbon::now()->subMonths($months)->toDateString();

                // Parse berat badan (e.g. "6.5 kg" -> 6.5)
                $beratBadan = 5.0;
                if (!empty($user->baby_weight) && preg_match('/(\d+(\.\d+)?)/', $user->baby_weight, $matches)) {
                    $beratBadan = (float) $matches[1];
                }

                // Parse tinggi badan (e.g. "63 cm" -> 63.0)
                $tinggiBadan = 55.0;
                if (!empty($user->baby_height) && preg_match('/(\d+(\.\d+)?)/', $user->baby_height, $matches)) {
                    $tinggiBadan = (float) $matches[1];
                }

                $gender = ($user->gender === 'perempuan') ? 'perempuan' : 'laki-laki';

                DB::table('babies')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'nama_bayi'     => $user->baby_name,
                        'tanggal_lahir' => $tanggalLahir,
                        'jenis_kelamin' => $gender,
                        'berat_badan'   => $beratBadan,
                        'tinggi_badan'  => $tinggiBadan,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('babies');
    }
};

