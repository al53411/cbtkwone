<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Kelas;
use App\Models\Mapel;
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

        // Mengatur token ujian
        $tokenUjian = $request->filled('token') 
            ? strtoupper(trim($request->token)) 
            : strtoupper(Str::random(6));

        // Format boolean murni untuk PostgreSQL menggunakan DB::raw
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
}