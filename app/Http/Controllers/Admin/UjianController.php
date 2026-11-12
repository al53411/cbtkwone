<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel = class_exists(Mapel::class) ? Mapel::all() : collect();

        $query = Ujian::with(['kelas', 'mapel']);

        if ($request->filled('search')) {
            $query->where('nama_ujian', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $ujians = $query->latest()->paginate(10)->withQueryString();

        return view('admin.ujian.index', compact('ujians', 'listKelas', 'listMapel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapels,id',
            'token'         => 'nullable|string|max:10',
            'durasi'        => 'required|integer|min:1',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable',
            'acak_jawaban'  => 'nullable',
        ]);

        $tokenUjian = $request->filled('token')
            ? strtoupper(trim($request->token))
            : strtoupper(Str::random(6));

        $acakSoalVal = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        Ujian::create([
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'token'         => $tokenUjian,
            'durasi'        => $validated['durasi'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'status'        => $validated['status'],
            'acak_soal'     => DB::raw($acakSoalVal),
            'acak_jawaban'  => DB::raw($acakJawabanVal),
        ]);

        return redirect()->route('admin.ujian.index')->with('success', 'Sesi ujian dan token mapel berhasil dibuat!');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapels,id',
            'token'         => 'nullable|string|max:10',
            'durasi'        => 'required|integer|min:1',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable',
            'acak_jawaban'  => 'nullable',
        ]);

        $ujian = Ujian::findOrFail($id);

        $tokenUjian = $request->filled('token')
            ? strtoupper(trim($request->token))
            : $ujian->token;

        $acakSoalVal = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        $ujian->update([
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'token'         => $tokenUjian,
            'durasi'        => $validated['durasi'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'status'        => $validated['status'],
            'acak_soal'     => DB::raw($acakSoalVal),
            'acak_jawaban'  => DB::raw($acakJawabanVal),
        ]);

        return redirect()->route('admin.ujian.index')->with('success', 'Data sesi ujian dan mapel berhasil diperbarui!');
    }

    // 1. Halaman Rekap Semua Hasil Ujian (Menu Sidebar)
    public function rekapNilai(Request $request)
    {
        $listUjian = Ujian::with('mapel')->latest()->get();
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $query = NilaiUjian::with(['siswa.kelas', 'ujian.mapel']);

        if ($request->filled('ujian_id')) {
            $query->where('ujian_id', $request->ujian_id);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $hasilUjians = $query->latest()->paginate(15)->withQueryString();

        return view('admin.ujian.rekap', compact('hasilUjians', 'listUjian', 'listKelas'));
    }

    // 2. Halaman Detail Hasil Per Sesi Ujian
    public function hasil(Request $request, string $id)
    {
        $ujian = Ujian::with(['kelas', 'mapel'])->findOrFail($id);

        $query = NilaiUjian::with(['siswa.kelas'])
            ->where('ujian_id', $id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $hasilUjians = $query->orderBy('nilai', 'desc')->get();

        $statistik = [
            'total_peserta' => $hasilUjians->count(),
            'rata_rata'     => $hasilUjians->avg('nilai') ? round($hasilUjians->avg('nilai'), 2) : 0,
            'tertinggi'     => $hasilUjians->max('nilai') ?? 0,
            'terendah'      => $hasilUjians->min('nilai') ?? 0,
        ];

        return view('admin.ujian.hasil', compact('ujian', 'hasilUjians', 'statistik'));
    }

    // 3. Halaman Cetak Rekap Nilai Per Sesi Ujian
    public function cetakHasil(string $id)
    {
        $ujian = Ujian::with(['kelas', 'mapel'])->findOrFail($id);

        $hasilUjians = NilaiUjian::with(['siswa.kelas'])
            ->where('ujian_id', $id)
            ->orderBy('nilai', 'desc')
            ->get();

        return view('admin.ujian.cetak-hasil', compact('ujian', 'hasilUjians'));
    }
}