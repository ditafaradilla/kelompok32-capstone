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
        // Pastikan setiap user yang memiliki data bayi di kolom lama disalin ke tabel babies jika belum ada
        if (Schema::hasColumn('users', 'baby_name')) {
            $usersWithBabies = DB::table('users')
                ->whereNotNull('baby_name')
                ->where('baby_name', '!=', '')
                ->get();

            foreach ($usersWithBabies as $user) {
                $alreadyExists = DB::table('babies')->where('user_id', $user->id)->exists();
                if (!$alreadyExists) {
                    $months = 0;
                    if (!empty($user->baby_age) && preg_match('/(\d+)/', $user->baby_age, $matches)) {
                        $months = (int) $matches[1];
                    }
                    $tanggalLahir = Carbon::now()->subMonths($months)->toDateString();

                    $beratBadan = 5.0;
                    if (!empty($user->baby_weight) && preg_match('/(\d+(\.\d+)?)/', $user->baby_weight, $matches)) {
                        $beratBadan = (float) $matches[1];
                    }

                    $tinggiBadan = 55.0;
                    if (!empty($user->baby_height) && preg_match('/(\d+(\.\d+)?)/', $user->baby_height, $matches)) {
                        $tinggiBadan = (float) $matches[1];
                    }

                    $gender = (isset($user->gender) && $user->gender === 'perempuan') ? 'perempuan' : 'laki-laki';

                    DB::table('babies')->insert([
                        'user_id'       => $user->id,
                        'nama_bayi'     => $user->baby_name,
                        'tanggal_lahir' => $tanggalLahir,
                        'jenis_kelamin' => $gender,
                        'berat_badan'   => $beratBadan,
                        'tinggi_badan'  => $tinggiBadan,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            // Hapus kolom lama dari tabel users
            Schema::table('users', function (Blueprint $table) {
                $columnsToDrop = [];
                foreach (['baby_name', 'gender', 'baby_age', 'baby_weight', 'baby_height'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('baby_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('baby_age')->nullable();
            $table->string('baby_weight')->nullable();
            $table->string('baby_height')->nullable();
        });
    }
};

