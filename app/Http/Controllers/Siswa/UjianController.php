<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UjianController extends Controller
{
    /**
     * Memproses verifikasi token ujian dari siswa
     */
    public function verifikasiToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:10'
        ], [
            'token.required' => 'Token ujian wajib diisi!',
        ]);

        $siswa = Auth::guard('siswa')->user();

        if (!$siswa || !$siswa->kelas_id) {
            return back()->with('error', 'Data kelas siswa tidak ditemukan. Silakan hubungi proktor/admin.');
        }

        $tokenInput = strtoupper(trim($request->token));
        $now = Carbon::now();

        // Cari ujian yang aktif, sesuai token, sesuai kelas siswa/semua kelas, dan waktunya valid
        $ujian = Ujian::where('token', $tokenInput)
            ->where('status', 'aktif')
            ->where(function ($query) use ($siswa) {
                $query->where('kelas_id', $siswa->kelas_id)
                      ->orWhereNull('kelas_id');
            })
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->first();

        if (!$ujian) {
            return back()->with('error', 'Token salah, ujian tidak aktif, waktu ujian belum/sudah berakhir, atau bukan untuk kelas Anda!');
        }

        // Simpan session ujian aktif siswa
        session(['current_ujian_id' => $ujian->id]);

        return redirect()->route('siswa.ujian.mulai')
            ->with('success', 'Token valid! Selamat mengerjakan.');
    }

    /**
     * Menampilkan halaman pengerjaan soal ujian
     */
    public function mulai()
    {
        $siswa = Auth::guard('siswa')->user();
        $ujianId = session('current_ujian_id');

        if (!$ujianId) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Silakan masukkan token ujian terlebih dahulu.');
        }

        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId);

        // Ambil daftar soal berdasarkan mapel_id ujian DAN kelas_id siswa yang sedang login
        $querySoal = Soal::where('mapel_id', $ujian->mapel_id)
                         ->where('kelas_id', $siswa->kelas_id);

        // Terapkan pengaturan acak soal jika diaktifkan oleh Superadmin/Admin
        if ($ujian->acak_soal == 1 || $ujian->acak_soal === true || $ujian->acak_soal === 'true') {
            $querySoal->inRandomOrder();
        }

        $soals = $querySoal->get();

        return view('siswa.ujian.mulai', compact('siswa', 'ujian', 'soals'));
    }
}