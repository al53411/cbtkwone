<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SoalImport;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        $kodeSoals = Soal::select('kode_soal')
            ->whereNotNull('kode_soal')
            ->distinct()
            ->pluck('kode_soal');

        $query = Soal::with(['mapel', 'kelas']);

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('soal', 'like', "%{$search}%")
                    ->orWhere('kode_soal', 'like', "%{$search}%");
            });
        }

        $soals = $query->latest()->paginate(10)->withQueryString();

        $totalPaket = Soal::count();

        if (Schema::hasColumn('soals', 'tipe')) {
            $totalPg   = Soal::where('tipe', 'pg')->count();
            $totalEsai = Soal::where('tipe', 'esai')->count();
        } elseif (Schema::hasColumn('soals', 'jenis_soal')) {
            $totalPg   = Soal::where('jenis_soal', 'pg')->count();
            $totalEsai = Soal::where('jenis_soal', 'esai')->count();
        } else {
            $totalPg   = Soal::count();
            $totalEsai = 0;
        }

        $totalMapel = Mapel::has('soals')->count();

        return view('superadmin.soal.index', compact(
            'soals',
            'mapels',
            'kelass',
            'kodeSoals',
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

        $jumlahOpsi = (int) $request->jumlah_opsi;

        Soal::create([
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'kode_soal'     => 'SOAL-' . strtoupper(uniqid()),
            'nomor_soal'    => $request->nomor_soal ?? null,
            'soal'          => $request->soal,
            'jumlah_opsi'   => $jumlahOpsi,
            'option_a'      => $request->option_a,
            'option_b'      => $request->option_b,
            'option_c'      => $jumlahOpsi >= 3 ? $request->option_c : null,
            'option_d'      => $jumlahOpsi >= 4 ? $request->option_d : null,
            'option_e'      => $jumlahOpsi >= 5 ? $request->option_e : null,
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
        $jumlahOpsi = (int) $request->jumlah_opsi;

        $soal->update([
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'soal'          => $request->soal,
            'nomor_soal'    => $request->nomor_soal ?? $soal->nomor_soal,
            'jumlah_opsi'   => $jumlahOpsi,
            'option_a'      => $request->option_a,
            'option_b'      => $request->option_b,
            'option_c'      => $jumlahOpsi >= 3 ? $request->option_c : null,
            'option_d'      => $jumlahOpsi >= 4 ? $request->option_d : null,
            'option_e'      => $jumlahOpsi >= 5 ? $request->option_e : null,
            'kunci_jawaban' => $request->kunci_jawaban,
        ]);

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $soal = Soal::findOrFail($id);

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil dihapus!');
    }

    public function show(string $id)
    {
        $soal = Soal::with(['mapel', 'kelas'])->findOrFail($id);

        return view('superadmin.soal.show', compact('soal'));
    }

    public function import()
    {
        return view('superadmin.soal.import');
    }

    public function showUploadGambar()
    {
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelass = Kelas::orderBy('nama_kelas', 'asc')->get();

        $kodeSoals = Soal::select('kode_soal')
            ->whereNotNull('kode_soal')
            ->groupBy('kode_soal')
            ->pluck('kode_soal');

        return view('superadmin.soal.upload-gambar', compact('mapels', 'kelass', 'kodeSoals'));
    }

    public function processUploadGambar(Request $request)
    {
        $request->validate([
            'kode_soal'  => 'required',
            'kelas_id'   => 'required',
            'nomor_soal' => 'required|numeric',
            'gambar'     => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'kode_soal.required'  => 'Kode soal wajib diisi!',
            'kelas_id.required'   => 'Kelas wajib dipilih!',
            'nomor_soal.required' => 'Nomor soal wajib diisi!',
            'gambar.required'     => 'File gambar wajib diunggah!',
            'gambar.image'        => 'File harus berupa gambar valid.',
            'gambar.max'          => 'Ukuran gambar maksimal 2MB.',
        ]);

        $query = Soal::where('kode_soal', $request->kode_soal)
            ->where('kelas_id', $request->kelas_id);

        if (Schema::hasColumn('soals', 'nomor_soal')) {
            $soal = $query->where('nomor_soal', $request->nomor_soal)->first();
        } else {
            $soal = $query->orderBy('id', 'asc')
                ->skip($request->nomor_soal - 1)
                ->take(1)
                ->first();
        }

        if (!$soal) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Soal tidak ditemukan untuk Kode: {$request->kode_soal} pada kelas tersebut dengan Nomor Soal: {$request->nomor_soal}");
        }

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $path = $request->file('gambar')->store('soal', 'public');

        $soal->update([
            'gambar' => $path
        ]);

        return redirect()->back()->with('success', "Gambar berhasil diunggah untuk Soal No. {$request->nomor_soal} ({$request->kode_soal})!");
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File template wajib diunggah!',
            'file.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv',
            'file.max'      => 'Ukuran file terlalu besar (maksimal 5MB).',
        ]);

        try {
            Excel::import(new SoalImport, $request->file('file'));

            return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil diimpor!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    private function cellColumnToIndex($cellRef)
    {
        preg_match('/([A-Z]+)/', strtoupper($cellRef), $matches);
        if (!isset($matches[1])) return 0;
        $str = $matches[1];
        $len = strlen($str);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($str[$i]) - 64);
        }
        return $num - 1;
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/template_impor_soal.xlsx');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File template_impor_soal.xlsx tidak ditemukan di folder public/templates/');
        }

        return response()->download($filePath, 'template_impor_soal.xlsx');
    }
}