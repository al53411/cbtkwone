<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        $query = Soal::with(['mapel', 'kelas']);

        // Filter berdasarkan Mata Pelajaran
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Filter berdasarkan Kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Pencarian Keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('soal', 'like', "%{$search}%")
                  ->orWhere('kode_soal', 'like', "%{$search}%");
            });
        }

        $soals = $query->latest()->paginate(10)->withQueryString();

        // Data Statistik untuk Ringkasan Atas
        $totalPaket  = Soal::count();
        $totalPg     = Soal::where('jenis_soal', 'pg')->count(); 
        $totalEsai   = Soal::where('jenis_soal', 'esai')->count();
        $totalMapel  = Mapel::has('soals')->count();

        return view('superadmin.soal.index', compact(
            'soals', 
            'mapels', 
            'kelass', 
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
        $soal->delete();

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil dihapus!');
    }

    public function show(string $id)
    {
        $soal = Soal::with(['mapel', 'kelas'])->findOrFail($id);
        
        return view('superadmin.soal.show', compact('soal'));
    }

    /**
     * Menampilkan form import soal
     */
    public function import()
    {
        return view('superadmin.soal.import');
    }

    /**
     * Memproses file import Excel/CSV secara manual tanpa library tambahan
     */
    public function processImport(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:5120',
        ], [
            'file.required' => 'File template wajib diunggah!',
            'file.mimes'    => 'Format file harus berupa .xlsx atau .csv',
            'file.max'      => 'Ukuran file terlalu besar (maksimal 5MB).',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $rows = $this->parseCsvFile($file->getPathname());
            } elseif ($extension === 'xlsx') {
                $rows = $this->parseXlsxFile($file->getPathname());
            } else {
                return redirect()->back()->with('error', 'Format file tidak didukung. Harap gunakan format .xlsx atau .csv');
            }

            if (empty($rows) || count($rows) < 2) {
                return redirect()->back()->with('error', 'File kosong atau tidak memiliki data baris soal!');
            }

            $rawHeader = array_shift($rows);
            $header = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$h)));
            }, $rawHeader);

            $imported = 0;
            $skipped = 0;

            DB::transaction(function () use ($rows, $header, &$imported, &$skipped) {
                foreach ($rows as $row) {
                    if (empty(array_filter($row))) continue;
                    
                    $dataRow = [];
                    foreach ($header as $index => $colName) {
                        $dataRow[$colName] = isset($row[$index]) ? trim((string)$row[$index]) : '';
                    }

                    $mapelId      = $dataRow['mapel_id'] ?? null;
                    $kelasId      = $dataRow['kelas_id'] ?? null;
                    $pertanyaan   = $dataRow['soal'] ?? $dataRow['pertanyaan'] ?? '';
                    $jumlahOpsi   = isset($dataRow['jumlah_opsi']) && is_numeric($dataRow['jumlah_opsi']) ? (int)$dataRow['jumlah_opsi'] : 5;
                    $kunciJawaban = strtoupper($dataRow['kunci_jawaban'] ?? $dataRow['kunci'] ?? 'A');
                    
                    $optA = $dataRow['option_a'] ?? $dataRow['jawaban_a'] ?? null;
                    $optB = $dataRow['option_b'] ?? $dataRow['jawaban_b'] ?? null;
                    $optC = $jumlahOpsi >= 3 ? ($dataRow['option_c'] ?? $dataRow['jawaban_c'] ?? null) : null;
                    $optD = $jumlahOpsi >= 4 ? ($dataRow['option_d'] ?? $dataRow['jawaban_d'] ?? null) : null;
                    $optE = $jumlahOpsi >= 5 ? ($dataRow['option_e'] ?? $dataRow['jawaban_e'] ?? null) : null;

                    if (!empty($pertanyaan) && !empty($optA) && !empty($optB) && !empty($mapelId) && !empty($kelasId)) {
                        Soal::create([
                            'mapel_id'      => $mapelId,
                            'kelas_id'      => $kelasId,
                            'kode_soal'     => 'SOAL-' . strtoupper(uniqid()),
                            'soal'          => $pertanyaan,
                            'jumlah_opsi'   => $jumlahOpsi,
                            'option_a'      => $optA,
                            'option_b'      => $optB,
                            'option_c'      => $optC,
                            'option_d'      => $optD,
                            'option_e'      => $optE,
                            'kunci_jawaban' => $kunciJawaban,
                        ]);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
            });

            if ($imported === 0) {
                return redirect()->back()->with('error', 'Gagal mengimpor soal! Pastikan mapel_id, kelas_id, soal, dan opsi jawaban terisi dengan benar.');
            }

            $msg = "Berhasil mengimpor {$imported} butir soal.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} baris dilewati karena data tidak lengkap).";
            }

            return redirect()->route('superadmin.soal.index')->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk membaca file CSV
     */
    private function parseCsvFile($path)
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Helper untuk membaca file XLSX menggunakan ZipArchive bawaan PHP
     */
    private function parseXlsxFile($path)
    {
        $rows = [];
        $zip = new \ZipArchive();
        if ($zip->open($path) === TRUE) {
            $sharedStrings = [];
            if (($index = $zip->locateName('xl/sharedStrings.xml')) !== FALSE) {
                $xml = simplexml_load_string($zip->getFromIndex($index));
                foreach ($xml->si as $val) {
                    $sharedStrings[] = (string)$val->t;
                }
            }
            if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) !== FALSE) {
                $xml = simplexml_load_string($zip->getFromIndex($index));
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    foreach ($row->c as $cell) {
                        $val = "";
                        $t = (string)$cell['t'];
                        if (isset($cell->v)) {
                            $v = (string)$cell->v;
                            if ($t == 's') {
                                $val = $sharedStrings[$v] ?? '';
                            } else {
                                $val = $v;
                            }
                        }
                        $rowData[] = $val;
                    }
                    $rows[] = $rowData;
                }
            }
            $zip->close();
        }
        return $rows;
    }
}