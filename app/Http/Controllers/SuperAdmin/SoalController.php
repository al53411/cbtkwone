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
use Illuminate\Database\Schema\Blueprint;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SoalImport;
use App\Exports\TemplateSoalExport;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureSoalMultiTipeColumns();

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

        if ($request->filled('tipe')) {
            $tipeFilter = strtolower(trim($request->tipe));
            $query->where(function ($q) use ($tipeFilter) {
                if (Schema::hasColumn('soals', 'tipe_soal')) {
                    $q->where('tipe_soal', $tipeFilter);
                }
                if (Schema::hasColumn('soals', 'tipe')) {
                    $q->orWhere('tipe', $tipeFilter);
                }
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('soal', 'like', "%{$search}%")
                    ->orWhere('kode_soal', 'like', "%{$search}%");
            });
        }

        $soals = $query->latest()->paginate(10)->withQueryString();

        $totalPaket = Soal::whereNotNull('kode_soal')->distinct('kode_soal')->count('kode_soal');
        if ($totalPaket === 0) {
            $totalPaket = Soal::count();
        }

        $totalPg = Soal::where(function ($q) {
            $q->where('tipe_soal', 'pg')->orWhereNull('tipe_soal');
        })->count();
        $totalMenjodohkan = Soal::where('tipe_soal', 'menjodohkan')->count();
        $totalEsai        = Soal::whereIn('tipe_soal', ['esai', 'essay'])->count();
        $totalMapel       = Mapel::has('soals')->count();

        return view('superadmin.soal.index', compact(
            'soals',
            'mapels',
            'kelass',
            'kodeSoals',
            'paketGrouped',
            'totalPaket',
            'totalPg',
            'totalMenjodohkan',
            'totalEsai',
            'totalMapel'
        ));
    }

    public function create()
    {
        $this->ensureSoalMultiTipeColumns();

        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelass = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('superadmin.soal.create', compact('mapels', 'kelass'));
    }

    public function store(Request $request)
    {
        $this->ensureSoalMultiTipeColumns();

        $tipeSoal = strtolower($request->input('tipe_soal', 'pg'));

        // Validasi dinamis sesuai tipe soal
        $rules = [
            'tipe_soal'  => 'required|in:pg,menjodohkan,esai',
            'mapel_id'   => 'required',
            'kelas_id'   => 'required',
            'soal'       => 'required',
            'kode_soal'  => 'nullable|string|max:100',
            'bobot_soal' => 'nullable|numeric|min:0.1|max:100',
            'gambar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($tipeSoal === 'pg') {
            $rules['jumlah_opsi']   = 'required|integer|min:3|max:5';
            $rules['kunci_jawaban'] = 'required|in:A,B,C,D,E,a,b,c,d,e';
        } elseif ($tipeSoal === 'menjodohkan') {
            $rules['pasangan_kiri']   = 'required|array|min:2';
            $rules['pasangan_kanan']  = 'required|array|min:2';
        }

        $request->validate($rules);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soal', 'public');
        }

        $kodePaket = $request->filled('kode_soal')
            ? strtoupper(trim($request->kode_soal))
            : 'SOAL-' . strtoupper(uniqid());

        $dataSimpan = [
            'mapel_id'   => $request->mapel_id,
            'kelas_id'   => $request->kelas_id,
            'kode_soal'  => $kodePaket,
            'nomor_soal' => $request->nomor_soal ?? null,
            'soal'       => $request->soal,
            'tipe_soal'  => $tipeSoal,
            'gambar'     => $gambarPath,
            'bobot_soal' => (float) $request->input('bobot_soal', 1),
        ];

        if (Schema::hasColumn('soals', 'tipe')) {
            $dataSimpan['tipe'] = $tipeSoal;
        }

        if ($tipeSoal === 'menjodohkan') {
            $pasangan = $this->buildPasanganMenjodohkan($request);
            if (count($pasangan) < 2) {
                return redirect()->back()->withInput()->with('error', 'Soal menjodohkan wajib memiliki minimal 2 pasangan Kiri & Kanan yang terisi lengkap.');
            }
            $dataSimpan['pasangan_menjodohkan'] = json_encode($pasangan, JSON_UNESCAPED_UNICODE);
            $dataSimpan['jumlah_opsi']          = count($pasangan);
            $dataSimpan['kunci_jawaban']        = null;
        } elseif ($tipeSoal === 'esai') {
            $dataSimpan['jumlah_opsi']          = 0;
            $dataSimpan['pasangan_menjodohkan'] = null;
            $dataSimpan['kunci_jawaban']        = $request->filled('kunci_esai') ? trim($request->kunci_esai) : null;
        } else {
            // Pilihan Ganda (PG)
            $jumlahOpsi                         = (int) $request->jumlah_opsi;
            $dataSimpan['jumlah_opsi']          = $jumlahOpsi;
            $dataSimpan['option_a']             = $request->option_a;
            $dataSimpan['option_b']             = $request->option_b;
            $dataSimpan['option_c']             = $jumlahOpsi >= 3 ? $request->option_c : null;
            $dataSimpan['option_d']             = $jumlahOpsi >= 4 ? $request->option_d : null;
            $dataSimpan['option_e']             = $jumlahOpsi >= 5 ? $request->option_e : null;
            $dataSimpan['pasangan_menjodohkan'] = null;
            $dataSimpan['kunci_jawaban']        = strtoupper(trim($request->kunci_jawaban));
        }

        Soal::create($dataSimpan);

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal (' . strtoupper($tipeSoal) . ') berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $this->ensureSoalMultiTipeColumns();

        $soal   = Soal::findOrFail($id);
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelass = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('superadmin.soal.edit', compact('soal', 'mapels', 'kelass'));
    }

    public function update(Request $request, string $id)
    {
        $this->ensureSoalMultiTipeColumns();

        $soal     = Soal::findOrFail($id);
        $tipeSoal = strtolower($request->input('tipe_soal', $soal->tipe_soal ?? 'pg'));

        $rules = [
            'tipe_soal'  => 'required|in:pg,menjodohkan,esai',
            'mapel_id'   => 'required',
            'kelas_id'   => 'required',
            'soal'       => 'required',
            'kode_soal'  => 'nullable|string|max:100',
            'bobot_soal' => 'nullable|numeric|min:0.1|max:100',
            'gambar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($tipeSoal === 'pg') {
            $rules['jumlah_opsi']   = 'required|integer|min:3|max:5';
            $rules['kunci_jawaban'] = 'required|in:A,B,C,D,E,a,b,c,d,e';
        } elseif ($tipeSoal === 'menjodohkan') {
            $rules['pasangan_kiri']  = 'required|array|min:2';
            $rules['pasangan_kanan'] = 'required|array|min:2';
        }

        $request->validate($rules);

        $updateData = [
            'mapel_id'   => $request->mapel_id,
            'kelas_id'   => $request->kelas_id,
            'kode_soal'  => $request->filled('kode_soal') ? strtoupper(trim($request->kode_soal)) : $soal->kode_soal,
            'soal'       => $request->soal,
            'tipe_soal'  => $tipeSoal,
            'nomor_soal' => $request->nomor_soal ?? $soal->nomor_soal,
            'bobot_soal' => (float) $request->input('bobot_soal', $soal->bobot_soal ?? 1),
        ];

        if (Schema::hasColumn('soals', 'tipe')) {
            $updateData['tipe'] = $tipeSoal;
        }

        if ($tipeSoal === 'menjodohkan') {
            $pasangan = $this->buildPasanganMenjodohkan($request);
            if (count($pasangan) < 2) {
                return redirect()->back()->withInput()->with('error', 'Soal menjodohkan wajib memiliki minimal 2 pasangan Kiri & Kanan yang terisi lengkap.');
            }
            $updateData['pasangan_menjodohkan'] = json_encode($pasangan, JSON_UNESCAPED_UNICODE);
            $updateData['jumlah_opsi']          = count($pasangan);
            $updateData['option_a']             = null;
            $updateData['option_b']             = null;
            $updateData['option_c']             = null;
            $updateData['option_d']             = null;
            $updateData['option_e']             = null;
            $updateData['kunci_jawaban']        = null;
        } elseif ($tipeSoal === 'esai') {
            $updateData['pasangan_menjodohkan'] = null;
            $updateData['jumlah_opsi']          = 0;
            $updateData['option_a']             = null;
            $updateData['option_b']             = null;
            $updateData['option_c']             = null;
            $updateData['option_d']             = null;
            $updateData['option_e']             = null;
            $updateData['kunci_jawaban']        = $request->filled('kunci_esai') ? trim($request->kunci_esai) : null;
        } else {
            $jumlahOpsi                         = (int) $request->jumlah_opsi;
            $updateData['pasangan_menjodohkan'] = null;
            $updateData['jumlah_opsi']          = $jumlahOpsi;
            $updateData['option_a']             = $request->option_a;
            $updateData['option_b']             = $request->option_b;
            $updateData['option_c']             = $jumlahOpsi >= 3 ? $request->option_c : null;
            $updateData['option_d']             = $jumlahOpsi >= 4 ? $request->option_d : null;
            $updateData['option_e']             = $jumlahOpsi >= 5 ? $request->option_e : null;
            $updateData['kunci_jawaban']        = strtoupper(trim($request->kunci_jawaban));
        }

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

        $baseQuery = Soal::where('kode_soal', $kodeSoal);

        if ($kelasId) {
            $baseQuery->where(function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
            });
        }

        $soal = null;

        if (Schema::hasColumn('soals', 'nomor_soal')) {
            $soal = (clone $baseQuery)->where('nomor_soal', $nomor)->first();
        }

        if (!$soal) {
            $soalsCollection = (clone $baseQuery)->orderBy('id', 'asc')->get();

            if ($soalsCollection->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Tidak ada soal ditemukan dengan Kode: '{$kodeSoal}'.");
            }

            $targetIndex = $nomor - 1;

            if (!isset($soalsCollection[$targetIndex])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nomor soal '{$nomor}' tidak ditemukan! Paket '{$kodeSoal}' hanya memiliki " . $soalsCollection->count() . " butir soal.");
            }

            $soal = $soalsCollection[$targetIndex];
        }

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $path = $request->file('gambar')->store('soal', 'public');

        DB::table('soals')->where('id', $soal->id)->update([
            'gambar'     => $path,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Gambar sukses terpasang pada Soal (ID: {$soal->id}, No: {$nomor}, Kode: {$kodeSoal})!");
    }

    public function import()
    {
        $this->ensureSoalMultiTipeColumns();

        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelass = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('superadmin.soal.import', compact('mapels', 'kelass'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateSoalExport, 'template_impor_soal.xlsx');
    }

    public function processImport(Request $request)
    {
        $this->ensureSoalMultiTipeColumns();

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File Excel/CSV wajib dipilih!',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $kodePaket = $request->filled('kode_soal') ? strtoupper(trim($request->kode_soal)) : null;
            $importer  = new SoalImport($kodePaket);
            Excel::import($importer, $request->file('file'));

            if ($importer->importedCount === 0) {
                $alasan = !empty($importer->skippedRows)
                    ? implode(' | ', array_slice($importer->skippedRows, 0, 3))
                    : 'Tidak ada baris data yang valid pada sheet pertama.';

                return redirect()->back()
                    ->with('error', '0 soal berhasil diimpor. Penyebab: ' . $alasan);
            }

            $pesan = "{$importer->importedCount} butir soal berhasil diimpor!";
            if (!empty($importer->skippedRows)) {
                $pesan .= ' (' . count($importer->skippedRows) . ' baris dilewati: ' . $importer->skippedRows[0] . ')';
            }

            return redirect()->route('superadmin.soal.index')->with('success', $pesan);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengimpor file soal: ' . $e->getMessage());
        }
    }

    /**
     * Helper private menyusun pasangan Kiri <-> Kanan untuk soal Menjodohkan
     */
    private function buildPasanganMenjodohkan(Request $request): array
    {
        $kiriList  = $request->input('pasangan_kiri', []);
        $kananList = $request->input('pasangan_kanan', []);
        $pasangan  = [];

        foreach ($kiriList as $idx => $kiri) {
            $teksKiri  = trim((string) $kiri);
            $teksKanan = trim((string) ($kananList[$idx] ?? ''));

            if ($teksKiri !== '' && $teksKanan !== '') {
                $pasangan[] = [
                    'kiri'  => $teksKiri,
                    'kanan' => $teksKanan,
                ];
            }
        }

        return $pasangan;
    }

    /**
     * Helper private memperbarui struktur kolom tabel soals agar mendukung PG, Menjodohkan, & Esai
     */
    private function ensureSoalMultiTipeColumns(): void
    {
        if (Schema::hasTable('soals')) {
            Schema::table('soals', function (Blueprint $table) {
                if (!Schema::hasColumn('soals', 'kode_soal')) {
                    $table->string('kode_soal', 100)->nullable();
                }
                if (!Schema::hasColumn('soals', 'tipe_soal')) {
                    $table->string('tipe_soal', 30)->default('pg');
                }
                if (!Schema::hasColumn('soals', 'pasangan_menjodohkan')) {
                    $table->longText('pasangan_menjodohkan')->nullable();
                }
                if (!Schema::hasColumn('soals', 'bobot_soal')) {
                    $table->decimal('bobot_soal', 5, 2)->default(1.00);
                }
                if (!Schema::hasColumn('soals', 'gambar')) {
                    $table->string('gambar')->nullable();
                }
            });

            // Ubah opsi PG & kunci jawaban menjadi nullable agar soal Esai dan Menjodohkan dapat disimpan
            Schema::table('soals', function (Blueprint $table) {
                foreach (['option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'kunci_jawaban'] as $col) {
                    if (Schema::hasColumn('soals', $col)) {
                        $table->text($col)->nullable()->change();
                    }
                }
                if (Schema::hasColumn('soals', 'jumlah_opsi')) {
                    $table->integer('jumlah_opsi')->nullable()->default(5)->change();
                }
            });
        }
    }
}
