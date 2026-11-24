<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\NilaiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();

        if (!$siswa) {
            return redirect()->route('siswa.login');
        }

        $sekarang = Carbon::now('Asia/Jakarta');

        // Muat relasi mapel & kelas dengan aman
        $relasi = [];
        if (method_exists(Ujian::class, 'mapel')) {
            $relasi[] = 'mapel';
        }
        if (method_exists(Ujian::class, 'kelas')) {
            $relasi[] = 'kelas';
        }

        // Ambil daftar jadwal ujian khusus kelas siswa atau umum (null)
        $query = Ujian::with($relasi)
            ->where(function ($q) use ($siswa) {
                if (!empty($siswa->kelas_id)) {
                    $q->where('kelas_id', $siswa->kelas_id)
                        ->orWhereNull('kelas_id');
                }
            });

        // Filter juga berdasarkan sekolah siswa jika kolom sekolah_id tersedia
        if (!empty($siswa->sekolah_id) && Schema::hasColumn('ujians', 'sekolah_id')) {
            $query->where(function ($q) use ($siswa) {
                $q->where('sekolah_id', $siswa->sekolah_id)
                    ->orWhereNull('sekolah_id');
            });
        }

        // Urutkan jadwal: yang berstatus 'aktif' di atas, lalu urut berdasarkan waktu_mulai
        if (Schema::hasColumn('ujians', 'waktu_mulai')) {
            $query->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
                ->orderBy('waktu_mulai', 'asc');
        } else {
            $query->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
                ->latest();
        }

        $ujianAktif = $query->get();

        // Ambil riwayat pengerjaan siswa ini
        $riwayatPengerjaan = collect();
        if (class_exists(NilaiUjian::class)) {
            $riwayatPengerjaan = NilaiUjian::where('siswa_id', $siswa->id)
                ->get()
                ->keyBy('ujian_id');
        }

        return view('siswa.dashboard', compact(
            'siswa',
            'ujianAktif',
            'riwayatPengerjaan',
            'sekarang'
        ));
    }

    /**
     * Fallback konfirmasi ujian: teruskan ke verifikasiToken agar logika validasi,
     * pencatatan waktu_mulai, dan pemeriksaan sesi 100% seragam.
     */
    public function konfirmasiUjian(Request $request)
    {
        return app(UjianController::class)->verifikasiToken($request);
    }
}