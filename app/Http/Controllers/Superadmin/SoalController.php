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
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelass = Kelas::orderBy('nama_kelas', 'asc')->get();

        $kodeSoals = Soal::select('kode_soal')
            ->whereNotNull('kode_soal')
            ->distinct()
            ->pluck('kode_soal');

        // Kelompokkan data soal per kode_soal untuk pengisian otomatis di JavaScript
        $paketGrouped = Soal::select('id', 'kode_soal', 'kelas_id', 'soal')
            ->get()
            ->groupBy('kode_soal')
            ->map(function ($items) {
                return [
                    'kelas_id' => $items->first()->kelas_id ?? '',
                    'items'    => $items->values()->map(function ($item, $idx) {
                        return [
                            'no'   => $idx + 1,
                            'id'   => $item->id,
                            'text' => \Illuminate\Support\Str::limit(strip_tags($item->soal ?? ''), 45)
                        ];
                    })
                ];
            });

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
            'paketGrouped',
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
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $jumlahOpsi = (int) $request->jumlah_opsi;
        $gambarPath = null;

        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soal', 'public');
        }

        Soal::create([
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'kode_soal'     => 'SOAL-' . strtoupper(uniqid()),
            'nomor_soal'    => $request->nomor_soal ?? null,
            'soal'          => $request->soal,
            'gambar'        => $gambarPath,
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
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $soal = Soal::findOrFail($id);
        $jumlahOpsi = (int) $request->jumlah_opsi;

        $updateData = [
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
        ];

        if ($request->hasFile('gambar')) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $updateData['gambar'] = $request->file('gambar')->store('soal', 'public');
        }

        $soal->update($updateData);

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

        $kodeSoal = trim($request->kode_soal);
        $kelasId  = $request->kelas_id;
        $nomor    = (int) $request->nomor_soal;

        // 1. Query dasar berdasarkan kode_soal & kelas_id
        $baseQuery = Soal::where('kode_soal', $kodeSoal);

        if ($kelasId) {
            $baseQuery->where(function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
            });
        }

        $soal = null;

        // 2. Cek apakah kolom 'nomor_soal' ada di database sebelum me-query
        if (Schema::hasColumn('soals', 'nomor_soal')) {
            $soal = (clone $baseQuery)->where('nomor_soal', $nomor)->first();
        }

        // 3. Jika kolom tidak ada atau data tidak ditemukan via nomor_soal, cari berdasarkan urutan baris ke-N
        if (!$soal) {
            $soalsCollection = (clone $baseQuery)->orderBy('id', 'asc')->get();

            if ($soalsCollection->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Tidak ada soal ditemukan dengan Kode: '{$kodeSoal}'.");
            }

            $targetIndex = $nomor - 1; // Nomor 1 = indeks array ke-0

            if (!isset($soalsCollection[$targetIndex])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nomor soal '{$nomor}' tidak ditemukan! Paket '{$kodeSoal}' hanya memiliki " . $soalsCollection->count() . " butir soal.");
            }

            $soal = $soalsCollection[$targetIndex];
        }

        // 4. Hapus gambar lama jika ada
        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        // 5. Simpan file gambar baru
        $path = $request->file('gambar')->store('soal', 'public');

        // 6. Update database via DB Query Builder agar langsung tersimpan
        DB::table('soals')->where('id', $soal->id)->update([
            'gambar'     => $path,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Gambar sukses terpasang pada Soal (ID: {$soal->id}, No: {$nomor}, Kode: {$kodeSoal})!");
    }
}
