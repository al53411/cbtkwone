<?php

namespace App\Http\Controllers\Korektor;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profilGuru = Guru::with('kelas')->where('user_id', $user->id)->first();

        $kelasDiampu = $profilGuru ? $profilGuru->kelas : collect();
        $kelasIds    = $kelasDiampu->pluck('id')->toArray();
        $mapelDiampu = $profilGuru->mata_pelajaran ?? 'Semua Mapel';

        // Hitung total siswa di rombel yang diampu korektor
        $totalSiswaDiampu = 0;
        $siswaIds = collect();

        if (!empty($kelasIds) && Schema::hasTable('siswas')) {
            $siswaIds = DB::table('siswas')->whereIn('kelas_id', $kelasIds)->pluck('id');
            $totalSiswaDiampu = $siswaIds->count();
        }

        // Hitung statistik koreksi jawaban esai (jawaban_teks & skor_esai)
        $belumDikoreksi = 0;
        $sudahDikoreksi = 0;
        $daftarTugasKoreksi = collect();

        if (Schema::hasTable('jawaban_siswas') && Schema::hasColumn('jawaban_siswas', 'skor_esai')) {
            $queryEsai = DB::table('jawaban_siswas')
                ->whereNotNull('jawaban_teks')
                ->where('jawaban_teks', '!=', '');

            if (!empty($kelasIds)) {
                $queryEsai->whereIn('siswa_id', $siswaIds);
            }

            $belumDikoreksi = (clone $queryEsai)->whereNull('skor_esai')->count();
            $sudahDikoreksi = (clone $queryEsai)->whereNotNull('skor_esai')->count();

            if (Schema::hasTable('ujians')) {
                $ujianIds = (clone $queryEsai)
                    ->whereNull('skor_esai')
                    ->distinct()
                    ->pluck('ujian_id');

                if ($ujianIds->isNotEmpty()) {
                    $daftarTugasKoreksi = DB::table('ujians')
                        ->whereIn('id', $ujianIds)
                        ->latest('created_at')
                        ->take(5)
                        ->get();
                }
            }
        }

        return view('korektor.dashboard', compact(
            'user',
            'profilGuru',
            'mapelDiampu',
            'kelasDiampu',
            'totalSiswaDiampu',
            'belumDikoreksi',
            'sudahDikoreksi',
            'daftarTugasKoreksi'
        ));
    }
}
