<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Menampilkan daftar kelas
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $kelass = Kelas::when($search, function ($query, $search) {
            return $query->where('nama_kelas', 'like', "%{$search}%")
                         ->orWhere('tingkat', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

        return view('superadmin.kelas.index', compact('kelass', 'search'));
    }

    /**
     * Menampilkan form tambah kelas
     */
    public function create()
    {
        return view('superadmin.kelas.create');
    }

    /**
     * Menyimpan data kelas baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tingkat'    => 'nullable|string|max:50',
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
        ]);

        Kelas::create([
            'nama_kelas' => $validated['nama_kelas'],
            'tingkat'    => $validated['tingkat'] ?? null,
        ]);

        return redirect()->route('superadmin.kelas.index')
                         ->with('success', 'Data kelas berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit kelas
     */
    public function edit(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        
        return view('superadmin.kelas.edit', compact('kelas'));
    }

    /**
     * Memperbarui data kelas
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tingkat'    => 'nullable|string|max:50',
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update([
            'nama_kelas' => $validated['nama_kelas'],
            'tingkat'    => $validated['tingkat'] ?? null,
        ]);

        return redirect()->route('superadmin.kelas.index')
                         ->with('success', 'Data kelas berhasil diperbarui!');
    }

    /**
     * Menghapus data kelas
     */
    public function destroy(string $id)
    {
        $kelas = Kelas::findOrFail($id);

        if (method_exists($kelas, 'siswas') && $kelas->siswas()->count() > 0) {
            return redirect()->back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki data siswa!');
        }

        $kelas->delete();

        return redirect()->route('superadmin.kelas.index')
                         ->with('success', 'Data kelas berhasil dihapus!');
    }
}