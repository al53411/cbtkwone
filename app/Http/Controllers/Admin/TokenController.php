<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Ujian;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    /**
     * Menampilkan Halaman Manajemen Token & Ujian
     */
    public function index(Request $request)
    {
        // 1. Ambil data Kelas dan Mapel untuk Dropdown Filter
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel = Mapel::orderBy('nama_mapel', 'asc')->get();

        // 2. Query daftar Ujian dengan tambahan hitung progress siswa
        $query = Ujian::with(['kelas', 'mapel'])
            ->withCount([
                // Menghitung jumlah siswa yang sudah selesai ujian ini
                'nilaiUjians as siswa_selesai_count' => function ($q) {
                    $q->where('status', 'selesai');
                },
                // Menghitung total siswa berdasarkan kelas ujian tersebut
                // (Jika kelas_id kosong, hitung seluruh total siswa)
                'kelas as total_siswa_count' => function ($q) {
                    // Mengambil jumlah siswa dari relasi kelas
                    $q->withCount('siswas'); // Sesuaikan jika nama relasi di model Kelas berbeda (misal: siswa)
                }
            ]);

        // Tangani jika total_siswa ingin dihitung manual berdasarkan kelas ujian
        // (Alternatif query jika withCount kelas siswas tidak langsung tembus):

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(10)->appends($request->query());

        // Lakukan perhitungan manual total siswa per baris ujian agar 100% akurat
        foreach ($ujians as $ujian) {
            if ($ujian->kelas_id) {
                $ujian->total_siswa_count = \App\Models\Siswa::where('kelas_id', $ujian->kelas_id)->count();
            } else {
                $ujian->total_siswa_count = \App\Models\Siswa::count();
            }

            // Hitung jumlah siswa selesai untuk ujian ini
            $ujian->siswa_selesai_count = \App\Models\NilaiUjian::where('ujian_id', $ujian->id)
                ->where('status', 'selesai')
                ->count();
        }

        // 3. Ambil token dari ujian aktif terbaru (untuk tampilan Banner Ringkasan)
        $ujianAktif = Ujian::where('status', 'aktif')
            ->whereNotNull('token')
            ->latest('updated_at')
            ->first();

        $tokenAktif = (object) [
            'token' => $ujianAktif->token ?? '------',
            'nama_ujian' => $ujianAktif->nama_ujian ?? 'Tidak ada ujian aktif'
        ];

        return view('admin.token.index', compact('tokenAktif', 'listKelas', 'listMapel', 'ujians'));
    }

    /**
     * Rilis / Generate Token Baru (Spesifik per Ujian/Mapel)
     */
    public function generate(Request $request)
    {
        $karakter = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        // 1. Jika ada parameter 'ujian_id', update token untuk 1 ujian/mapel spesifik tersebut
        if ($request->filled('ujian_id')) {
            $ujian = Ujian::with('mapel')->findOrFail($request->ujian_id);

            $newToken = substr(str_shuffle(str_repeat($karakter, 5)), 0, 6);
            $ujian->update(['token' => $newToken]);

            return back()->with('success', "Token ($newToken) berhasil dirilis untuk mapel: " . ($ujian->mapel->nama_mapel ?? $ujian->nama_ujian));
        }

        // 2. Jika tidak ada 'ujian_id', generate token unik masing-masing untuk SEMUA ujian berstatus 'aktif'
        $ujiansAktif = Ujian::with('mapel')->where('status', 'aktif')->get();

        if ($ujiansAktif->isEmpty()) {
            return back()->with('error', 'Tidak ada ujian berstatus AKTIF yang dapat di-update tokennya.');
        }

        foreach ($ujiansAktif as $ujian) {
            $uniqueToken = substr(str_shuffle(str_repeat($karakter, 5)), 0, 6);
            $ujian->update(['token' => $uniqueToken]);
        }

        $count = $ujiansAktif->count();
        return back()->with('success', "Token unik berhasil dirilis untuk $count ujian/mapel yang sedang aktif!");
    }

    /**
     * Menampilkan halaman progress siswa yang sudah dan belum mengerjakan ujian
     */
    public function progressUjian($id)
    {
        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($id);

        // 1. Ambil semua siswa yang sesuai dengan kelas ujian tersebut
        // Jika kelas_id pada ujian kosong (berlaku untuk semua kelas), ambil seluruh siswa
        $querySiswa = \App\Models\Siswa::query();
        if ($ujian->kelas_id) {
            $querySiswa->where('kelas_id', $ujian->kelas_id);
        }
        $semuaSiswa = $querySiswa->orderBy('nama_siswa', 'asc')->get();

        // 2. Ambil data nilai/pengerjaan siswa untuk ujian ini
        $nilaiSiswa = \App\Models\NilaiUjian::where('ujian_id', $id)->get()->keyBy('siswa_id');

        // 3. Petakan status masing-masing siswa (Sudah / Belum mengerjakan)
        $progressPeserta = $semuaSiswa->map(function ($siswa) use ($nilaiSiswa) {
            $pengerjaan = $nilaiSiswa->get($siswa->id);

            return [
                'siswa'        => $siswa,
                'sudah_ujian'  => !empty($pengerjaan),
                'nilai'        => $pengerjaan->nilai ?? null,
                'jumlah_benar' => $pengerjaan->jumlah_benar ?? 0,
                'jumlah_salah' => $pengerjaan->jumlah_salah ?? 0,
                'tidak_dijawab' => $pengerjaan->tidak_dijawab ?? 0,
                'waktu_selesai' => $pengerjaan->waktu_selesai ?? null,
                'status'       => $pengerjaan->status ?? 'Belum Mengerjakan',
            ];
        });

        return view('admin.token.progress', compact('ujian', 'progressPeserta'));
    }
}
