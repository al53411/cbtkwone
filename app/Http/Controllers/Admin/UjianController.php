<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'mapel_id'      => 'required|exists:mapels,id', // Memastikan mapel benar-benar ada di database
            'token'         => 'nullable|string|max:10',
            'durasi'        => 'required|integer|min:1',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable|boolean',
            'acak_jawaban'  => 'nullable|boolean',
        ]);

        // Jika token kosong, buat token otomatis yang unik
        if ($request->filled('token')) {
            $validated['token'] = strtoupper(trim($request->token));
        } else {
            // Opsional: Membuat token acak 6 karakter untuk ujian/mapel ini
            $validated['token'] = strtoupper(Str::random(6));
        }

        $validated['acak_soal'] = $request->has('acak_soal') ? 1 : 0;
        $validated['acak_jawaban'] = $request->has('acak_jawaban') ? 1 : 0;

        Ujian::create($validated);

        return redirect()->route('admin.sesi.index')->with('success', 'Sesi ujian dan token mapel berhasil dibuat!');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapels,id', // Memastikan mapel valid
            'token'         => 'nullable|string|max:10',
            'durasi'        => 'required|integer|min:1',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable|boolean',
            'acak_jawaban'  => 'nullable|boolean',
        ]);

        $ujian = Ujian::findOrFail($id);
        
        if ($request->filled('token')) {
            $validated['token'] = strtoupper(trim($request->token));
        }

        $validated['acak_soal'] = $request->has('acak_soal') ? 1 : 0;
        $validated['acak_jawaban'] = $request->has('acak_jawaban') ? 1 : 0;
        
        $ujian->update($validated);

        return redirect()->route('admin.sesi.index')->with('success', 'Data sesi ujian dan mapel berhasil diperbarui!');
    }
}