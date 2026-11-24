<?php

namespace App\Http\Controllers\Korektor;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KoreksiController extends Controller
{
    /**
     * Ambil daftar soal bertipe esai/uraian menggunakan logika yang sama persis dengan Siswa\UjianController.
     */
    private function getSoalEsaiByUjian($ujian)
    {
        if (!Schema::hasTable('soals')) {
            return collect();
        }

        $querySoal = DB::table('soals');

        if (Schema::hasColumn('soals', 'ujian_id') && DB::table('soals')->where('ujian_id', $ujian->id)->exists()) {
            $querySoal->where('ujian_id', $ujian->id);
        } elseif (Schema::hasColumn('soals', 'mapel_id') && isset($ujian->mapel_id)) {
            $querySoal->where('mapel_id', $ujian->mapel_id);
            if (Schema::hasColumn('soals', 'kelas_id') && !empty($ujian->kelas_id)) {
                $querySoal->where(function ($q) use ($ujian) {
                    $q->where('kelas_id', $ujian->kelas_id)
                        ->orWhereNull('kelas_id');
                });
            }
        }

        $kolomTipe = collect(['tipe_soal', 'tipe', 'jenis_soal', 'jenis'])
            ->first(fn($col) => Schema::hasColumn('soals', $col));

        if ($kolomTipe) {
            $querySoal->whereIn(DB::raw("LOWER(TRIM({$kolomTipe}))"), ['esai', 'essay', 'uraian']);
        }

        if (Schema::hasColumn('soals', 'nomor_soal')) {
            $querySoal->orderByRaw('CAST(nomor_soal AS UNSIGNED) ASC');
        }
        $querySoal->orderBy('id', 'asc');

        return $querySoal->get();
    }

    public function index()
    {
        $user = Auth::user();
        $profilGuru = Guru::with('kelas')->where('user_id', $user->id)->first();

        $ujians = collect();

        if (Schema::hasTable('ujians') && Schema::hasTable('jawaban_siswas')) {
            $queryUjian = DB::table('ujians');

            if ($user->sekolah_id && Schema::hasColumn('ujians', 'sekolah_id')) {
                $queryUjian->where('sekolah_id', $user->sekolah_id);
            }

            $ujians = $queryUjian->latest('created_at')->get()->map(function ($ujian) {
                $soalEsaiIds = $this->getSoalEsaiByUjian($ujian)->pluck('id');

                $qEsai = DB::table('jawaban_siswas')
                    ->where('ujian_id', $ujian->id);

                if ($soalEsaiIds->isNotEmpty()) {
                    $qEsai->whereIn('soal_id', $soalEsaiIds);
                } else {
                    $qEsai->whereRaw('1 = 0');
                }

                $ujian->total_esai = (clone $qEsai)->count();
                $ujian->belum_dikoreksi = Schema::hasColumn('jawaban_siswas', 'skor_esai')
                    ? (clone $qEsai)->whereNull('skor_esai')->count()
                    : $ujian->total_esai;
                $ujian->sudah_dikoreksi = max(0, $ujian->total_esai - $ujian->belum_dikoreksi);

                return $ujian;
            });
        }

        return view('korektor.koreksi.index', compact('ujians', 'profilGuru'));
    }

    public function show(Request $request, $ujianId)
    {
        $ujian = DB::table('ujians')->where('id', $ujianId)->first();
        if (!$ujian) {
            abort(404, 'Paket ujian tidak ditemukan.');
        }

        $soals = $this->getSoalEsaiByUjian($ujian)->values()->map(function ($soal, $idx) use ($ujianId) {
            $soal->no_urut_tampil = $soal->nomor_soal ?? ($idx + 1);

            $qJawaban = DB::table('jawaban_siswas')
                ->where('ujian_id', $ujianId)
                ->where('soal_id', $soal->id);

            $soal->total_jawaban = (clone $qJawaban)->count();
            $soal->sisa_belum = Schema::hasColumn('jawaban_siswas', 'skor_esai')
                ? (clone $qJawaban)->whereNull('skor_esai')->count()
                : $soal->total_jawaban;

            return $soal;
        });

        $selectedSoalId = $request->query('soal_id') ?? ($soals->first()->id ?? null);
        $soalAktif = $soals->firstWhere('id', $selectedSoalId) ?? $soals->first();

        $jawabanSiswas = collect();
        if ($soalAktif && Schema::hasTable('siswas')) {
            $kolomNamaSiswa = collect(['nama_lengkap', 'nama', 'name'])
                ->first(fn($col) => Schema::hasColumn('siswas', $col)) ?? 'id';

            $kolomNis = collect(['nisn', 'nis', 'nomor_peserta'])
                ->first(fn($col) => Schema::hasColumn('siswas', $col));

            $kolomJawaban = Schema::hasColumn('jawaban_siswas', 'jawaban_teks')
                ? 'COALESCE(NULLIF(jawaban_siswas.jawaban_teks, ""), jawaban_siswas.jawaban, "(Tidak dijawab)")'
                : 'COALESCE(jawaban_siswas.jawaban, "(Tidak dijawab)")';

            $queryJawaban = DB::table('jawaban_siswas')
                ->join('siswas', 'jawaban_siswas.siswa_id', '=', 'siswas.id')
                ->leftJoin('kelas', 'siswas.kelas_id', '=', 'kelas.id')
                ->where('jawaban_siswas.ujian_id', $ujianId)
                ->where('jawaban_siswas.soal_id', $soalAktif->id)
                ->select(
                    'jawaban_siswas.id as jawaban_id',
                    DB::raw("{$kolomJawaban} as jawaban_teks"),
                    Schema::hasColumn('jawaban_siswas', 'skor_esai')
                        ? 'jawaban_siswas.skor_esai'
                        : DB::raw('NULL as skor_esai'),
                    'siswas.id as siswa_id',
                    "siswas.{$kolomNamaSiswa} as nama_siswa",
                    $kolomNis ? "siswas.{$kolomNis} as nomor_identitas" : DB::raw("'-' as nomor_identitas"),
                    Schema::hasTable('kelas') ? 'kelas.nama_kelas' : DB::raw("'-' as nama_kelas")
                );

            if ($request->filled('kelas_id')) {
                $queryJawaban->where('siswas.kelas_id', $request->kelas_id);
            }

            if (Schema::hasTable('kelas')) {
                $queryJawaban->orderBy('kelas.nama_kelas', 'asc');
            }
            if (Schema::hasColumn('siswas', 'no_absen')) {
                $queryJawaban->orderBy('siswas.no_absen', 'asc');
            }
            $queryJawaban->orderBy("siswas.{$kolomNamaSiswa}", 'asc');

            $jawabanSiswas = $queryJawaban->get();
        }

        $daftarKelas = Schema::hasTable('kelas')
            ? DB::table('kelas')->orderBy('nama_kelas')->get()
            : collect();

        return view('korektor.koreksi.show', compact(
            'ujian',
            'soals',
            'soalAktif',
            'jawabanSiswas',
            'daftarKelas'
        ));
    }

    public function simpanSkor(Request $request, $jawabanId)
    {
        $request->validate([
            'skor_esai' => 'required|numeric|min:0|max:100',
        ]);

        $jawaban = DB::table('jawaban_siswas')->where('id', $jawabanId)->first();
        if (!$jawaban) {
            return response()->json(['success' => false, 'message' => 'Data jawaban tidak ditemukan.'], 404);
        }

        DB::table('jawaban_siswas')
            ->where('id', $jawabanId)
            ->update([
                'skor_esai'  => $request->skor_esai,
                'updated_at' => now(),
            ]);

        // Hitung ulang nilai akhir siswa di tabel nilai_ujians secara otomatis
        $this->recalculateNilaiUjian($jawaban->siswa_id, $jawaban->ujian_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Skor berhasil disimpan & nilai akhir diperbarui.',
                'skor_esai' => $request->skor_esai,
            ]);
        }

        return back()->with('success', 'Skor jawaban esai berhasil disimpan.');
    }

    /**
     * Sinkronisasi ulang nilai_esai & nilai total di nilai_ujians setelah korektor memberi skor.
     */
    private function recalculateNilaiUjian($siswaId, $ujianId): void
    {
        $ujian = DB::table('ujians')->where('id', $ujianId)->first();
        $rekap = DB::table('nilai_ujians')
            ->where('siswa_id', $siswaId)
            ->where('ujian_id', $ujianId)
            ->first();

        if (!$ujian || !$rekap) {
            return;
        }

        $soalEsaiIds = $this->getSoalEsaiByUjian($ujian)->pluck('id');
        $skorEsaiTotal = (float) DB::table('jawaban_siswas')
            ->where('siswa_id', $siswaId)
            ->where('ujian_id', $ujianId)
            ->when($soalEsaiIds->isNotEmpty(), fn($q) => $q->whereIn('soal_id', $soalEsaiIds))
            ->sum('skor_esai');

        $bobotEsai = (float) ($ujian->bobot_esai ?? 0);
        $nilaiEsai = $bobotEsai > 0 ? min($bobotEsai, $skorEsaiTotal) : $skorEsaiTotal;

        $nilaiPg = (float) ($rekap->nilai_pg ?? 0);
        $nilaiJodoh = (float) ($rekap->nilai_menjodohkan ?? 0);
        $nilaiAkhir = round($nilaiPg + $nilaiJodoh + $nilaiEsai, 2);

        $updateData = [
            'nilai'      => $nilaiAkhir,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('nilai_ujians', 'nilai_esai')) {
            $updateData['nilai_esai'] = round($nilaiEsai, 2);
        }
        if (Schema::hasColumn('nilai_ujians', 'status_kkm')) {
            $kkm = (float) ($ujian->kkm ?? 75);
            $updateData['status_kkm'] = $nilaiAkhir >= $kkm ? 'TUNTAS' : 'REMEDIAL';
        }

        DB::table('nilai_ujians')->where('id', $rekap->id)->update($updateData);
    }

    public function rekapNilai()
    {
        return redirect()->route('korektor.koreksi.index');
    }

    public function cetakNilai($ujianId)
    {
        return redirect()->route('korektor.koreksi.show', $ujianId);
    }

    public function pedomanSoal()
    {
        return redirect()->route('korektor.koreksi.index');
    }
}
