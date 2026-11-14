<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('siswa.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nisn'     => 'required|string',
            'password' => 'required|string',
        ], [
            'nisn.required'     => 'NISN wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $nisn = trim($request->nisn);
        $passwordInput = trim($request->password);

        // 1. Cari data siswa berdasarkan NISN
        $siswa = Siswa::where('nisn', $nisn)->first();

        if (!$siswa) {
            return back()->withErrors([
                'login_error' => 'NISN tidak terdaftar dalam sistem!',
            ])->onlyInput('nisn');
        }

        // 2. Format password standar: NISN + '*'
        $passwordStandar = $siswa->nisn . '*';

        // 3. Validasi kecocokan password
        $isPasswordValid = (
            ($passwordInput === $passwordStandar) ||
            ($passwordInput === $siswa->nisn) ||
            ($siswa->password && Hash::check($passwordInput, $siswa->password)) ||
            ($siswa->password && $passwordInput === $siswa->password)
        );

        if ($isPasswordValid) {
            // 4. CEK KUNCI PERANGKAT: Tolak login jika akun masih aktif di perangkat lain
            if (!empty($siswa->session_token)) {
                return back()->withErrors([
                    'login_error' => 'Akun Anda sedang aktif di perangkat lain! Jika browser tertutup atau ganti perangkat, minta Proktor/Admin menekan tombol Reset Login.',
                ])->onlyInput('nisn');
            }

            // 5. Generate token sesi unik & simpan ke DB terlebih dahulu
            $tokenSesi = Str::random(60);
            $siswa->session_token = $tokenSesi;
            $siswa->save();

            // 6. Login-kan siswa menggunakan guard 'siswa'
            Auth::guard('siswa')->login($siswa);

            // Regenerate session & simpan token ke session aktif
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            $request->session()->put('siswa_session_token', $tokenSesi);
            $request->session()->save();

            return redirect()->route('siswa.dashboard');
        }

        return back()->withErrors([
            'login_error' => 'NISN atau Password salah!',
        ])->onlyInput('nisn');
    }

    public function logout(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();

        if ($siswa instanceof Siswa) {
            $siswa->session_token = null;
            $siswa->save();
        }

        Auth::guard('siswa')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('siswa.login');
    }
}
