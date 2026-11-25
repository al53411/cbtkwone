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

        // Ambil riwayat pengerjaan siswa ini lengkap dengan relasi ujian & mapel
        $riwayatList = collect();
        if (class_exists(NilaiUjian::class)) {
            $relasiNilai = ['ujian'];
            if (method_exists(Ujian::class, 'mapel')) {
                $relasiNilai[] = 'ujian.mapel';
            }

            $riwayatList = NilaiUjian::with($relasiNilai)
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($item) {
                    $ujian = $item->ujian;

                    // Cek status rilis nilai dari pengaturan SuperAdmin (kolom tampilkan_nilai)
                    $isDirilis = $ujian && (
                        method_exists($ujian, 'isNilaiDirilis')
                        ? $ujian->isNilaiDirilis()
                        : (bool) ($ujian->tampilkan_nilai ?? false)
                    );

                    // Sisipkan atribut bantu agar view bisa langsung memakainya dengan aman
                    $item->is_nilai_dirilis = $isDirilis;
                    $item->nilai_tampil     = $isDirilis ? $item->nilai : null;

                    return $item;
                });
        }

        // Tetap sediakan $riwayatPengerjaan (keyBy ujian_id) untuk kartu jadwal ujian aktif
        $riwayatPengerjaan = $riwayatList->keyBy('ujian_id');

        // Sediakan $riwayatNilais (khusus yang sudah selesai) untuk tabel daftar hasil ujian
        $riwayatNilais = $riwayatList->filter(function ($item) {
            $status = strtolower((string) ($item->status ?? ''));
            return in_array($status, ['selesai', 'finished', 'completed']) || !is_null($item->waktu_selesai ?? null);
        })->values();

        return view('siswa.dashboard', compact(
            'siswa',
            'ujianAktif',
            'riwayatPengerjaan',
            'riwayatNilais',
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
