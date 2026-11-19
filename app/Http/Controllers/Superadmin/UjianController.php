<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\NilaiUjian;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $query = Ujian::with(['mapel', 'kelas']);

        // 1. Pencarian Nama Ujian atau Nama Mapel
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_ujian', 'LIKE', "%{$search}%")
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

        $mapels    = Mapel::where('status', 'aktif')->get();
        $kelases   = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel = $mapels;
        $listKelas = $kelases;

        return view('superadmin.ujian.index', compact(
            'ujians',
            'mapels',
            'kelases',
            'listMapel',
            'listKelas'
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

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $tokenUjian = !empty($validated['token'])
            ? strtoupper(trim($validated['token']))
            : strtoupper(Str::random(6));

        $acakSoalVal    = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        $dataUjian = [
            'nama_ujian'      => $validated['nama_ujian'],
            'kelas_id'        => $validated['kelas_id'],
            'mapel_id'        => $validated['mapel_id'],
            'waktu_mulai'     => $validated['waktu_mulai'],
            'waktu_selesai'   => $validated['waktu_selesai'],
            'durasi'          => $validated['durasi'],
            'token'           => $tokenUjian,
            'status'          => $validated['status'],
            'aturan_ujian'    => !empty($validated['aturan_ujian']) ? trim($validated['aturan_ujian']) : null,
            'acak_soal'       => DB::raw($acakSoalVal),
            'acak_jawaban'    => DB::raw($acakJawabanVal),
            'tampilkan_nilai' => DB::raw('false'), // Default terkunci saat ujian baru dibuat
        ];

        if (Schema::hasColumn('ujians', 'tampilkan_soal')) {
            $dataUjian['tampilkan_soal'] = DB::raw('false');
        }

        Ujian::create($dataUjian);

        return redirect()->route('superadmin.ujian.index')->with('success', 'Jadwal Ujian berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $ujian     = Ujian::findOrFail($id);
        $validated = $this->validateRequest($request);

        $acakSoalVal    = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        $ujian->update([
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'durasi'        => $validated['durasi'],
            'token'         => !empty($validated['token']) ? strtoupper(trim($validated['token'])) : $ujian->token,
            'status'        => $validated['status'],
            'aturan_ujian'  => !empty($validated['aturan_ujian']) ? trim($validated['aturan_ujian']) : null,
            'acak_soal'     => DB::raw($acakSoalVal),
            'acak_jawaban'  => DB::raw($acakJawabanVal),
        ]);

        return redirect()->route('superadmin.ujian.index')->with('success', 'Jadwal Ujian berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $ujian = Ujian::findOrFail($id);
        $ujian->delete();

        return redirect()->route('superadmin.ujian.index')->with('success', 'Jadwal Ujian berhasil dihapus!');
    }

    /**
     * Toggle Rilis / Kunci Nilai Ujian untuk ditampilkan di panel Admin
     */
    public function toggleRilisNilai($id)
    {
        $ujian      = Ujian::findOrFail($id);
        $statusBaru = !$ujian->isNilaiDirilis();

        DB::table('ujians')->where('id', $id)->update([
            'tampilkan_nilai' => DB::raw($statusBaru ? 'true' : 'false'),
            'updated_at'      => now(),
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
        if (Schema::hasTable('ujians') && !Schema::hasColumn('ujians', 'tampilkan_soal')) {
            Schema::table('ujians', function (Blueprint $table) {
                $table->boolean('tampilkan_soal')->default(false);
            });
        }

        $ujian      = Ujian::findOrFail($id);
        $statusBaru = !((bool) ($ujian->tampilkan_soal ?? false));

        DB::table('ujians')->where('id', $id)->update([
            'tampilkan_soal' => DB::raw($statusBaru ? 'true' : 'false'),
            'updated_at'     => now(),
        ]);

        $pesan = $statusBaru
            ? "Akses lihat butir soal untuk ujian '{$ujian->nama_ujian}' berhasil DIBUKA bagi Admin Sekolah."
            : "Akses lihat butir soal untuk ujian '{$ujian->nama_ujian}' DIKUNCI kembali dari Admin Sekolah.";

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Helper private untuk validasi request form Ujian
     */
    private function validateRequest(Request $request)
    {
        return $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'kelas_id'      => 'required|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapels,id',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi'        => 'required|integer|min:1',
            'token'         => 'nullable|string|max:6',
            'status'        => 'required|in:draft,aktif,selesai',
            'aturan_ujian'  => 'nullable|string',
            'acak_soal'     => 'nullable',
            'acak_jawaban'  => 'nullable',
        ]);
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
        $sekolahs  = Sekolah::all();
        $kelases   = Kelas::all();

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

        $hasFileCol    = Schema::hasTable('ujians') && Schema::hasColumn('ujians', 'file_berita_acara');
        $hasSekolahCol = Schema::hasTable('ujians') && Schema::hasColumn('ujians', 'sekolah_id');

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

        $ujians = $query->orderByDesc($hasFileCol ? 'uploaded_ba_at' : 'created_at')
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
}
