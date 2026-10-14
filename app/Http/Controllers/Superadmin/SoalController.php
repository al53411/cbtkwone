<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        $query = Soal::with(['mapel', 'kelas']);

        // Filter berdasarkan Mata Pelajaran
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Filter berdasarkan Kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Pencarian Keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('soal', 'like', "%{$search}%")
                  ->orWhere('kode_soal', 'like', "%{$search}%");
            });
        }

        $soals = $query->latest()->paginate(10)->withQueryString();

        // Data Statistik untuk Ringkasan Atas
        $totalPaket  = Soal::count();
        $totalPg     = Soal::where('tipe', 'pg')->count(); 
        $totalEsai   = Soal::where('tipe', 'esai')->count();
        $totalMapel  = Mapel::has('soals')->count();

        return view('superadmin.soal.index', compact(
            'soals', 
            'mapels', 
            'kelass', 
            'totalPaket', 
            'totalPg', 
            'totalEsai', 
            'totalMapel'
        ));
    }

    public function create()
    {
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        return view('superadmin.soal.create', compact('mapels', 'kelass'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapel_id'      => 'required',
            'kelas_id'      => 'required',
            'soal'          => 'required',
            'jumlah_opsi'   => 'required|integer',
            'kunci_jawaban' => 'required',
        ]);

        Soal::create([
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'kode_soal'     => 'SOAL-' . strtoupper(uniqid()), // Membuat kode soal otomatis jika kosong
            'soal'          => $request->soal,
            'jumlah_opsi'   => $request->jumlah_opsi,
            'option_a'      => $request->option_a,
            'option_b'      => $request->option_b,
            'option_c'      => $request->jumlah_opsi >= 3 ? $request->option_c : null,
            'option_d'      => $request->jumlah_opsi >= 4 ? $request->option_d : null,
            'option_e'      => $request->jumlah_opsi >= 5 ? $request->option_e : null,
            'kunci_jawaban' => $request->kunci_jawaban,
        ]);

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $soal = Soal::findOrFail($id);
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        return view('superadmin.soal.edit', compact('soal', 'mapels', 'kelass'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'mapel_id'      => 'required',
            'kelas_id'      => 'required',
            'soal'          => 'required',
            'jumlah_opsi'   => 'required|integer',
            'kunci_jawaban' => 'required',
        ]);

        $soal = Soal::findOrFail($id);
        $soal->update([
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'soal'          => $request->soal,
            'jumlah_opsi'   => $request->jumlah_opsi,
            'option_a'      => $request->option_a,
            'option_b'      => $request->option_b,
            'option_c'      => $request->jumlah_opsi >= 3 ? $request->option_c : null,
            'option_d'      => $request->jumlah_opsi >= 4 ? $request->option_d : null,
            'option_e'      => $request->jumlah_opsi >= 5 ? $request->option_e : null,
            'kunci_jawaban' => $request->kunci_jawaban,
        ]);

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $soal = Soal::findOrFail($id);
        $soal->delete();

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil dihapus!');
    }

    public function show(string $id)
    {
        $soal = Soal::with(['mapel', 'kelas'])->findOrFail($id);
        
        return view('superadmin.soal.show', compact('soal'));
    }
}