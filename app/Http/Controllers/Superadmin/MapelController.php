<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Mapel; // Sesuaikan dengan Model Mapel Anda
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $query = Mapel::query();

        // Fitur Pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_mapel', 'ILIKE', "%{$search}%") // Menggunakan ILIKE untuk PostgreSQL di Supabase
                  ->orWhere('kode_mapel', 'ILIKE', "%{$search}%");
        }

        $mapels = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('superadmin.mapel.index', compact('mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_mapel' => 'required|string|unique:mapels,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'kelompok'   => 'nullable|string',
            'status'     => 'required|in:aktif,nonaktif',
        ]);

        Mapel::create([
            'kode_mapel' => strtoupper($request->kode_mapel),
            'nama_mapel' => $request->nama_mapel,
            'kelompok'   => $request->kelompok ?? 'Wajib',
            'status'     => $request->status,
        ]);

        return redirect()->route('superadmin.mapel.index')->with('success', 'Mata Pelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);

        $request->validate([
            'kode_mapel' => 'required|string|unique:mapels,kode_mapel,' . $id,
            'nama_mapel' => 'required|string|max:255',
            'kelompok'   => 'nullable|string',
            'status'     => 'required|in:aktif,nonaktif',
        ]);

        $mapel->update([
            'kode_mapel' => strtoupper($request->kode_mapel),
            'nama_mapel' => $request->nama_mapel,
            'kelompok'   => $request->kelompok,
            'status'     => $request->status,
        ]);

        return redirect()->route('superadmin.mapel.index')->with('success', 'Mata Pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->delete();

        return redirect()->route('superadmin.mapel.index')->with('success', 'Mata Pelajaran berhasil dihapus!');
    }
}