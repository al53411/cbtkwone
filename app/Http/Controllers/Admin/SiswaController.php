<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private function getSekolahId()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        return $user->sekolah_id ?? $user->guru?->sekolah_id;
    }

    /**
     * Menampilkan daftar siswa (Mendukung AJAX Filter, Pencarian, Paginasi & Full Page)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $sekolahId = $this->getSekolahId();

        $kelasQuery = Kelas::query();
        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $kelasQuery->where('sekolah_id', $sekolahId);
        }
        $listKelas = $kelasQuery->orderBy('nama_kelas', 'asc')->get();

        $query = Siswa::with('kelas')
            ->select('siswas.*')
            ->leftJoin('kelas', 'siswas.kelas_id', '=', 'kelas.id');

        if ($user && ($user->isGuru() || $user->role === 'guru' || $user->guru)) {
            $guru = $user->guru;
            $allKelasIds = [];
            if ($guru) {
                $kelasWaliIds = Kelas::where('guru_id', $guru->id)->pluck('id')->toArray();
                $kelasPengampuIds = method_exists($guru, 'kelas')
                    ? $guru->kelas()->pluck('kelas.id')->toArray()
                    : [];
                $allKelasIds = array_unique(array_merge($kelasWaliIds, $kelasPengampuIds));
            }
            if (!empty($allKelasIds)) {
                $query->whereIn('siswas.kelas_id', $allKelasIds);
            } elseif ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
                $query->where(function ($q) use ($sekolahId) {
                    $q->where('siswas.sekolah_id', $sekolahId)
                        ->orWhereNull('siswas.sekolah_id');
                });
            }
        } else {
            if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
                $query->where(function ($q) use ($sekolahId) {
                    $q->where('siswas.sekolah_id', $sekolahId)
                        ->orWhereNull('siswas.sekolah_id');
                });
            }
        }

        if ($request->filled('kelas_id')) {
            $query->where('siswas.kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                if (Schema::hasColumn('siswas', 'nama_siswa')) {
                    $q->where('siswas.nama_siswa', 'like', "%{$search}%");
                } else {
                    $q->where('siswas.nama_lengkap', 'like', "%{$search}%");
                }
                $q->orWhere('siswas.nisn', 'like', "%{$search}%");
            });
        }

        $siswas = $query->orderBy('kelas.nama_kelas', 'asc')
            ->orderBy('siswas.nama_siswa', 'asc')
            ->paginate(10)
            ->withQueryString();

        if ($siswas->isEmpty() && !$request->filled('search') && !$request->filled('kelas_id') && $user && ($user->isGuru() || $user->role === 'guru' || $user->guru) && $sekolahId) {
            $siswas = Siswa::with('kelas')
                ->select('siswas.*')
                ->leftJoin('kelas', 'siswas.kelas_id', '=', 'kelas.id')
                ->where(function ($q) use ($sekolahId) {
                    $q->where('siswas.sekolah_id', $sekolahId)
                        ->orWhereNull('siswas.sekolah_id');
                })
                ->orderBy('kelas.nama_kelas', 'asc')
                ->orderBy('siswas.nama_siswa', 'asc')
                ->paginate(10)
                ->withQueryString();
        }

        return view('admin.siswa.index', compact('siswas', 'listKelas'));
    }

    public function create(Request $request)
    {
        $sekolahId = $this->getSekolahId();

        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $kelas = Kelas::where('sekolah_id', $sekolahId)->orderBy('nama_kelas', 'asc')->get();
        } else {
            $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        }

        return view('admin.siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $sekolahId = $this->getSekolahId();

        $validated = $request->validate([
            'nama_siswa'    => 'required|string|max:255',
            'nisn'          => 'required|string|max:20|unique:siswas,nisn',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
            'password'      => 'nullable|string|min:6',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $siswa = DB::transaction(function () use ($request, $validated, $sekolahId) {
            $emailUser = $validated['nisn'];
            $passwordUser = !empty($validated['password']) ? $validated['password'] : ($validated['nisn'] . '*');

            $userPayload = [
                'name'     => $validated['nama_siswa'],
                'password' => Hash::make($passwordUser),
            ];

            if (Schema::hasColumn('users', 'role')) {
                $userPayload['role'] = 'siswa';
            }
            if ($sekolahId && Schema::hasColumn('users', 'sekolah_id')) {
                $userPayload['sekolah_id'] = $sekolahId;
            }

            $user = User::updateOrCreate(
                ['email' => $emailUser],
                $userPayload
            );

            $data = [
                'nama_siswa'    => $validated['nama_siswa'],
                'nisn'          => $validated['nisn'],
                'kelas_id'      => $validated['kelas_id'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat'        => $validated['alamat'] ?? null,
            ];

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
            }

            if (Schema::hasColumn('siswas', 'user_id')) {
                $data['user_id'] = $user->id;
            }

            if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
                $data['sekolah_id'] = $sekolahId;
            }

            return Siswa::create($data);
        });

        $message = 'Data siswa & akun user berhasil ditambahkan.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $siswa->load('kelas'),
            ]);
        }

        return redirect()->route('admin.siswa.index')->with('success', $message);
    }

    public function show(string $id)
    {
        $sekolahId = $this->getSekolahId();
        $query = Siswa::with('kelas');

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $query->where('sekolah_id', $sekolahId);
        }

        $siswa = $query->findOrFail($id);

        return view('admin.siswa.show', compact('siswa'));
    }

    public function edit(string $id)
    {
        $sekolahId = $this->getSekolahId();
        $siswaQuery = Siswa::query();

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $siswaQuery->where('sekolah_id', $sekolahId);
        }
        $siswa = $siswaQuery->findOrFail($id);

        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $kelas = Kelas::where('sekolah_id', $sekolahId)->orderBy('nama_kelas', 'asc')->get();
        } else {
            $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        }

        return view('admin.siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, string $id)
    {
        $sekolahId = $this->getSekolahId();
        $siswaQuery = Siswa::query();

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $siswaQuery->where('sekolah_id', $sekolahId);
        }
        $siswa = $siswaQuery->findOrFail($id);

        $validated = $request->validate([
            'nama_siswa'    => 'required|string|max:255',
            'nisn'          => ['required', 'string', 'max:20', Rule::unique('siswas', 'nisn')->ignore($siswa->id)],
            'kelas_id'      => 'nullable|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
            'password'      => 'nullable|string|min:6',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated, $siswa) {
            $oldNisn = $siswa->nisn;

            $updateData = [
                'nama_siswa'    => $validated['nama_siswa'],
                'nisn'          => $validated['nisn'],
                'kelas_id'      => $validated['kelas_id'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat'        => $validated['alamat'] ?? null,
            ];

            if ($request->hasFile('foto')) {
                if (!empty($siswa->foto) && Storage::disk('public')->exists($siswa->foto)) {
                    Storage::disk('public')->delete($siswa->foto);
                }
                $updateData['foto'] = $request->file('foto')->store('foto_siswa', 'public');
            }

            $siswa->update($updateData);

            $user = null;
            if (Schema::hasColumn('siswas', 'user_id') && $siswa->user_id) {
                $user = User::find($siswa->user_id);
            } else {
                $user = User::where('email', $oldNisn)
                    ->orWhere('email', $oldNisn . '@siswa.com')
                    ->first();
            }

            if ($user) {
                $userUpdate = [
                    'name'  => $validated['nama_siswa'],
                    'email' => $validated['nisn'],
                ];
                if (!empty($validated['password'])) {
                    $userUpdate['password'] = Hash::make($validated['password']);
                }
                $user->update($userUpdate);
            }
        });

        $message = 'Data siswa dan akun user berhasil diperbarui.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $siswa->fresh('kelas'),
            ]);
        }

        return redirect()->route('admin.siswa.index')->with('success', $message);
    }

    public function destroy(Request $request, string $id)
    {
        $sekolahId = $this->getSekolahId();
        $siswaQuery = Siswa::query();

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $siswaQuery->where('sekolah_id', $sekolahId);
        }
        $siswa = $siswaQuery->findOrFail($id);

        DB::transaction(function () use ($siswa) {
            if (!empty($siswa->foto) && Storage::disk('public')->exists($siswa->foto)) {
                Storage::disk('public')->delete($siswa->foto);
            }

            if (Schema::hasColumn('siswas', 'user_id') && $siswa->user_id) {
                User::where('id', $siswa->user_id)->delete();
            } else {
                User::where('email', $siswa->nisn)
                    ->orWhere('email', $siswa->nisn . '@siswa.com')
                    ->delete();
            }
            $siswa->delete();
        });

        $message = 'Data siswa berhasil dihapus.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.siswa.index')->with('success', $message);
    }

    public function import(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $sekolahId = $this->getSekolahId();
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $isAjax = $request->expectsJson() || $request->ajax();

        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $rows = $this->parseCsvFile($file->getPathname());
            } elseif ($extension === 'xlsx') {
                $rows = $this->parseXlsxFile($file->getPathname());
            } else {
                $msg = 'Format file tidak didukung. Gunakan CSV atau XLSX.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : redirect()->back()->with('error', $msg);
            }

            if (empty($rows) || count($rows) < 2) {
                $msg = 'File kosong atau tidak memiliki data!';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : redirect()->back()->with('error', $msg);
            }

            $rawHeader = array_shift($rows);
            $header = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string) $h)));
            }, $rawHeader);

            $kelasQuery = Kelas::query();
            if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
                $kelasQuery->where('sekolah_id', $sekolahId);
            }
            $kelasMapRaw = $kelasQuery->pluck('id', 'nama_kelas')->toArray();
            $kelasMap = [];
            foreach ($kelasMapRaw as $namaKls => $idKls) {
                $kelasMap[strtolower(trim($namaKls))] = $idKls;
            }

            $imported = 0;
            $skipped = 0;

            DB::transaction(function () use ($rows, $header, $kelasMap, $sekolahId, &$imported, &$skipped) {
                foreach ($rows as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $dataRow = [];
                    foreach ($header as $index => $colName) {
                        $dataRow[$colName] = isset($row[$index]) ? trim((string) $row[$index]) : '';
                    }

                    $nisn = $dataRow['nisn'] ?? '';
                    $namaSiswa = $dataRow['nama_siswa'] ?? $dataRow['nama'] ?? '';
                    $jenisKelamin = strtoupper($dataRow['jenis_kelamin'] ?? 'L');
                    $kelasInput = strtolower(trim($dataRow['kelas_id'] ?? $dataRow['nama_kelas'] ?? $dataRow['kelas'] ?? ''));
                    $alamat = $dataRow['alamat'] ?? null;

                    $emailUser = !empty($dataRow['email']) ? $dataRow['email'] : $nisn;
                    $plainPassword = !empty($dataRow['password']) ? $dataRow['password'] : ($nisn . '*');

                    $kelasId = null;
                    if (!empty($kelasInput)) {
                        if (is_numeric($kelasInput)) {
                            $kelasId = array_search((int) $kelasInput, $kelasMap) !== false ? (int) $kelasInput : null;
                        } elseif (isset($kelasMap[$kelasInput])) {
                            $kelasId = $kelasMap[$kelasInput];
                        }
                    }

                    if (!empty($nisn) && !empty($namaSiswa)) {
                        $userPayload = [
                            'name'     => $namaSiswa,
                            'password' => Hash::make($plainPassword),
                        ];
                        if (Schema::hasColumn('users', 'role')) {
                            $userPayload['role'] = 'siswa';
                        }
                        if ($sekolahId && Schema::hasColumn('users', 'sekolah_id')) {
                            $userPayload['sekolah_id'] = $sekolahId;
                        }

                        $user = User::updateOrCreate(
                            ['email' => $emailUser],
                            $userPayload
                        );

                        $payload = [
                            'nama_siswa'    => $namaSiswa,
                            'jenis_kelamin' => in_array($jenisKelamin, ['L', 'P']) ? $jenisKelamin : 'L',
                            'kelas_id'      => $kelasId,
                            'alamat'        => $alamat ?: null,
                        ];
                        if (Schema::hasColumn('siswas', 'user_id')) {
                            $payload['user_id'] = $user->id;
                        }
                        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
                            $payload['sekolah_id'] = $sekolahId;
                        }

                        Siswa::updateOrCreate(['nisn' => $nisn], $payload);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
            });

            if ($imported === 0) {
                $msg = 'Gagal memproses data! Pastikan kolom NISN dan Nama Siswa terisi.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : redirect()->back()->with('error', $msg);
            }

            $successMsg = "Berhasil mengimport {$imported} data siswa" . ($skipped > 0 ? " ({$skipped} baris dilewati)." : ".");

            if ($isAjax) {
                return response()->json([
                    'success'  => true,
                    'message'  => $successMsg,
                    'imported' => $imported,
                    'skipped'  => $skipped,
                ]);
            }

            return redirect()->route('admin.siswa.index')->with('success', $successMsg);
        } catch (\Exception $e) {
            $errMsg = 'Terjadi kesalahan: ' . $e->getMessage();
            return $isAjax
                ? response()->json(['success' => false, 'message' => $errMsg], 500)
                : redirect()->back()->with('error', $errMsg);
        }
    }

    private function parseXlsxFile($filepath)
    {
        $zip = new \ZipArchive();
        if ($zip->open($filepath) !== true) {
            return [];
        }
        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xmlStr = $zip->getFromIndex($index);
            $xml = simplexml_load_string($xmlStr);
            foreach ($xml->si as $val) {
                $sharedStrings[] = (string) ($val->t ?? $val->r->t ?? '');
            }
        }
        $sheetXmlStr = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXmlStr) {
            return [];
        }
        $xml = simplexml_load_string($sheetXmlStr);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowCells = [];
            $lastColIndex = 0;
            foreach ($row->c as $cell) {
                preg_match('/[A-Z]+/', (string) $cell['r'], $matches);
                $colLetter = $matches[0] ?? 'A';
                $colIndex = 0;
                for ($i = 0; $i < strlen($colLetter); $i++) {
                    $colIndex = $colIndex * 26 + (ord($colLetter[$i]) - ord('A')) + 1;
                }
                $colIndex--;
                while ($lastColIndex < $colIndex) {
                    $rowCells[] = '';
                    $lastColIndex++;
                }
                $cellValue = (string) ($cell->v ?? '');
                $cellType = (string) ($cell['t'] ?? '');
                if ($cellType === 's' && isset($sharedStrings[(int) $cellValue])) {
                    $cellValue = $sharedStrings[(int) $cellValue];
                }
                $rowCells[] = $cellValue;
                $lastColIndex++;
            }
            $rows[] = $rowCells;
        }
        return $rows;
    }

    private function parseCsvFile($filepath)
    {
        $rows = [];
        if (($handle = fopen($filepath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    public function cetakKartu($id = null)
    {
        $sekolahId = $this->getSekolahId();

        $semuaKelasQuery = Kelas::query();
        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $semuaKelasQuery->where('sekolah_id', $sekolahId);
        }
        $semuaKelas = $semuaKelasQuery->orderBy('nama_kelas', 'asc')->get();

        $query = Siswa::with(['kelas', 'sekolah'])
            ->select('siswas.*')
            ->leftJoin('kelas', 'siswas.kelas_id', '=', 'kelas.id');

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $query->where('siswas.sekolah_id', $sekolahId);
        }

        if (!$id || !is_numeric($id)) {
            $siswas = $query->orderBy('kelas.nama_kelas', 'asc')
                ->orderBy('siswas.nama_siswa', 'asc')
                ->get();
            $kelasPilih = null;
            $namaKelasDipilih = 'Semua Kelas';
        } else {
            $siswa = Siswa::with(['kelas', 'sekolah'])->find($id);
            if (!$siswa) {
                return redirect()->route('admin.siswa.index')->with('error', 'Data siswa tidak ditemukan.');
            }
            $siswas = collect([$siswa]);
            $kelasPilih = $siswa->kelas_id;
            $namaKelasDipilih = $siswa->kelas->nama_kelas ?? 'Tanpa Kelas';
        }

        return view('admin.siswa.cetak-kartu', compact('siswas', 'semuaKelas', 'kelasPilih', 'namaKelasDipilih'));
    }

    public function cetakKartuKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        $sekolahId = $this->getSekolahId();
        $kelasId = $request->kelas_id;

        $semuaKelasQuery = Kelas::query();
        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $semuaKelasQuery->where('sekolah_id', $sekolahId);
        }
        $semuaKelas = $semuaKelasQuery->orderBy('nama_kelas', 'asc')->get();

        $kelasObj = Kelas::findOrFail($kelasId);
        $namaKelasDipilih = $kelasObj->nama_kelas;
        $kelasPilih = $kelasId;

        $query = Siswa::with(['kelas', 'sekolah'])
            ->where('kelas_id', $kelasId);

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $query->where('sekolah_id', $sekolahId);
        }

        $siswas = $query->orderBy('nama_siswa', 'asc')->get();

        if ($siswas->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa pada kelas tersebut.');
        }

        $judulUjian = $request->judul_ujian;

        return view('admin.siswa.cetak-kartu', compact('siswas', 'semuaKelas', 'kelasPilih', 'namaKelasDipilih', 'judulUjian'));
    }

    public function printPreview(Request $request)
    {
        $sekolahId = $this->getSekolahId();
        $kelasId = $request->kelas_id;
        $judulUjian = $request->judul_ujian;

        $query = Siswa::with(['kelas', 'sekolah'])
            ->select('siswas.*')
            ->leftJoin('kelas', 'siswas.kelas_id', '=', 'kelas.id');

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $query->where('siswas.sekolah_id', $sekolahId);
        }

        if ($kelasId) {
            $query->where('siswas.kelas_id', $kelasId);
            $kelasObj = Kelas::find($kelasId);
            $namaKelasDipilih = $kelasObj ? $kelasObj->nama_kelas : '';
        } else {
            $namaKelasDipilih = 'Semua Kelas';
        }

        $siswas = $query->orderBy('kelas.nama_kelas', 'asc')
            ->orderBy('siswas.nama_siswa', 'asc')
            ->get();
        return view('admin.siswa.print-pure', compact('siswas', 'namaKelasDipilih', 'judulUjian'));
    }
}
