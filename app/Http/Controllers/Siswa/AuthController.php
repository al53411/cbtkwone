<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('siswa')->check()) {
            $siswa = Auth::guard('siswa')->user();
            $tokenDb = DB::table('siswas')->where('id', $siswa->id)->value('session_token');

            if (!empty($tokenDb) && $tokenDb === session('siswa_session_token')) {
                return redirect()->route('siswa.dashboard');
            }

            // Jika di DB sudah di-reset Admin tapi cookie browser masih nyangkut, bersihkan sekalian
            Auth::guard('siswa')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

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

        // 1. Cari data siswa berdasarkan NISN (antisipasi jika ada spasi di kolom nisn DB)
        $siswa = Siswa::where('nisn', $nisn)
            ->orWhereRaw('TRIM(nisn) = ?', [$nisn])
            ->first();

        if (!$siswa) {
            return back()->withErrors([
                'login_error' => 'NISN tidak terdaftar dalam sistem!',
            ])->onlyInput('nisn');
        }

        // 2. Format password standar: NISN + '*'
        $nisnBersih = trim($siswa->nisn);
        $passwordStandar = $nisnBersih . '*';

        // 3. Validasi kecocokan password
        $isPasswordValid = (
            ($passwordInput === $passwordStandar) ||
            ($passwordInput === $nisnBersih) ||
            ($siswa->password && Hash::check($passwordInput, $siswa->password)) ||
            ($siswa->password && $passwordInput === $siswa->password)
        );

        if (!$isPasswordValid) {
            return back()->withErrors([
                'login_error' => 'NISN atau Password salah!',
            ])->onlyInput('nisn');
        }

        // 4. Ambil nilai session_token terbaru langsung dari database (hindari cache model)
        $tokenAktifDb = DB::table('siswas')
            ->where('id', $siswa->id)
            ->value('session_token');

        if (!empty($tokenAktifDb)) {
            $sesiMasihHidup = true;

            // Verifikasi ke tabel sessions: jika baris sesi sudah dihapus Admin / expired, izinkan login
            if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
                $batasAktif = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;

                $sesiMasihHidup = DB::table('sessions')
                    ->where('payload', 'LIKE', '%' . $tokenAktifDb . '%')
                    ->where('last_activity', '>=', $batasAktif)
                    ->exists();
            }

            if ($sesiMasihHidup) {
                return back()->withErrors([
                    'login_error' => 'Akun Anda sedang aktif di perangkat lain! Jika browser tertutup atau ganti perangkat, minta Proktor/Admin menekan tombol Reset Login.',
                ])->onlyInput('nisn');
            }

            // Sesi lama sudah hilang di tabel sessions -> bersihkan otomatis
            DB::table('siswas')
                ->where('id', $siswa->id)
                ->update(['session_token' => null]);
        }

        // 5. Login-kan siswa menggunakan guard 'siswa' & regenerate session
        Auth::guard('siswa')->login($siswa);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        // 6. Generate token sesi baru & simpan serentak ke session serta database
        $tokenSesi = Str::random(60);
        $request->session()->put('siswa_session_token', $tokenSesi);
        $request->session()->save();

        DB::table('siswas')
            ->where('id', $siswa->id)
            ->update([
                'session_token' => $tokenSesi,
                'updated_at'    => now(),
            ]);

        return redirect()->route('siswa.dashboard');
    }

    public function logout(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();

        if ($siswa) {
            DB::table('siswas')
                ->where('id', $siswa->id)
                ->update([
                    'session_token'  => null,
                    'remember_token' => null,
                    'updated_at'     => now(),
                ]);
        }

        Auth::guard('siswa')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('siswa.login');
    }
}
