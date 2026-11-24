<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\JawabanSiswa;
use App\Models\NilaiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class UjianController extends Controller
{
    private string $timezone = 'Asia/Jakarta';

    public function verifikasiToken(Request $request)
    {
        $request->validate([
            'token'    => 'required|string|max:10',
            'ujian_id' => 'nullable|integer',
        ], [
            'token.required' => 'Token ujian wajib diisi!',
        ]);

        $siswa = Auth::guard('siswa')->user();

        if (!$siswa) {
            return redirect()->route('siswa.login');
        }

        if (!$siswa->kelas_id) {
            return back()->withInput()->with('error', 'Data kelas siswa tidak ditemukan. Silakan hubungi proktor/admin.');
        }

        $tokenInput = strtoupper(trim($request->token));
        $now = Carbon::now($this->timezone);

        $queryUjian = Ujian::whereRaw('UPPER(TRIM(token)) = ?', [$tokenInput])
            ->where(function ($query) use ($siswa) {
                $query->where('kelas_id', $siswa->kelas_id)
                    ->orWhereNull('kelas_id');
            });

        if ($request->filled('ujian_id')) {
            $queryUjian->where('id', (int) $request->ujian_id);
        }

        $ujian = $queryUjian->first();

        if (!$ujian) {
            return back()->withInput()->with('error', 'Token ujian tidak ditemukan atau ujian tersebut bukan untuk kelas Anda.');
        }

        if ($ujian->status !== 'aktif') {
            return back()->withInput()->with('error', "Ujian '{$ujian->nama_ujian}' saat ini berstatus " . strtoupper($ujian->status) . ' (belum aktif / sudah ditutup).');
        }

        if (!empty($ujian->waktu_mulai)) {
            $waktuMulai = Carbon::parse($ujian->getRawOriginal('waktu_mulai') ?? $ujian->waktu_mulai, $this->timezone);
            if ($now->lt($waktuMulai)) {
                return back()->withInput()->with('error', "Ujian belum dimulai. Jadwal mulai: {$waktuMulai->format('d M Y H:i')} WIB.");
            }
        }

        if (!empty($ujian->waktu_selesai)) {
            $waktuSelesai = Carbon::parse($ujian->getRawOriginal('waktu_selesai') ?? $ujian->waktu_selesai, $this->timezone);
            if ($now->gt($waktuSelesai)) {
                return back()->withInput()->with('error', "Waktu pelaksanaan ujian sudah berakhir pada {$waktuSelesai->format('d M Y H:i')} WIB.");
            }
        }

        $riwayat = NilaiUjian::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->first();

        if ($riwayat && ($riwayat->status === 'selesai' || !empty($riwayat->waktu_selesai))) {
            return back()->withInput()->with('error', 'Anda sudah menyelesaikan sesi ujian ini.');
        }

        if (!$riwayat) {
            $dataAwal = [
                'status'        => 'mengerjakan',
                'jumlah_benar'  => 0,
                'jumlah_salah'  => 0,
                'tidak_dijawab' => 0,
                'nilai'         => 0,
            ];

            if (Schema::hasColumn('nilai_ujians', 'waktu_mulai')) {
                $dataAwal['waktu_mulai'] = $now;
            }

            NilaiUjian::create(array_merge([
                'siswa_id' => $siswa->id,
                'ujian_id' => $ujian->id,
            ], $dataAwal));
        }

        session(['current_ujian_id' => $ujian->id]);

        return redirect()->route('siswa.ujian.mulai')
            ->with('success', 'Token valid! Selamat mengerjakan.');
    }

    public function mulai()
    {
        $this->ensureJawabanDanNilaiColumns();

        $siswa = Auth::guard('siswa')->user();
        $ujianId = session('current_ujian_id');

        if (!$siswa) {
            return redirect()->route('siswa.login');
        }

        if (!$ujianId) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Silakan masukkan token ujian terlebih dahulu.');
        }

        $now = Carbon::now($this->timezone);
        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId);

        $pengerjaan = NilaiUjian::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->first();

        if ($pengerjaan && ($pengerjaan->status === 'selesai' || !empty($pengerjaan->waktu_selesai))) {
            session()->forget('current_ujian_id');
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Anda sudah menyelesaikan ujian ini.');
        }

        if (!$pengerjaan) {
            $dataAwal = [
                'siswa_id'      => $siswa->id,
                'ujian_id'      => $ujian->id,
                'status'        => 'mengerjakan',
                'jumlah_benar'  => 0,
                'jumlah_salah'  => 0,
                'tidak_dijawab' => 0,
                'nilai'         => 0,
            ];
            if (Schema::hasColumn('nilai_ujians', 'waktu_mulai')) {
                $dataAwal['waktu_mulai'] = $now;
            }
            $pengerjaan = NilaiUjian::create($dataAwal);
        }

        $durasiDetik = max(60, ((int) ($ujian->durasi ?? 90)) * 60);
        if (!empty($pengerjaan->waktu_mulai)) {
            $mulaiSiswa = Carbon::parse($pengerjaan->getRawOriginal('waktu_mulai') ?? $pengerjaan->waktu_mulai, $this->timezone);
            $akhirPengerjaan = $mulaiSiswa->copy()->addSeconds($durasiDetik);

            if (!empty($ujian->waktu_selesai)) {
                $batasSesi = Carbon::parse($ujian->getRawOriginal('waktu_selesai') ?? $ujian->waktu_selesai, $this->timezone);
                if ($batasSesi->lt($akhirPengerjaan)) {
                    $akhirPengerjaan = $batasSesi;
                }
            }
            $sisaDetik = max(0, (int) $now->diffInSeconds($akhirPengerjaan, false));
        } else {
            $sisaDetik = $durasiDetik;
        }

        $soals = $this->getOrderedSoals($ujian, (int) $siswa->kelas_id, true, (int) $siswa->id);

        $jawabanExisting = JawabanSiswa::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->get()
            ->keyBy('soal_id');

        $jawabanTersimpan = [];
        foreach ($jawabanExisting as $soalId => $row) {
            $raw = ($row->jawaban_teks !== null && $row->jawaban_teks !== '')
                ? $row->jawaban_teks
                : $row->jawaban;

            if ($raw === null || $raw === '') {
                continue;
            }

            $decoded = is_string($raw) ? json_decode($raw, true) : null;
            $jawabanTersimpan[$soalId] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                ? $decoded
                : $raw;
        }

        return view('siswa.ujian.mulai', compact(
            'siswa',
            'ujian',
            'soals',
            'jawabanExisting',
            'jawabanTersimpan',
            'pengerjaan',
            'sisaDetik'
        ));
    }

    public function simpanJawaban(Request $request)
    {
        $this->ensureJawabanDanNilaiColumns();

        $request->validate([
            'ujian_id' => 'required|integer|exists:ujians,id',
            'soal_id'  => 'required|integer|exists:soals,id',
            'jawaban'  => 'nullable',
        ]);

        $siswa = Auth::guard('siswa')->user();
        if (!$siswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi siswa tidak ditemukan.'
            ], 401);
        }

        $ujianId = (int) $request->ujian_id;
        $soal = Soal::findOrFail((int) $request->soal_id);

        $tipe = $this->detectTipeSoal($soal);
        $rawJawaban = $request->input('jawaban');
        $payloadSimpan = [
            'is_benar' => 0,
        ];

        if ($tipe === 'menjodohkan') {
            $arrJawaban = is_array($rawJawaban)
                ? $rawJawaban
                : (is_string($rawJawaban) ? (json_decode($rawJawaban, true) ?? []) : []);

            if (!is_array($arrJawaban)) {
                $arrJawaban = [];
            }

            $kunciPasangan = method_exists($soal, 'getPasanganList')
                ? $soal->getPasanganList()
                : (is_string($soal->pasangan_menjodohkan) ? (json_decode($soal->pasangan_menjodohkan, true) ?? []) : ($soal->pasangan_menjodohkan ?? []));

            $totalPasangan = is_array($kunciPasangan) ? count($kunciPasangan) : 0;
            $cocok = 0;

            if (is_array($kunciPasangan)) {
                foreach ($kunciPasangan as $pair) {
                    $kiri = trim((string) ($pair['kiri'] ?? ''));
                    $kananBenar = trim((string) ($pair['kanan'] ?? ''));
                    $jawabSiswa = trim((string) ($arrJawaban[$kiri] ?? ''));

                    if ($kiri !== '' && $kananBenar !== '' && mb_strtolower($jawabSiswa) === mb_strtolower($kananBenar)) {
                        $cocok++;
                    }
                }
            }

            $jsonString = json_encode($arrJawaban, JSON_UNESCAPED_UNICODE);

            // Kosongkan kolom 'jawaban' (VARCHAR 10) agar tidak terjadi Data too long
            $payloadSimpan['jawaban'] = null;
            $payloadSimpan['jawaban_teks'] = $jsonString;
            $payloadSimpan['is_benar'] = ($totalPasangan > 0 && $cocok === $totalPasangan) ? 1 : 0;
        } elseif ($tipe === 'esai') {
            $teksEsai = trim((string) ($rawJawaban ?? ''));

            // Kosongkan kolom 'jawaban' (VARCHAR 10) dan simpan uraian panjang ke 'jawaban_teks'
            $payloadSimpan['jawaban'] = null;
            $payloadSimpan['jawaban_teks'] = $teksEsai;
            $payloadSimpan['is_benar'] = 0;
        } else {
            $huruf = strtoupper(trim((string) ($rawJawaban ?? '')));
            $kunci = strtoupper(trim((string) ($soal->kunci_jawaban ?? '')));
            $isBenar = ($huruf !== '' && $kunci !== '' && $huruf === $kunci) ? 1 : 0;

            $payloadSimpan['jawaban'] = $huruf !== '' ? mb_substr($huruf, 0, 10) : null;
            $payloadSimpan['jawaban_teks'] = null;
            $payloadSimpan['is_benar'] = $isBenar;
        }

        // Gunakan DB::table agar aman meskipun $fillable di model JawabanSiswa belum diperbarui
        $existing = DB::table('jawaban_siswas')
            ->where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujianId)
            ->where('soal_id', $soal->id)
            ->first();

        if ($existing) {
            DB::table('jawaban_siswas')
                ->where('id', $existing->id)
                ->update(array_merge($payloadSimpan, [
                    'updated_at' => now(),
                ]));
        } else {
            DB::table('jawaban_siswas')->insert(array_merge([
                'siswa_id'   => $siswa->id,
                'ujian_id'   => $ujianId,
                'soal_id'    => $soal->id,
                'created_at' => now(),
                'updated_at' => now(),
            ], $payloadSimpan));
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Jawaban berhasil disimpan',
        ]);
    }

    public function selesaiUjian(Request $request)
    {
        $this->ensureJawabanDanNilaiColumns();

        $request->validate([
            'ujian_id' => 'required|integer|exists:ujians,id',
        ]);

        $siswa = Auth::guard('siswa')->user();
        if (!$siswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi siswa tidak ditemukan.'
            ], 401);
        }

        $ujian = Ujian::findOrFail((int) $request->ujian_id);
        $soals = $this->getOrderedSoals($ujian, (int) $siswa->kelas_id, false, (int) $siswa->id);
        $totalSoal = $soals->count();

        $jawabans = JawabanSiswa::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->get()
            ->keyBy('soal_id');

        $jumlahBenar = 0;
        $jumlahSalah = 0;

        $totalPg = 0;
        $benarPg = 0;
        $totalJodoh = 0;
        $benarJodoh = 0;
        $totalEsai = 0;
        $skorEsaiTotal = 0;

        foreach ($soals as $s) {
            $tipe = $this->detectTipeSoal($s);
            $jw = $jawabans->get($s->id);
            $teksJawab = $jw ? trim((string) ($jw->jawaban_teks ?? $jw->jawaban ?? '')) : '';
            $terjawab = ($teksJawab !== '' && $teksJawab !== '[]' && $teksJawab !== '{}');

            if ($tipe === 'menjodohkan') {
                $totalJodoh++;
                if ($terjawab && (int) ($jw->is_benar ?? 0) === 1) {
                    $benarJodoh++;
                }
            } elseif ($tipe === 'esai') {
                $totalEsai++;
                if ($jw && isset($jw->skor_esai) && is_numeric($jw->skor_esai)) {
                    $skorEsaiTotal += (float) $jw->skor_esai;
                }
            } else {
                $totalPg++;
                if ($terjawab && (int) ($jw->is_benar ?? 0) === 1) {
                    $benarPg++;
                }
            }

            if (!$terjawab) {
                continue;
            }

            // Untuk esai yang belum dikoreksi, jangan langsung dianggap salah permanen
            if ($tipe === 'esai') {
                if ($jw && isset($jw->skor_esai) && (float) $jw->skor_esai > 0) {
                    $jumlahBenar++;
                }
            } else {
                if ((int) ($jw->is_benar ?? 0) === 1) {
                    $jumlahBenar++;
                } else {
                    $jumlahSalah++;
                }
            }
        }

        $tidakDijawab = max(0, $totalSoal - $jawabans->filter(function ($jw) {
            $t = trim((string) ($jw->jawaban_teks ?? $jw->jawaban ?? ''));
            return $t !== '' && $t !== '[]' && $t !== '{}';
        })->count());

        $bobotPg    = (float) ($ujian->bobot_pg ?? 100);
        $bobotJodoh = (float) ($ujian->bobot_menjodohkan ?? 0);
        $bobotEsai  = (float) ($ujian->bobot_esai ?? 0);

        $nilaiPg    = $totalPg > 0 ? ($benarPg / $totalPg) * $bobotPg : 0;
        $nilaiJodoh = $totalJodoh > 0 ? ($benarJodoh / $totalJodoh) * $bobotJodoh : 0;
        $nilaiEsai  = ($totalEsai > 0 && $bobotEsai > 0)
            ? ($skorEsaiTotal / ($totalEsai * 100)) * $bobotEsai
            : $skorEsaiTotal;

        $nilaiAkhir = ($bobotPg + $bobotJodoh + $bobotEsai) > 0
            ? ($nilaiPg + $nilaiJodoh + $nilaiEsai)
            : ($totalSoal > 0 ? ($jumlahBenar / $totalSoal) * 100 : 0);

        $nilaiBulat = round($nilaiAkhir, 2);

        $nilaiData = [
            'jumlah_benar'  => $jumlahBenar,
            'jumlah_salah'  => $jumlahSalah,
            'tidak_dijawab' => $tidakDijawab,
            'nilai'         => $nilaiBulat,
            'waktu_selesai' => Carbon::now($this->timezone),
            'status'        => 'selesai',
        ];

        if (Schema::hasColumn('nilai_ujians', 'nilai_pg')) {
            $nilaiData['nilai_pg'] = round($nilaiPg, 2);
        }
        if (Schema::hasColumn('nilai_ujians', 'nilai_menjodohkan')) {
            $nilaiData['nilai_menjodohkan'] = round($nilaiJodoh, 2);
        }
        if (Schema::hasColumn('nilai_ujians', 'nilai_esai')) {
            $nilaiData['nilai_esai'] = round($nilaiEsai, 2);
        }
        if (Schema::hasColumn('nilai_ujians', 'status_kkm')) {
            $kkm = (float) ($ujian->kkm ?? 75);
            $nilaiData['status_kkm'] = $nilaiBulat >= $kkm ? 'TUNTAS' : 'REMEDIAL';
        }

        NilaiUjian::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'ujian_id' => $ujian->id,
            ],
            $nilaiData
        );

        session()->forget('current_ujian_id');

        return response()->json([
            'status'  => 'success',
            'message' => 'Ujian selesai, nilai berhasil dihitung.',
            'data'    => [
                'nilai'         => $nilaiBulat,
                'jumlah_benar'  => $jumlahBenar,
                'jumlah_salah'  => $jumlahSalah,
                'tidak_dijawab' => $tidakDijawab,
            ],
        ]);
    }

    private function detectTipeSoal(Soal $soal): string
    {
        if (method_exists($soal, 'getTipeNormalized')) {
            $t = strtolower(trim((string) $soal->getTipeNormalized()));
            return in_array($t, ['essay', 'uraian', 'esai'], true) ? 'esai'
                : (in_array($t, ['jodohkan', 'match', 'menjodohkan'], true) ? 'menjodohkan' : 'pg');
        }

        $raw = strtolower(trim((string) ($soal->tipe_soal ?? $soal->tipe ?? 'pg')));
        if (in_array($raw, ['essay', 'uraian', 'esai'], true)) {
            return 'esai';
        }
        if (in_array($raw, ['jodohkan', 'match', 'menjodohkan'], true)) {
            return 'menjodohkan';
        }

        return 'pg';
    }

    private function getTipeAktifList(Ujian $ujian): array
    {
        $rawList = method_exists($ujian, 'getTipeSoalAktif')
            ? (array) $ujian->getTipeSoalAktif()
            : ['pg', 'menjodohkan', 'esai'];

        $normalized = [];
        foreach ($rawList as $t) {
            $val = strtolower(trim((string) $t));
            if (in_array($val, ['essay', 'uraian', 'esai'], true)) {
                $val = 'esai';
            } elseif (in_array($val, ['jodohkan', 'match', 'menjodohkan'], true)) {
                $val = 'menjodohkan';
            } elseif ($val === '') {
                $val = 'pg';
            }
            if (!in_array($val, $normalized, true)) {
                $normalized[] = $val;
            }
        }

        return !empty($normalized) ? $normalized : ['pg', 'menjodohkan', 'esai'];
    }

    private function getOrderedSoals(Ujian $ujian, int $kelasId, bool $applyOrdering = true, int $siswaId = 0)
    {
        $targetKelasId = $ujian->kelas_id ?: $kelasId;
        $tipeAktif = $this->getTipeAktifList($ujian);

        $querySoal = Soal::query();

        if (Schema::hasColumn('soals', 'ujian_id') && Soal::where('ujian_id', $ujian->id)->exists()) {
            $querySoal->where('ujian_id', $ujian->id);
        } else {
            $querySoal->where('mapel_id', $ujian->mapel_id)
                ->where(function ($q) use ($targetKelasId) {
                    $q->where('kelas_id', $targetKelasId)
                        ->orWhereNull('kelas_id');
                });
        }

        $querySoal->where(function ($q) use ($tipeAktif) {
            if (Schema::hasColumn('soals', 'tipe_soal')) {
                $q->whereIn('tipe_soal', $tipeAktif);
                if (in_array('pg', $tipeAktif, true)) {
                    $q->orWhereNull('tipe_soal')->orWhere('tipe_soal', '');
                }
            } elseif (Schema::hasColumn('soals', 'tipe')) {
                $q->whereIn('tipe', $tipeAktif);
                if (in_array('pg', $tipeAktif, true)) {
                    $q->orWhereNull('tipe')->orWhere('tipe', '');
                }
            } elseif (!in_array('pg', $tipeAktif, true)) {
                $q->whereRaw('1 = 0');
            }
        });

        $collection = $querySoal->get();

        if (!$applyOrdering) {
            return $collection->values();
        }

        $isAcak = ($ujian->acak_soal == 1 || $ujian->acak_soal === true || $ujian->acak_soal === 'true' || (string) $ujian->acak_soal === '1');
        $ordered = collect();

        foreach ($tipeAktif as $tipe) {
            $group = $collection->filter(fn($item) => $this->detectTipeSoal($item) === $tipe)->values();

            if ($isAcak) {
                $group = $group->shuffle();
            } else {
                $group = $group->sortBy(function ($item) {
                    return [
                        is_numeric($item->nomor_soal) ? (int) $item->nomor_soal : PHP_INT_MAX,
                        (int) $item->id,
                    ];
                })->values();
            }

            $ordered = $ordered->concat($group);
        }

        return $ordered->values();
    }

    private function ensureJawabanDanNilaiColumns(): void
    {
        if (Cache::get('cbt_columns_ensured_v2')) {
            return;
        }

        if (Schema::hasTable('jawaban_siswas')) {
            Schema::table('jawaban_siswas', function (Blueprint $table) {
                if (!Schema::hasColumn('jawaban_siswas', 'jawaban_teks')) {
                    $table->longText('jawaban_teks')->nullable();
                }
                if (!Schema::hasColumn('jawaban_siswas', 'skor_esai')) {
                    $table->decimal('skor_esai', 5, 2)->nullable();
                }
            });
        }

        if (Schema::hasTable('nilai_ujians')) {
            Schema::table('nilai_ujians', function (Blueprint $table) {
                if (!Schema::hasColumn('nilai_ujians', 'waktu_mulai')) {
                    $table->timestamp('waktu_mulai')->nullable();
                }
                if (!Schema::hasColumn('nilai_ujians', 'nilai_pg')) {
                    $table->decimal('nilai_pg', 5, 2)->default(0);
                }
                if (!Schema::hasColumn('nilai_ujians', 'nilai_menjodohkan')) {
                    $table->decimal('nilai_menjodohkan', 5, 2)->default(0);
                }
                if (!Schema::hasColumn('nilai_ujians', 'nilai_esai')) {
                    $table->decimal('nilai_esai', 5, 2)->default(0);
                }
                if (!Schema::hasColumn('nilai_ujians', 'status_kkm')) {
                    $table->string('status_kkm', 20)->nullable();
                }
            });
        }

        Cache::put('cbt_columns_ensured_v2', true, now()->addHours(24));
    }
}
