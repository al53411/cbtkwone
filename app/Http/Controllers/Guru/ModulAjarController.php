<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ModulAjar;
use App\Models\Mapel;
use App\Models\CapaianPembelajaran;
use App\Models\TujuanPembelajaran;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModulAjarController extends Controller
{
    /**
     * Menampilkan daftar Modul Ajar dan data master untuk dropdown form.
     */
    public function index(Request $request)
    {
        $mapelTable = (new Mapel())->getTable();

        $request->validate([
            'mapel_id' => "nullable|exists:{$mapelTable},id",
            'fase'     => 'nullable|in:A,B,C,D,E,F',
        ]);

        $query = ModulAjar::with(['mapel', 'capaianPembelajaran', 'tujuanPembelajarans']);

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('fase')) {
            $query->where('fase', $request->fase);
        }

        $modulAjars = $query->latest()->paginate(10)->withQueryString();

        $mapels       = Mapel::all();
        $cps          = CapaianPembelajaran::all();
        $kelases      = class_exists(Kelas::class) ? Kelas::all() : collect();
        $tahunAjarans = class_exists(TahunAjaran::class) ? TahunAjaran::all() : collect();

        $semesters = collect([
            (object)['id' => '1', 'nama' => 'Semester 1 (Ganjil)'],
            (object)['id' => '2', 'nama' => 'Semester 2 (Genap)'],
        ]);

        $taAktifRecord = class_exists(TahunAjaran::class) ? TahunAjaran::where('is_aktif', 1)->first() : null;
        $tahunAjaranAktif = $taAktifRecord->tahun ?? '2026/2027';
        
        $semesterAktif = '1';
        if ($taAktifRecord) {
            $semesterAktif = (strtolower($taAktifRecord->semester) === 'genap' || $taAktifRecord->semester == '2') ? '2' : '1';
        }

        return view('guru.modul_ajar.index', compact(
            'modulAjars',
            'mapels',
            'cps',
            'kelases',
            'tahunAjarans',
            'tahunAjaranAktif',
            'semesterAktif',
            'semesters'
        ));
    }

    /**
     * Menyimpan Modul Ajar baru beserta relasi TP.
     */
    public function store(Request $request)
    {
        $mapelTable = (new Mapel())->getTable();
        $cpTable    = (new CapaianPembelajaran())->getTable();
        $tpTable    = (new TujuanPembelajaran())->getTable();

        $validatedData = $request->validate([
            'judul'                => 'required|string|max:255',
            'mapel_id'             => "required|exists:{$mapelTable},id",
            'cp_id'                => "nullable|exists:{$cpTable},id",
            'fase'                 => 'required|in:A,B,C,D,E,F',
            'kelas'                => 'required|string|max:50',
            'semester'             => 'required|in:1,2',
            'tahun_ajaran'         => 'required|string|max:20',
            'alokasi_waktu'        => 'required|string|max:100',
            'target_peserta_didik' => 'required|string|max:255',
            'model_pembelajaran'   => 'nullable|string|max:255',
            'status'               => 'required|in:draft,published',
            'kompetensi_awal'      => 'nullable|string',
            'pemahaman_bermakna'   => 'nullable|string',
            'pertanyaan_pemantik'  => 'nullable|string',
            'tp_ids'               => 'nullable|array',
            'tp_ids.*'             => "exists:{$tpTable},id",
        ]);

        $validatedData['user_id'] = Auth::id();

        DB::transaction(function () use ($validatedData, $request) {
            $modul = ModulAjar::create($validatedData);

            if ($request->filled('tp_ids')) {
                if (method_exists($modul->tujuanPembelajarans(), 'sync')) {
                    $modul->tujuanPembelajarans()->sync($request->tp_ids);
                } else {
                    foreach ($request->tp_ids as $tpId) {
                        $masterTp = TujuanPembelajaran::find($tpId);
                        if ($masterTp) {
                            TujuanPembelajaran::create([
                                'modul_ajar_id' => $modul->id,
                                'sekolah_id'    => $masterTp->sekolah_id,
                                'mapel_id'      => $masterTp->mapel_id,
                                'kelas_id'      => $masterTp->kelas_id,
                                'fase'          => $masterTp->fase,
                                'elemen'        => $masterTp->elemen,
                                'kode_tp'       => $masterTp->kode_tp,
                                'deskripsi_tp'  => $masterTp->deskripsi_tp,
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->route('guru.modul_ajar.index')
            ->with('success', 'Modul Ajar berhasil ditambahkan.');
    }

    /**
     * Detail Modul Ajar.
     */
    public function show($id)
    {
        $modulAjar = ModulAjar::with([
            'mapel',
            'capaianPembelajaran',
            'tujuanPembelajarans',
            'langkahPembelajarans',
            'user'
        ])->findOrFail($id);

        return view('guru.modul_ajar.show', compact('modulAjar'));
    }

    /**
     * Memperbarui Modul Ajar dan sinkronisasi TP.
     */
    public function update(Request $request, $id)
    {
        $modul = ModulAjar::findOrFail($id);

        $mapelTable = (new Mapel())->getTable();
        $cpTable    = (new CapaianPembelajaran())->getTable();
        $tpTable    = (new TujuanPembelajaran())->getTable();

        $validatedData = $request->validate([
            'judul'                => 'required|string|max:255',
            'mapel_id'             => "required|exists:{$mapelTable},id",
            'cp_id'                => "nullable|exists:{$cpTable},id",
            'fase'                 => 'required|in:A,B,C,D,E,F',
            'kelas'                => 'required|string|max:50',
            'semester'             => 'required|in:1,2',
            'tahun_ajaran'         => 'required|string|max:20',
            'alokasi_waktu'        => 'required|string|max:100',
            'target_peserta_didik' => 'required|string|max:255',
            'model_pembelajaran'   => 'nullable|string|max:255',
            'status'               => 'required|in:draft,published',
            'kompetensi_awal'      => 'nullable|string',
            'pemahaman_bermakna'   => 'nullable|string',
            'pertanyaan_pemantik'  => 'nullable|string',
            'tp_ids'               => 'nullable|array',
            'tp_ids.*'             => "exists:{$tpTable},id",
        ]);

        DB::transaction(function () use ($modul, $validatedData, $request) {
            $modul->update($validatedData);

            if (method_exists($modul->tujuanPembelajarans(), 'sync')) {
                $modul->tujuanPembelajarans()->sync($request->tp_ids ?? []);
            } else {
                TujuanPembelajaran::where('modul_ajar_id', $modul->id)->delete();

                if ($request->filled('tp_ids')) {
                    foreach ($request->tp_ids as $tpId) {
                        $masterTp = TujuanPembelajaran::find($tpId);
                        if ($masterTp) {
                            TujuanPembelajaran::create([
                                'modul_ajar_id' => $modul->id,
                                'sekolah_id'    => $masterTp->sekolah_id,
                                'mapel_id'      => $masterTp->mapel_id,
                                'kelas_id'      => $masterTp->kelas_id,
                                'fase'          => $masterTp->fase,
                                'elemen'        => $masterTp->elemen,
                                'kode_tp'       => $masterTp->kode_tp,
                                'deskripsi_tp'  => $masterTp->deskripsi_tp,
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->route('guru.modul_ajar.index')
            ->with('success', 'Modul Ajar berhasil diperbarui.');
    }

    /**
     * Hapus Modul Ajar.
     */
    public function destroy($id)
    {
        $modul = ModulAjar::findOrFail($id);
        $modul->delete();

        return redirect()->route('guru.modul_ajar.index')
            ->with('success', 'Modul Ajar berhasil dihapus.');
    }

    /**
     * AJAX Endpoint: Mengambil CP berdasarkan Mapel & Fase
     */
    public function getCpFiltered(Request $request)
    {
        $cps = CapaianPembelajaran::query()
            ->when($request->filled('mapel_id'), fn($q) => $q->where('mapel_id', $request->mapel_id))
            ->when($request->filled('fase'), fn($q) => $q->where('fase', $request->fase))
            ->get(['id', 'elemen', 'deskripsi_cp']);

        return response()->json($cps);
    }

    /**
     * AJAX Endpoint: Mengambil TP berdasarkan Filter (Master Data Admin)
     */
    public function getTpFiltered(Request $request)
    {
        $tps = TujuanPembelajaran::query()
            ->whereNull('modul_ajar_id')
            ->when($request->filled('mapel_id'), fn($q) => $q->where('mapel_id', $request->mapel_id))
            ->when($request->filled('kelas_id'), fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('fase'), fn($q) => $q->where('fase', $request->fase))
            ->get(['id', 'kode_tp', 'deskripsi_tp']);

        return response()->json($tps);
    }

    /**
     * AJAX Endpoint: Mengambil TP berdasarkan ID Capaian Pembelajaran (CP)
     */
    public function getTpByCp($cp_id)
    {
        try {
            $cp = CapaianPembelajaran::find($cp_id);

            if (!$cp) {
                return response()->json([]);
            }

            // Mencocokkan TP berdasarkan mapel_id dan fase dari CP terkait
            $tps = TujuanPembelajaran::whereNull('modul_ajar_id')
                ->where('mapel_id', $cp->mapel_id)
                ->where(function ($q) use ($cp) {
                    $faseClean = str_replace('Fase ', '', $cp->fase);
                    $q->where('fase', $cp->fase)
                      ->orWhere('fase', $faseClean)
                      ->orWhere('fase', 'Fase ' . $faseClean);
                })
                ->get(['id', 'kode_tp', 'deskripsi_tp']);

            return response()->json($tps);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cetak Modul Ajar.
     */
    public function print($id)
    {
        $modul = ModulAjar::with([
            'sekolah', 
            'user', 
            'mapel', 
            'capaianPembelajaran', 
            'tujuanPembelajarans', 
            'langkahPembelajarans'
        ])->findOrFail($id);

        return view('guru.modul_ajar.print', compact('modul'));
    }
}