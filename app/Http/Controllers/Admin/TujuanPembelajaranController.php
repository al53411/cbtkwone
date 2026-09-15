<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TujuanPembelajaran;
use App\Models\CapaianPembelajaran;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TujuanPembelajaranController extends Controller
{
    /**
     * Tampilkan daftar Tujuan Pembelajaran
     */
    public function index()
    {
        $sekolahId = Auth::user()->sekolah_id ?? null;

        $tps = TujuanPembelajaran::with(['mapel', 'kelas'])
            ->when($sekolahId, function ($q) use ($sekolahId) {
                $q->where('sekolah_id', $sekolahId);
            })
            ->latest()
            ->get();

        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelases = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.tujuan_pembelajaran.index', compact('tps', 'mapels', 'kelases'));
    }

    /**
     * Simpan TP baru ke database (Multiple Poin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'mapel_id'       => 'required|exists:mapels,id',
            'kelas_id'       => 'nullable|exists:kelas,id',
            'fase'           => 'nullable|string|max:20',
            'elemen'         => 'nullable|string|max:255',
            'kode_tp'        => 'nullable|string|max:50',
            'deskripsi_tp'   => 'required|array|min:1',
            'deskripsi_tp.*' => 'required|string',
        ], [
            'mapel_id.required'       => 'Mata pelajaran wajib dipilih.',
            'deskripsi_tp.required'   => 'Deskripsi Tujuan Pembelajaran wajib diisi.',
            'deskripsi_tp.*.required' => 'Setiap poin deskripsi TP tidak boleh kosong.',
        ]);

        $sekolahId = Auth::user()->sekolah_id ?? null;

        // Loop untuk menyimpan setiap poin sebagai record terpisah
        foreach ($request->deskripsi_tp as $deskripsi) {
            TujuanPembelajaran::create([
                'sekolah_id'   => $sekolahId,
                'mapel_id'     => $request->mapel_id,
                'kelas_id'     => $request->kelas_id,
                'fase'         => $request->fase,
                'elemen'       => $request->elemen,
                'kode_tp'      => $request->kode_tp,
                'deskripsi_tp' => $deskripsi,
            ]);
        }

        return redirect()->back()->with('success', 'Tujuan Pembelajaran berhasil ditambahkan!');
    }

    /**
     * Update TP yang sudah ada
     */
    public function update(Request $request, $id)
    {
        $tp = TujuanPembelajaran::findOrFail($id);

        $request->validate([
            'mapel_id'       => 'required|exists:mapels,id',
            'kelas_id'       => 'nullable|exists:kelas,id',
            'fase'           => 'nullable|string|max:20',
            'elemen'         => 'nullable|string|max:255',
            'kode_tp'        => 'nullable|string|max:50',
            'deskripsi_tp'   => 'required|array|min:1',
            'deskripsi_tp.*' => 'required|string',
        ]);

        // Ambil poin pertama untuk update record saat ini
        $deskripsiArray = array_values($request->deskripsi_tp);

        $tp->update([
            'mapel_id'     => $request->mapel_id,
            'kelas_id'     => $request->kelas_id,
            'fase'         => $request->fase,
            'elemen'       => $request->elemen,
            'kode_tp'      => $request->kode_tp,
            'deskripsi_tp' => $deskripsiArray[0],
        ]);

        // Jika user menambah poin baru di modal edit, simpan sisanya sebagai record baru
        if (count($deskripsiArray) > 1) {
            for ($i = 1; $i < count($deskripsiArray); $i++) {
                TujuanPembelajaran::create([
                    'sekolah_id'   => Auth::user()->sekolah_id ?? null,
                    'mapel_id'     => $request->mapel_id,
                    'kelas_id'     => $request->kelas_id,
                    'fase'         => $request->fase,
                    'elemen'       => $request->elemen,
                    'kode_tp'      => $request->kode_tp,
                    'deskripsi_tp' => $deskripsiArray[$i],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Tujuan Pembelajaran berhasil diperbarui!');
    }

    /**
     * API JSON untuk mengambil daftar Elemen unik dari CapaianPembelajaran berdasarkan mapel_id dan fase
     */
    public function getElemenByMapelFase(Request $request)
    {
        $query = CapaianPembelajaran::query();

        if (Auth::check() && Auth::user()->sekolah_id) {
            $query->where(function ($q) {
                $q->where('sekolah_id', Auth::user()->sekolah_id)
                  ->orWhereNull('sekolah_id');
            });
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('fase')) {
            $faseInput = trim($request->fase);
            $faseKode  = trim(str_replace('Fase', '', $faseInput));

            $query->where(function ($q) use ($faseInput, $faseKode) {
                $q->whereRaw('TRIM(fase) = ?', [$faseInput])
                  ->orWhereRaw('TRIM(fase) = ?', [$faseKode]);
            });
        }

        $elemenList = $query->whereNotNull('elemen')
                            ->where('elemen', '!=', '')
                            ->distinct()
                            ->orderBy('elemen', 'asc')
                            ->pluck('elemen')
                            ->values();

        return response()->json($elemenList);
    }

    /**
     * Hapus TP
     */
    public function destroy($id)
    {
        $tp = TujuanPembelajaran::findOrFail($id);
        $tp->delete();

        return redirect()->back()->with('success', 'Tujuan Pembelajaran berhasil dihapus!');
    }

    /**
     * API JSON untuk mengambil daftar TP berdasarkan mapel/mapel_id & kelas_id
     */
    public function getByMapel(Request $request)
    {
        $query = TujuanPembelajaran::query();

        if (Auth::check() && Auth::user()->sekolah_id) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        } elseif ($request->filled('mapel')) {
            $query->whereHas('mapel', function ($q) use ($request) {
                $q->where('nama_mapel', $request->mapel);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id)
                  ->orWhereNull('kelas_id');
            });
        }

        $tps = $query->orderBy('kode_tp', 'asc')->get();

        return response()->json($tps);
    }

    /**
     * API JSON untuk mengambil daftar TP & Elemen berdasarkan mapel_id dan fase
     */
    public function getElemenAndTp(Request $request)
    {
        $query = TujuanPembelajaran::query();

        if (Auth::check() && Auth::user()->sekolah_id) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('fase')) {
            $faseInput = trim($request->fase);
            $faseKode  = trim(str_replace('Fase', '', $faseInput));

            $query->where(function ($q) use ($faseInput, $faseKode) {
                $q->whereRaw('TRIM(fase) = ?', [$faseInput])
                  ->orWhereRaw('TRIM(fase) = ?', [$faseKode]);
            });
        }

        $tps = $query->orderBy('kode_tp', 'asc')->get();

        return response()->json($tps);
    }
}