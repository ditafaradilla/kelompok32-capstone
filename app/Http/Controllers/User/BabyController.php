<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Baby;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BabyController extends Controller
{
    /**
     * Simpan data bayi baru untuk pengguna saat ini.
     * Aturan: Satu user hanya satu bayi. Jika sudah ada, perbarui data yang ada.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $this->validateBabyData($request);

        $baby = Baby::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $redirectUrl = $this->getSafeRedirectUrl($request->input('redirect'), 'profile.edit');

        return redirect($redirectUrl)->with('success', 'Data si kecil berhasil disimpan!');
    }

    /**
     * Perbarui data bayi milik pengguna.
     */
    public function update(Request $request, Baby $baby)
    {
        $user = $request->user();

        if ($baby->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data bayi ini.');
        }

        $validated = $this->validateBabyData($request);

        $baby->update($validated);

        $redirectUrl = $this->getSafeRedirectUrl($request->input('redirect'), 'profile.edit');

        return redirect($redirectUrl)->with('success', 'Data si kecil berhasil diperbarui!');
    }

    /**
     * Hapus data bayi milik pengguna.
     */
    public function destroy(Request $request, Baby $baby)
    {
        $user = $request->user();

        if ($baby->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data bayi ini.');
        }

        $baby->delete();

        $redirectUrl = $this->getSafeRedirectUrl($request->input('redirect'), 'profile.edit');

        return redirect($redirectUrl)->with('success', 'Data si kecil berhasil dihapus!');
    }

    /**
     * Validasi data input bayi sesuai aturan sistem.
     */
    protected function validateBabyData(Request $request): array
    {
        $fiveYearsAgo = Carbon::now()->subYears(5)->toDateString();
        $today = Carbon::today()->toDateString();

        return $request->validate([
            'nama_bayi'     => ['required', 'string', 'max:255'],
            'tanggal_lahir' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . $fiveYearsAgo,
            ],
            'jenis_kelamin' => ['required', 'in:laki-laki,perempuan'],
            'berat_badan'   => ['required', 'numeric', 'min:0.5', 'max:30'],
            'tinggi_badan'  => ['required', 'numeric', 'min:30', 'max:130'],
        ], [
            'nama_bayi.required'          => 'Nama panggilan si kecil wajib diisi.',
            'tanggal_lahir.required'      => 'Tanggal lahir si kecil wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'tanggal_lahir.after_or_equal'  => 'Tanggal lahir maksimal 5 tahun ke belakang.',
            'jenis_kelamin.required'      => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in'            => 'Pilihan jenis kelamin tidak valid.',
            'berat_badan.required'        => 'Berat badan si kecil wajib diisi.',
            'berat_badan.numeric'         => 'Berat badan harus berupa angka.',
            'berat_badan.min'             => 'Berat badan minimal 0.5 kg.',
            'berat_badan.max'             => 'Berat badan maksimal 30 kg.',
            'tinggi_badan.required'       => 'Tinggi badan si kecil wajib diisi.',
            'tinggi_badan.numeric'        => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.min'            => 'Tinggi badan minimal 30 cm.',
            'tinggi_badan.max'            => 'Tinggi badan maksimal 130 cm.',
        ]);
    }

    /**
     * Validasi URL redirect hanya mengizinkan URL internal yang aman.
     */
    protected function getSafeRedirectUrl(?string $redirectUrl, string $fallbackRoute = 'profile.edit'): string
    {
        if ($redirectUrl && is_string($redirectUrl)) {
            $redirectUrl = trim($redirectUrl);
            $parsed = parse_url($redirectUrl);

            // Larang URL dengan skema/host (eksternal) dan protocol-relative URLs (//)
            if (!isset($parsed['host']) && !isset($parsed['scheme']) && str_starts_with($redirectUrl, '/') && !str_starts_with($redirectUrl, '//')) {
                return $redirectUrl;
            }
        }

        return route($fallbackRoute);
    }
}

