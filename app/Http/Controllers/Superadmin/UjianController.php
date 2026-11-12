<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\NilaiUjian;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

        $mapels = Mapel::where('status', 'aktif')->get();
        $kelases = Kelas::all();

        return view('superadmin.ujian.index', compact('ujians', 'mapels', 'kelases'));
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

        $acakSoalVal = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        Ujian::create([
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
        ]);

        return redirect()->route('superadmin.ujian.index')->with('success', 'Jadwal Ujian berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);
        $validated = $this->validateRequest($request);

        $acakSoalVal = $request->has('acak_soal') ? 'true' : 'false';
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
        $ujian = Ujian::findOrFail($id);
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
        $ujian = $ujianId ? Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId) : null;

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
        $ujian = $ujianId ? Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId) : null;

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
}