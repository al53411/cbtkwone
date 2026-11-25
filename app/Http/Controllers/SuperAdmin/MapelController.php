<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $query = Mapel::query();

        // Fitur Pencarian (Menggunakan LIKE yang kompatibel dengan MySQL/MariaDB)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_mapel', 'LIKE', "%{$search}%")
                    ->orWhere('kode_mapel', 'LIKE', "%{$search}%");
            });
        }

        // Menyimpan parameter pencarian saat berpindah halaman pagination
        $mapels = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('superadmin.mapel.index', compact('mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:50|unique:mapels,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'kelompok'   => 'nullable|string|max:100',
            'status'     => 'required|in:aktif,nonaktif',
        ], [
            'kode_mapel.required' => 'Kode mata pelajaran wajib diisi!',
            'kode_mapel.unique'   => 'Kode mata pelajaran sudah digunakan!',
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi!',
            'status.required'     => 'Status wajib dipilih!',
        ]);

        Mapel::create([
            'kode_mapel' => strtoupper(trim($request->kode_mapel)),
            'nama_mapel' => trim($request->nama_mapel),
            'kelompok'   => $request->kelompok ? trim($request->kelompok) : 'Wajib',
            'status'     => $request->status,
        ]);

        return redirect()->route('superadmin.mapel.index')->with('success', 'Mata Pelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);

        $request->validate([
            'kode_mapel' => 'required|string|max:50|unique:mapels,kode_mapel,' . $id,
            'nama_mapel' => 'required|string|max:255',
            'kelompok'   => 'nullable|string|max:100',
            'status'     => 'required|in:aktif,nonaktif',
        ], [
            'kode_mapel.required' => 'Kode mata pelajaran wajib diisi!',
            'kode_mapel.unique'   => 'Kode mata pelajaran sudah digunakan!',
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi!',
            'status.required'     => 'Status wajib dipilih!',
        ]);

        $mapel->update([
            'kode_mapel' => strtoupper(trim($request->kode_mapel)),
            'nama_mapel' => trim($request->nama_mapel),
            'kelompok'   => $request->kelompok ? trim($request->kelompok) : $mapel->kelompok,
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
