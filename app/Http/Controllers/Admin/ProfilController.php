<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    /**
     * Memperbarui kata sandi bawaan dari popup login pertama
     * dan menonaktifkan penanda wajib_ganti_sandi.
     */
    public function updateSandiAwal(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Sesi login telah berakhir, silakan masuk kembali.');
        }

        // Cegah pengguna memasukkan sandi yang sama persis dengan sandi bawaan sebelumnya
        if (Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Kata sandi baru tidak boleh sama dengan kata sandi bawaan sebelumnya.'])
                ->with('error', 'Kata sandi baru harus berbeda dari sandi sebelumnya.');
        }

        $user->update([
            'password'          => Hash::make($request->password),
            'wajib_ganti_sandi' => false,
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui! Akun Anda kini lebih aman.');
    }
}
