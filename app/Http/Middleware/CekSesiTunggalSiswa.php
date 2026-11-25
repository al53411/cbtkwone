<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CekSesiTunggalSiswa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('siswa')->check()) {
            $siswa = Auth::guard('siswa')->user();
            $tokenBrowser = session('siswa_session_token');

            // Ambil langsung dari DB agar tidak terpengaruh cache model in-memory
            $tokenDb = DB::table('siswas')
                ->where('id', $siswa->id)
                ->value('session_token');

            // Jika token di database kosong (habis di-Reset Login oleh Admin) atau berbeda dengan browser ini
            if (empty($tokenDb) || empty($tokenBrowser) || $tokenDb !== $tokenBrowser) {
                Auth::guard('siswa')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status'   => 'session_expired',
                        'message'  => 'Sesi login Anda telah direset oleh Proktor/Admin. Silakan login kembali.',
                        'redirect' => route('siswa.login'),
                    ], 401);
                }

                return redirect()->route('siswa.login')
                    ->with('error', 'Sesi login Anda telah direset oleh Proktor/Admin atau akun dibuka di perangkat lain. Silakan login kembali.');
            }
        }

        return $next($request);
    }
}
