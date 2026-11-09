<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna dan data si kecil.
     */
    public function edit(Request $request)
    {
        $user = $request->user()->load('baby');

        return view('User.edit-profil', compact('user'));
    }

    /**
     * Perbarui data profil pengguna (nama, email, telepon, alamat, foto).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'        => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'string'],
            'photo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'remove_photo' => ['nullable'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique'   => 'Alamat email sudah digunakan oleh akun lain.',
            'photo.image'    => 'File foto harus berupa gambar.',
            'photo.mimes'    => 'Format foto harus berupa jpeg, png, jpg, atau webp.',
            'photo.max'      => 'Ukuran foto maksimal 3 MB.',
        ]);

        // Tangani penghapusan foto profil
        if ($request->boolean('remove_photo') || $request->input('remove_photo') === '1') {
            if (!empty($user->photo) && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = null;
        } elseif ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if (!empty($user->photo) && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('profiles', 'public');
            $user->photo = $path;
        }

        $user->name    = $request->name;
        $user->email   = $request->email;
        $user->phone   = $request->phone;
        $user->address = $request->address;
        $user->save();

        $redirectUrl = $this->getSafeRedirectUrl($request->input('redirect'), 'profile.edit');

        return redirect($redirectUrl)->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Ubah kata sandi pengguna.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.min'              => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi lama tidak sesuai.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Kata sandi berhasil diperbarui!');
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

