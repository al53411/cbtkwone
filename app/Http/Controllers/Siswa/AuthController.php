<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('siswa.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
            'password' => 'required|string',
        ], [
            'nisn.required' => 'NISN wajib diisi.',
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

        // 3. Validasi kecocokan password (mendukung teks biasa, NISN*, maupun hash DB)
        $isPasswordValid = (
            ($passwordInput === $passwordStandar) || 
            ($passwordInput === $siswa->nisn) ||
            ($siswa->password && Hash::check($passwordInput, $siswa->password)) ||
            ($siswa->password && $passwordInput === $siswa->password)
        );

        if ($isPasswordValid) {
            // Login-kan siswa secara manual menggunakan guard 'siswa'
            Auth::guard('siswa')->login($siswa);
            
            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            return redirect()->intended(route('siswa.dashboard'));
        }

        return back()->withErrors([
            'login_error' => 'NISN atau Password salah!',
        ])->onlyInput('nisn');
    }

    public function logout(Request $request)
    {
        Auth::guard('siswa')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('siswa.login');
    }
}