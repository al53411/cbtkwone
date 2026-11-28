<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\Soal;
use App\Models\NilaiUjian;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureUjianMultiTipeColumns();

        $query = Ujian::with(['mapel', 'kelas']);

        // 1. Pencarian Nama Ujian, Kode Soal, atau Nama Mapel
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_ujian', 'LIKE', "%{$search}%")
                    ->orWhere('kode_soal', 'LIKE', "%{$search}%")
                    ->orWhereHas('mapel', function ($qm) use ($search) {
                        $qm->where('nama_mapel', 'LIKE', "%{$search}%");
                    });
            });
        }

        // 2. Filter Berdasarkan Kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // 3. Filter Berdasarkan Status (draft, aktif, selesai)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $mapels       = Mapel::where('status', 'aktif')->orderBy('nama_mapel', 'asc')->get();
        $kelases      = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel    = $mapels;
        $listKelas    = $kelases;
        $listKodeSoal = $this->getAvailableKodeSoals();

        return view('superadmin.ujian.index', compact(
            'ujians',
            'mapels',
            'kelases',
            'listMapel',
            'listKelas',
            'listKodeSoal'
        ));
    }

    /**
     * Endpoint data JSON untuk modal edit (Fetch API / AJAX)
     */
    public function show($id)
    {
        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($id);
        return response()->json($ujian);
    }

    /**
     * Halaman pengaturan / edit sesi ujian
     */
    public function edit($id)
    {
        $this->ensureUjianMultiTipeColumns();

        $ujian        = Ujian::with(['mapel', 'kelas'])->findOrFail($id);
        $ujianItem    = $ujian;
        $mapels       = Mapel::where('status', 'aktif')->orderBy('nama_mapel', 'asc')->get();
        $kelases      = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel    = $mapels;
        $listKelas    = $kelases;
        $listKodeSoal = $this->getAvailableKodeSoals();

        $viewName = view()->exists('superadmin.ujian.settings')
            ? 'superadmin.ujian.settings'
            : 'superadmin.ujian.edit';

        return view($viewName, compact(
            'ujian',
            'ujianItem',
            'mapels',
            'kelases',
            'listMapel',
            'listKelas',
            'listKodeSoal'
        ));
    }

    /**
     * Endpoint generate token ujian unik
     */
    public function generateToken()
    {
        return response()->json([
            'status' => 'success',
            'token'  => $this->createUniqueToken(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureUjianMultiTipeColumns();
        $validated = $this->validateRequest($request);

        // Parse & validasi komposisi tipe soal + bobot 100%
        $komposisi = $this->parseKomposisiDanBobot($request);
        if (!$komposisi['valid']) {
            return redirect()->back()->withInput()->with('error', $komposisi['message']);
        }

        $tokenUjian = !empty($validated['token'])
            ? strtoupper(trim($validated['token']))
            : $this->createUniqueToken();

        $dataUjian = [
            'nama_ujian'         => $validated['nama_ujian'],
            'kelas_id'           => $validated['kelas_id'],
            'mapel_id'           => $validated['mapel_id'],
            'kode_soal'          => !empty($validated['kode_soal']) ? trim($validated['kode_soal']) : null,
            'waktu_mulai'        => $validated['waktu_mulai'],
            'waktu_selesai'      => $validated['waktu_selesai'],
            'durasi'             => (int) $validated['durasi'],
            'token'              => $tokenUjian,
            'status'             => $validated['status'],
            'aturan_ujian'       => !empty($validated['aturan_ujian']) ? trim($validated['aturan_ujian']) : null,
            'acak_soal'          => $this->boolValue($request->has('acak_soal')),
            'acak_jawaban'       => $this->boolValue($request->has('acak_jawaban')),
            'tampilkan_nilai'    => $this->boolValue(false),
            'tampilkan_soal'     => $this->boolValue(false),
            'tampil_pg'          => $this->boolValue($komposisi['tampil_pg']),
            'tampil_esai'        => $this->boolValue($komposisi['tampil_esai']),
            'tampil_menjodohkan' => $this->boolValue($komposisi['tampil_menjodohkan']),
            'bobot_pg'           => $komposisi['bobot_pg'],
            'bobot_esai'         => $komposisi['bobot_esai'],
            'bobot_menjodohkan'  => $komposisi['bobot_menjodohkan'],
            'kkm'                => $komposisi['kkm'],
        ];

        Ujian::create($dataUjian);

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Jadwal Ujian beserta paket kode soal & KKM berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $this->ensureUjianMultiTipeColumns();
        $ujian     = Ujian::findOrFail($id);
        $validated = $this->validateRequest($request);

        // Parse & validasi komposisi tipe soal + bobot 100%
        $komposisi = $this->parseKomposisiDanBobot($request);
        if (!$komposisi['valid']) {
            return redirect()->back()->withInput()->with('error', $komposisi['message']);
        }

        $updateData = [
            'nama_ujian'         => $validated['nama_ujian'],
            'kelas_id'           => $validated['kelas_id'],
            'mapel_id'           => $validated['mapel_id'],
            'kode_soal'          => !empty($validated['kode_soal']) ? trim($validated['kode_soal']) : null,
            'waktu_mulai'        => $validated['waktu_mulai'],
            'waktu_selesai'      => $validated['waktu_selesai'],
            'durasi'             => (int) $validated['durasi'],
            'token'              => !empty($validated['token']) ? strtoupper(trim($validated['token'])) : $ujian->token,
            'status'             => $validated['status'],
            'aturan_ujian'       => !empty($validated['aturan_ujian']) ? trim($validated['aturan_ujian']) : null,
            'acak_soal'          => $this->boolValue($request->has('acak_soal')),
            'acak_jawaban'       => $this->boolValue($request->has('acak_jawaban')),
            'tampil_pg'          => $this->boolValue($komposisi['tampil_pg']),
            'tampil_esai'        => $this->boolValue($komposisi['tampil_esai']),
            'tampil_menjodohkan' => $this->boolValue($komposisi['tampil_menjodohkan']),
            'bobot_pg'           => $komposisi['bobot_pg'],
            'bobot_esai'         => $komposisi['bobot_esai'],
            'bobot_menjodohkan'  => $komposisi['bobot_menjodohkan'],
            'kkm'                => $komposisi['kkm'],
        ];

        if ($request->has('tampilkan_nilai')) {
            $updateData['tampilkan_nilai'] = $this->boolValue($request->boolean('tampilkan_nilai'));
        }

        if ($request->has('tampilkan_soal')) {
            $updateData['tampilkan_soal'] = $this->boolValue($request->boolean('tampilkan_soal'));
        }

        $ujian->update($updateData);

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Jadwal Ujian dan paket kode soal berhasil diperbarui!');
    }

    /**
     * Update status ujian (draft, aktif, selesai)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,aktif,selesai',
        ]);

        $ujian = Ujian::findOrFail($id);
        $ujian->update([
            'status' => $request->status,
        ]);

        return redirect()->back()
            ->with('success', "Status ujian '{$ujian->nama_ujian}' berhasil diubah menjadi {$request->status}.");
    }

    public function destroy($id)
    {
        $ujian = Ujian::findOrFail($id);
        $ujian->delete();

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Jadwal Ujian berhasil dihapus!');
    }

    /**
     * Toggle Rilis / Kunci Nilai Ujian untuk ditampilkan di panel Admin
     */
    public function toggleRilisNilai($id)
    {
        $ujian      = Ujian::findOrFail($id);
        $statusBaru = method_exists($ujian, 'isNilaiDirilis')
            ? !$ujian->isNilaiDirilis()
            : !((bool) ($ujian->tampilkan_nilai ?? false));

        $ujian->update([
            'tampilkan_nilai' => $this->boolValue($statusBaru),
        ]);

        $pesan = $statusBaru
            ? "Nilai ujian '{$ujian->nama_ujian}' berhasil dirilis ke menu Admin."
            : "Nilai ujian '{$ujian->nama_ujian}' dikunci kembali (Dalam Penilaian).";

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Toggle Izin Tampilkan / Kunci Daftar Soal ke Menu Admin Sekolah
     */
    public function toggleTampilSoal($id)
    {
        $this->ensureUjianMultiTipeColumns();

        $ujian      = Ujian::findOrFail($id);
        $statusBaru = !((bool) ($ujian->tampilkan_soal ?? false));

        $ujian->update([
            'tampilkan_soal' => $this->boolValue($statusBaru),
        ]);

        $pesan = $statusBaru
            ? "Akses lihat butir soal untuk ujian '{$ujian->nama_ujian}' berhasil DIBUKA bagi Admin Sekolah."
            : "Akses lihat butir soal untuk ujian '{$ujian->nama_ujian}' DIKUNCI kembali dari Admin Sekolah.";

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Menampilkan rekap nilai siswa (Semua ujian atau per sesi ujian)
     */
    public function rekapNilai(Request $request, $id = null)
    {
        $ujianId = $id ?? $request->ujian_id;
        $ujian   = $ujianId ? Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId) : null;

        $query = NilaiUjian::with(['siswa.kelas', 'siswa.sekolah', 'ujian.mapel']);

        if ($ujian) {
            $query->where('ujian_id', $ujian->id);
        }

        if ($request->filled('sekolah_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('sekolah_id', $request->sekolah_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $rekapNilais = $query->orderBy('nilai', 'desc')->get();

        $listUjian = Ujian::with(['mapel', 'kelas'])->orderBy('created_at', 'desc')->get();
        $sekolahs  = Sekolah::orderBy('nama_sekolah', 'asc')->get();
        $kelases   = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('superadmin.ujian.rekap', compact('ujian', 'rekapNilais', 'listUjian', 'sekolahs', 'kelases'));
    }

    /**
     * Cetak rekap nilai ujian (Semua ujian atau per sesi ujian)
     */
    public function cetakRekap(Request $request, $id = null)
    {
        $ujianId = $id ?? $request->ujian_id;
        $ujian   = $ujianId ? Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId) : null;

        $query = NilaiUjian::with(['siswa.kelas', 'siswa.sekolah', 'ujian.mapel']);

        if ($ujian) {
            $query->where('ujian_id', $ujian->id);
        }

        if ($request->filled('sekolah_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('sekolah_id', $request->sekolah_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $rekapNilais = $query->orderBy('nilai', 'desc')->get();

        return view('superadmin.ujian.cetak-rekap', compact('ujian', 'rekapNilais'));
    }

    /**
     * Monitoring Arsip Berita Acara Semua Sekolah (Khusus Superadmin)
     */
    public function superadminBeritaAcara(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'superadmin') {
            abort(403, 'Halaman ini khusus untuk Superadmin.');
        }

        $listSekolah = Sekolah::orderBy('nama_sekolah', 'asc')->get();

        $hasUjiansTable = Schema::hasTable('ujians');
        $hasFileCol     = $hasUjiansTable && Schema::hasColumn('ujians', 'file_berita_acara');
        $hasSekolahCol  = $hasUjiansTable && Schema::hasColumn('ujians', 'sekolah_id');
        $hasUploadedAt  = $hasUjiansTable && Schema::hasColumn('ujians', 'uploaded_ba_at');

        $query = Ujian::with(['kelas', 'mapel'])
            ->withCount('nilaiUjians');

        if (method_exists(Ujian::class, 'sekolah')) {
            $query->with('sekolah');
        }

        // Filter berdasarkan sekolah (cek sekolah_id di ujians ATAU sekolah siswa peserta)
        if ($request->filled('sekolah_id')) {
            $sekolahId = $request->sekolah_id;
            $query->where(function ($q) use ($sekolahId, $hasSekolahCol) {
                if ($hasSekolahCol) {
                    $q->where('sekolah_id', $sekolahId);
                }
                $q->orWhereHas('nilaiUjians.siswa', function ($qs) use ($sekolahId) {
                    $qs->where('sekolah_id', $sekolahId);
                });
            });
        }

        // Filter status upload (default: tampilkan yang sudah upload)
        if ($hasFileCol) {
            if ($request->input('status_upload') === 'belum') {
                $query->whereNull('file_berita_acara');
            } elseif ($request->input('status_upload') === 'semua') {
                // Tampilkan semua sesi ujian
            } else {
                $query->whereNotNull('file_berita_acara');
            }
        }

        // Pencarian nama ujian / mapel
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_ujian', 'LIKE', "%{$search}%")
                    ->orWhereHas('mapel', fn($qm) => $qm->where('nama_mapel', 'LIKE', "%{$search}%"));
            });
        }

        $orderCol = ($hasFileCol && $hasUploadedAt) ? 'uploaded_ba_at' : 'created_at';
        $ujians = $query->orderByDesc($orderCol)
            ->paginate(15)
            ->withQueryString();

        // Statistik ringkas arsip berita acara
        $totalSudahUpload = $hasFileCol ? Ujian::whereNotNull('file_berita_acara')->count() : 0;
        $totalBelumUpload = $hasFileCol ? Ujian::whereNull('file_berita_acara')->count() : Ujian::count();

        return view('superadmin.berita-acara.index', compact(
            'ujians',
            'listSekolah',
            'totalSudahUpload',
            'totalBelumUpload'
        ));
    }

    /**
     * Helper private untuk mengambil daftar paket kode_soal unik beserta jumlah butir soalnya
     */
    private function getAvailableKodeSoals()
    {
        if (!Schema::hasTable('soals') || !Schema::hasColumn('soals', 'kode_soal')) {
            return collect();
        }

        return Soal::select('kode_soal', 'mapel_id', 'kelas_id', DB::raw('COUNT(*) as total_soal'))
            ->whereNotNull('kode_soal')
            ->where('kode_soal', '!=', '')
            ->groupBy('kode_soal', 'mapel_id', 'kelas_id')
            ->orderBy('kode_soal', 'asc')
            ->get();
    }

    /**
     * Helper private untuk format boolean aman lintas driver database (MySQL/MariaDB & PostgreSQL)
     */
    private function boolValue(bool $value): mixed
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::raw($value ? 'true' : 'false');
        }

        return $value ? 1 : 0;
    }

    /**
     * Helper private untuk membuat token unik 6 karakter
     */
    private function createUniqueToken(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $token = '';
            for ($i = 0; $i < 6; $i++) {
                $token .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Ujian::where('token', $token)->exists());

        return $token;
    }

    /**
     * Helper private untuk memastikan kolom kode_soal, multi-tipe soal, bobot, & KKM tersedia di tabel ujians
     */
    private function ensureUjianMultiTipeColumns(): void
    {
        if (Cache::get('superadmin_ujian_columns_v2')) {
            return;
        }

        if (Schema::hasTable('ujians')) {
            Schema::table('ujians', function (Blueprint $table) {
                if (!Schema::hasColumn('ujians', 'kode_soal')) {
                    $table->string('kode_soal', 100)->nullable()->after('mapel_id');
                }
                if (!Schema::hasColumn('ujians', 'tampilkan_soal')) {
                    $table->boolean('tampilkan_soal')->default(false);
                }
                if (!Schema::hasColumn('ujians', 'tampil_pg')) {
                    $table->boolean('tampil_pg')->default(true);
                }
                if (!Schema::hasColumn('ujians', 'tampil_esai')) {
                    $table->boolean('tampil_esai')->default(false);
                }
                if (!Schema::hasColumn('ujians', 'tampil_menjodohkan')) {
                    $table->boolean('tampil_menjodohkan')->default(false);
                }
                if (!Schema::hasColumn('ujians', 'bobot_pg')) {
                    $table->integer('bobot_pg')->default(100);
                }
                if (!Schema::hasColumn('ujians', 'bobot_esai')) {
                    $table->integer('bobot_esai')->default(0);
                }
                if (!Schema::hasColumn('ujians', 'bobot_menjodohkan')) {
                    $table->integer('bobot_menjodohkan')->default(0);
                }
                if (!Schema::hasColumn('ujians', 'kkm')) {
                    $table->decimal('kkm', 5, 2)->default(75.00);
                }
            });
        }

        Cache::put('superadmin_ujian_columns_v2', true, now()->addHours(24));
    }

    /**
     * Helper private untuk menghitung & memvalidasi pilihan tipe soal, bobot (harus 100%), dan KKM
     */
    private function parseKomposisiDanBobot(Request $request): array
    {
        $tampilPg          = $request->has('tampil_pg');
        $tampilEsai        = $request->has('tampil_esai');
        $tampilMenjodohkan = $request->has('tampil_menjodohkan');

        // Fallback jika tidak ada checkbox tipe soal yang dikirim (misalnya form lama) -> default PG 100%
        if (!$tampilPg && !$tampilEsai && !$tampilMenjodohkan) {
            $tampilPg = true;
        }

        $bobotPg          = $tampilPg ? (int) $request->input('bobot_pg', 100) : 0;
        $bobotEsai        = $tampilEsai ? (int) $request->input('bobot_esai', 0) : 0;
        $bobotMenjodohkan = $tampilMenjodohkan ? (int) $request->input('bobot_menjodohkan', 0) : 0;

        // Jika hanya 1 tipe soal yang aktif dan user lupa mengisi bobotnya, otomatis set ke 100%
        $jumlahAktif = (int) $tampilPg + (int) $tampilEsai + (int) $tampilMenjodohkan;
        if ($jumlahAktif === 1 && ($bobotPg + $bobotEsai + $bobotMenjodohkan) === 0) {
            if ($tampilPg) $bobotPg = 100;
            if ($tampilEsai) $bobotEsai = 100;
            if ($tampilMenjodohkan) $bobotMenjodohkan = 100;
        }

        if (($bobotPg + $bobotEsai + $bobotMenjodohkan) !== 100) {
            return [
                'valid'   => false,
                'message' => 'Total pembagian Bobot Nilai (PG + Menjodohkan + Esai) harus berjumlah tepat 100%.',
            ];
        }

        return [
            'valid'              => true,
            'tampil_pg'          => $tampilPg,
            'tampil_esai'        => $tampilEsai,
            'tampil_menjodohkan' => $tampilMenjodohkan,
            'bobot_pg'           => $bobotPg,
            'bobot_esai'         => $bobotEsai,
            'bobot_menjodohkan'  => $bobotMenjodohkan,
            'kkm'                => (float) $request->input('kkm', 75),
        ];
    }

    /**
     * Helper private untuk validasi request form Ujian
     */
    private function validateRequest(Request $request)
    {
        return $request->validate([
            'nama_ujian'         => 'required|string|max:255',
            'kelas_id'           => 'required|exists:kelas,id',
            'mapel_id'           => 'required|exists:mapels,id',
            'kode_soal'          => 'nullable|string|max:100',
            'waktu_mulai'        => 'required|date',
            'waktu_selesai'      => 'required|date|after:waktu_mulai',
            'durasi'             => 'required|integer|min:1',
            'token'              => 'nullable|string|max:10',
            'status'             => 'required|in:draft,aktif,selesai',
            'aturan_ujian'       => 'nullable|string',
            'acak_soal'          => 'nullable',
            'acak_jawaban'       => 'nullable',
            'tampil_pg'          => 'nullable',
            'tampil_esai'        => 'nullable',
            'tampil_menjodohkan' => 'nullable',
            'bobot_pg'           => 'nullable|integer|min:0|max:100',
            'bobot_esai'         => 'nullable|integer|min:0|max:100',
            'bobot_menjodohkan'  => 'nullable|integer|min:0|max:100',
            'kkm'                => 'nullable|numeric|min:0|max:100',
        ]);
    }
}
