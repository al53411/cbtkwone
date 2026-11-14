<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekSesiTunggalSiswa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('siswa')->check()) {
            $siswa = Auth::guard('siswa')->user();
            $tokenBrowser = session('siswa_session_token');

            // Jika token di database kosong (habis di-Reset Login oleh Admin) atau berbeda dengan browser ini
            if (empty($siswa->session_token) || $siswa->session_token !== $tokenBrowser) {
                Auth::guard('siswa')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('siswa.login')
                    ->with('error', 'Sesi login Anda telah direset oleh Proktor/Admin atau akun dibuka di perangkat lain. Silakan login kembali.');
            }
        }

        return $next($request);
    }
}