<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('siswas')->orderBy('nama_kelas', 'asc')->get();
        $siswas = Siswa::with('kelas')->latest()->get();

        return view('admin.kelas.index', compact('kelas', 'siswas'));
    }

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

        return redirect()->back()->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $kelas = Kelas::with('siswas')->findOrFail($id);
        return view('admin.kelas.show', compact('kelas'));
    }

    public function edit(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        return view('admin.kelas.edit', compact('kelas'));
    }

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

        return redirect()->route('admin.kelas.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $kelas = Kelas::findOrFail($id);

        if ($kelas->siswas()->count() > 0) {
            return redirect()->back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki data siswa!');
        }

        $kelas->delete();

        return redirect()->back()->with('success', 'Data kelas berhasil dihapus.');
    }
}