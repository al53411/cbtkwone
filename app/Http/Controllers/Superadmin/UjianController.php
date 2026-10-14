<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $query = Ujian::with(['mapel', 'kelas']);

        // 1. Pencarian Nama Ujian atau Nama Mapel
        if ($request->filled('search')) {
            $search = $request->search;
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

        // Mengambil data dengan pagination dan mempertahankan parameter query di URL
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

        Ujian::create([
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'durasi'        => $validated['durasi'],
            'token'         => strtoupper($validated['token']),
            'status'        => $validated['status'],
            'acak_soal'     => $request->boolean('acak_soal'),
            'acak_jawaban'  => $request->boolean('acak_jawaban'),
        ]);

        return redirect()->route('superadmin.ujian.index')->with('success', 'Jadwal Ujian berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);
        $validated = $this->validateRequest($request);

        $ujian->update([
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'durasi'        => $validated['durasi'],
            'token'         => strtoupper($validated['token']),
            'status'        => $validated['status'],
            'acak_soal'     => $request->boolean('acak_soal'),
            'acak_jawaban'  => $request->boolean('acak_jawaban'),
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
            'token'         => 'required|string|size:6',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable|boolean',
            'acak_jawaban'  => 'nullable|boolean',
        ]);
    }
}