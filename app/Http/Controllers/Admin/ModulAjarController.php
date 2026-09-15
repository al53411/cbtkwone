<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModulAjar;
use App\Models\Mapel;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModulAjarController extends Controller
{
    /**
     * Tampilkan daftar seluruh modul ajar.
     */
    public function index(Request $request)
    {
        // 1. Ambil data Mata Pelajaran dan CP
        $mapels = Mapel::all();
        $cps = CapaianPembelajaran::all();

        // 2. Query data Modul Ajar beserta filter
        $query = ModulAjar::with(['mapel', 'user']);

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('fase')) {
            $query->where('fase', $request->fase);
        }

        $modulAjars = $query->latest()->paginate(10);

        // 3. Kirim $mapels dan $cps ke view
        return view('admin.modul_ajar.index', compact('modulAjars', 'mapels', 'cps'));
    }

    /**
     * Tampilkan form pembuatan modul ajar baru.
     */
    public function create()
    {
        $mapels = Mapel::all();
        $cps = CapaianPembelajaran::all();

        return view('admin.modul_ajar.create', compact('mapels', 'cps'));
    }

    /**
     * Simpan modul ajar baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mata_pelajarans,id',
            'cp_id' => 'nullable|exists:capaian_pembelajarans,id',
            'fase' => 'required|in:A,B,C,D,E,F',
            'kelas' => 'required|string|max:50',
            'alokasi_waktu' => 'required|string|max:100',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:1,2',
            'kompetensi_awal' => 'nullable|string',
            'profil_pelajar_pancasila' => 'nullable|array',
            'sarana_prasarana' => 'nullable|string',
            'target_peserta_didik' => 'required|string|max:255',
            'model_pembelajaran' => 'nullable|string|max:255',
            'pemahaman_bermakna' => 'nullable|string',
            'pertanyaan_pemantik' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;

        ModulAjar::create($validated);

        return redirect()->route('admin.modul_ajar.index')->with('success', 'Modul Ajar berhasil dibuat!');
    }

    /**
     * Tampilkan detail modul ajar.
     */
    public function show(ModulAjar $modulAjar)
    {
        $modulAjar->load(['mapel', 'capaianPembelajaran', 'tujuanPembelajarans', 'langkahPembelajarans', 'asesmens', 'lampirans']);

        return view('admin.modul_ajar.show', compact('modulAjar'));
    }

    /**
     * Tampilkan form edit modul ajar.
     */
    public function edit(ModulAjar $modulAjar)
    {
        $mapels = Mapel::all();
        $cps = CapaianPembelajaran::all();

        return view('admin.modul_ajar.edit', compact('modulAjar', 'mapels', 'cps'));
    }

    /**
     * Update data modul ajar.
     */
    public function update(Request $request, ModulAjar $modulAjar)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapels,id',
            'cp_id' => 'nullable|exists:capaian_pembelajarans,id',
            'fase' => 'required|in:A,B,C,D,E,F',
            'kelas' => 'required|string|max:50',
            'alokasi_waktu' => 'required|string|max:100',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:1,2',
            'kompetensi_awal' => 'nullable|string',
            'profil_pelajar_pancasila' => 'nullable|array',
            'sarana_prasarana' => 'nullable|string',
            'target_peserta_didik' => 'required|string|max:255',
            'model_pembelajaran' => 'nullable|string|max:255',
            'pemahaman_bermakna' => 'nullable|string',
            'pertanyaan_pemantik' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $modulAjar->update($validated);

        return redirect()->route('admin.modul_ajar.index')->with('success', 'Modul Ajar berhasil diperbarui!');
    }

    /**
     * Hapus modul ajar.
     */
    public function destroy(ModulAjar $modulAjar)
    {
        $modulAjar->delete();

        return redirect()->route('admin.modul_ajar.index')->with('success', 'Modul Ajar berhasil dihapus!');
    }
}