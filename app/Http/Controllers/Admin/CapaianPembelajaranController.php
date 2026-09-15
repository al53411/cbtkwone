<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapaianPembelajaran;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CapaianPembelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = CapaianPembelajaran::with('mapel');

        // Filter berdasarkan Sekolah (Milik sekolah login ATAU data master nasional NULL)
        if (Auth::check() && Auth::user()->sekolah_id) {
            $query->where(function ($q) {
                $q->where('sekolah_id', Auth::user()->sekolah_id)
                  ->orWhereNull('sekolah_id');
            });
        }

        // Filter berdasarkan Mapel
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Filter berdasarkan Fase (Fleksibel: mendukung "Fase A" maupun "A")
        if ($request->filled('fase')) {
            $faseInput = trim($request->fase);
            $faseKode  = trim(str_replace('Fase', '', $faseInput));

            $query->where(function ($q) use ($faseInput, $faseKode) {
                $q->whereRaw('TRIM(fase) = ?', [$faseInput])
                  ->orWhereRaw('TRIM(fase) = ?', [$faseKode]);
            });
        }

        $cps = $query->latest()->paginate(15)->withQueryString();
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelass = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.cp.index', compact('cps', 'mapels', 'kelass'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mapel_id'     => 'required|exists:mapels,id',
            'fase'         => 'required|string|max:20',
            'elemen'       => 'required|string|max:150',
            'deskripsi_cp' => 'required|string',
        ]);

        // Otomatis assign sekolah_id pengelola jika ada
        if (Auth::check() && Auth::user()->sekolah_id) {
            $validated['sekolah_id'] = Auth::user()->sekolah_id;
        }

        CapaianPembelajaran::create($validated);

        return redirect()->back()->with('success', 'Capaian Pembelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, CapaianPembelajaran $capaianPembelajaran)
    {
        $validated = $request->validate([
            'mapel_id'     => 'required|exists:mapels,id',
            'fase'         => 'required|string|max:20',
            'elemen'       => 'required|string|max:150',
            'deskripsi_cp' => 'required|string',
        ]);

        $capaianPembelajaran->update($validated);

        return redirect()->back()->with('success', 'Capaian Pembelajaran berhasil diperbarui!');
    }

    public function destroy(CapaianPembelajaran $capaianPembelajaran)
    {
        $capaianPembelajaran->delete();

        return redirect()->back()->with('success', 'Capaian Pembelajaran berhasil dihapus!');
    }

    /**
     * Endpoint API JSON untuk AJAX pencarian Elemen berdasarkan mapel_id dan fase
     * Dipanggil oleh form Tujuan Pembelajaran
     */
    public function getElemenByMapelFase(Request $request)
    {
        $query = CapaianPembelajaran::query();

        // 1. Filter Sekolah (Sertakan data khusus sekolah + data master nasional NULL)
        if (Auth::check() && Auth::user()->sekolah_id) {
            $query->where(function ($q) {
                $q->where('sekolah_id', Auth::user()->sekolah_id)
                  ->orWhereNull('sekolah_id');
            });
        }

        // 2. Filter Mapel
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // 3. Filter Fase Fleksibel (Mencakup "Fase A" dan "A")
        if ($request->filled('fase')) {
            $faseInput = trim($request->fase);
            $faseKode  = trim(str_replace('Fase', '', $faseInput));

            $query->where(function ($q) use ($faseInput, $faseKode) {
                $q->whereRaw('TRIM(fase) = ?', [$faseInput])
                  ->orWhereRaw('TRIM(fase) = ?', [$faseKode]);
            });
        }

        // 4. Ambil Elemen Unik & Urutkan secara Alphabetical
        $elemenList = $query->whereNotNull('elemen')
                            ->where('elemen', '!=', '')
                            ->distinct()
                            ->orderBy('elemen', 'asc')
                            ->pluck('elemen')
                            ->values(); // Reset kunci array agar format JSON berupa array murni []

        return response()->json($elemenList);
    }
}