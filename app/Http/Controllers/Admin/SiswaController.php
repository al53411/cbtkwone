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
     * Menampilkan daftar siswa (Menyediakan Filter Kelas & Pencarian Nama/NISN)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $sekolahId = $this->getSekolahId();

        // 1. Ambil daftar kelas untuk dropdown filter di Blade
        $kelasQuery = Kelas::query();
        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $kelasQuery->where('sekolah_id', $sekolahId);
        }
        $listKelas = $kelasQuery->orderBy('nama_kelas', 'asc')->get();

        // 2. Query Utama Siswa
        $query = Siswa::with('kelas');

        // LOGIKA PENYARINGAN ROLE GURU VS ADMIN
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
                $query->whereIn('kelas_id', $allKelasIds);
            } else if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
                $query->where(function($q) use ($sekolahId) {
                    $q->where('sekolah_id', $sekolahId)
                      ->orWhereNull('sekolah_id');
                });
            }
        } else {
            // Role Admin / Superadmin
            if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
                $query->where(function($q) use ($sekolahId) {
                    $q->where('sekolah_id', $sekolahId)
                      ->orWhereNull('sekolah_id');
                });
            }
        }

        // 3. APPLY FILTER: Dropdown Pilih Kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // 4. APPLY FILTER: Input Pencarian (Nama Siswa / NISN)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                if (Schema::hasColumn('siswas', 'nama_siswa')) {
                    $q->where('nama_siswa', 'like', "%{$search}%");
                } else {
                    $q->where('nama_lengkap', 'like', "%{$search}%");
                }
                $q->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswas = $query->paginate(10);

        // FALLBACK: Jika Guru tidak di-assign kelas spesifik dan hasil pencarian/filter awal kosong
        if ($siswas->isEmpty() && !$request->filled('search') && !$request->filled('kelas_id') && $user && ($user->isGuru() || $user->role === 'guru' || $user->guru) && $sekolahId) {
            $siswas = Siswa::with('kelas')
                ->where(function($q) use ($sekolahId) {
                    $q->where('sekolah_id', $sekolahId)
                      ->orWhereNull('sekolah_id');
                })
                ->latest()
                ->get();
        }

        return view('admin.siswa.index', compact('siswas', 'listKelas'));
    }

    /**
     * Menampilkan form untuk menambah siswa baru.
     */
    public function create()
    {
        $sekolahId = $this->getSekolahId();

        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $kelas = Kelas::where('sekolah_id', $sekolahId)->get();
        } else {
            $kelas = Kelas::all();
        }

        return view('admin.siswa.create', compact('kelas'));
    }

    /**
     * Menyimpan data siswa baru ke database + AKUN USER (NISN & NISN*)
     */
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
        ], [
            'nama_siswa.required'    => 'Nama siswa wajib diisi!',
            'nisn.unique'            => 'NISN sudah terdaftar dalam sistem!',
            'nisn.required'          => 'NISN wajib diisi!',
            'kelas_id.exists'        => 'Kelas yang dipilih belum di-setting atau tidak ditemukan!',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih!',
        ]);

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

        if (Schema::hasColumn('siswas', 'user_id')) {
            $data['user_id'] = $user->id;
        }

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $data['sekolah_id'] = $sekolahId;
        }

        Siswa::create($data);

        return redirect()->route('admin.siswa.index')
            ->with('success', "Data siswa & akun user berhasil ditambahkan (User: {$emailUser}, Password: {$validated['nisn']}*).");
    }

    /**
     * Menampilkan detail spesifik seorang siswa.
     */
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

    /**
     * Menampilkan form untuk mengedit data siswa.
     */
    public function edit(string $id)
    {
        $sekolahId = $this->getSekolahId();
        $siswaQuery = Siswa::query();

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $siswaQuery->where('sekolah_id', $sekolahId);
        }
        $siswa = $siswaQuery->findOrFail($id);

        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $kelas = Kelas::where('sekolah_id', $sekolahId)->get();
        } else {
            $kelas = Kelas::all();
        }

        return view('admin.siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * Memperbarui data siswa & akun user terikat di database.
     */
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
        ], [
            'nama_siswa.required'    => 'Nama siswa wajib diisi!',
            'nisn.unique'            => 'NISN sudah digunakan oleh siswa lain!',
            'nisn.required'          => 'NISN wajib diisi!',
            'kelas_id.exists'        => 'Kelas yang dipilih belum di-setting atau tidak ditemukan!',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih!',
        ]);

        $oldNisn = $siswa->nisn;

        $siswa->update([
            'nama_siswa'    => $validated['nama_siswa'],
            'nisn'          => $validated['nisn'],
            'kelas_id'      => $validated['kelas_id'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat'        => $validated['alamat'] ?? null,
        ]);

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

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa dan akun user berhasil diperbarui.');
    }

    /**
     * Menghapus data siswa dari database.
     */
    public function destroy(string $id)
    {
        $sekolahId = $this->getSekolahId();
        $siswaQuery = Siswa::query();

        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $siswaQuery->where('sekolah_id', $sekolahId);
        }
        $siswa = $siswaQuery->findOrFail($id);

        if (Schema::hasColumn('siswas', 'user_id') && $siswa->user_id) {
            User::where('id', $siswa->user_id)->delete();
        } else {
            User::where('email', $siswa->nisn)
                ->orWhere('email', $siswa->nisn . '@siswa.com')
            ->delete();
        }
        $siswa->delete();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Memproses import data siswa dari file Excel (.xlsx) atau CSV
     */
    public function import(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|max:5120',
        ], [
            'file.required' => 'File Excel/CSV wajib diunggah!',
        ]);

        $sekolahId = $this->getSekolahId();
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
                return redirect()->back()->with('error', 'File kosong atau tidak memiliki data!');
            }

            $rawHeader = array_shift($rows);
            $header = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$h)));
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

            \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $header, $kelasMap, $sekolahId, &$imported, &$skipped) {
                foreach ($rows as $row) {
                    if (empty(array_filter($row))) continue;
                    
                    $dataRow = [];
                    foreach ($header as $index => $colName) {
                        $dataRow[$colName] = isset($row[$index]) ? trim((string)$row[$index]) : '';
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
                            $kelasId = array_search((int)$kelasInput, $kelasMap) !== false ? (int)$kelasInput : null;
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
                return redirect()->back()->with('error', 'Gagal memproses data! Pastikan kolom nisn dan nama_siswa terisi.');
            }

            $msg = "Berhasil mengimport {$imported} data siswa. Akun User: NISN, Password: NISN*.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} baris dilewati karena NISN/Nama kosong).";
            }

            return redirect()->route('admin.siswa.index')->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengolah file: ' . $e->getMessage());
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
                $sharedStrings[] = (string)($val->t ?? $val->r->t ?? '');
            }
        }
        $sheetXmlStr = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXmlStr) return [];
        $xml = simplexml_load_string($sheetXmlStr);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowCells = [];
            $lastColIndex = 0;
            foreach ($row->c as $cell) {
                preg_match('/[A-Z]+/', (string)$cell['r'], $matches);
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
                $cellValue = (string)($cell->v ?? '');
                $cellType = (string)($cell['t'] ?? '');
                if ($cellType === 's' && isset($sharedStrings[(int)$cellValue])) {
                    $cellValue = $sharedStrings[(int)$cellValue];
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

   /**
     * Cetak kartu ujian untuk satu siswa tertentu atau fallback cetak semua
     */
    public function cetakKartu($id = null)
    {
        $sekolahId = $this->getSekolahId();
        
        // Ambil semua daftar kelas untuk dropdown filter
        $semuaKelasQuery = Kelas::query();
        if ($sekolahId && Schema::hasColumn('kelas', 'sekolah_id')) {
            $semuaKelasQuery->where('sekolah_id', $sekolahId);
        }
        $semuaKelas = $semuaKelasQuery->orderBy('nama_kelas', 'asc')->get();

        $query = Siswa::with(['kelas', 'sekolah']);
        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $query->where('sekolah_id', $sekolahId);
        }

        if (!$id) {
            // Urutkan berdasarkan kelas_id lalu nama siswa agar nomor meja per kelas akurat
            $siswas = $query->orderBy('kelas_id', 'asc')
                            ->orderBy('nama_siswa', 'asc')
                            ->get();
            $kelasPilih = null;
            $namaKelasDipilih = 'Semua Kelas';
        } else {
            $siswa = $query->find($id);
            if (!$siswa) {
                return redirect()->route('admin.siswa.index')->with('error', 'Data siswa tidak ditemukan.');
            }
            $siswas = collect([$siswa]);
            $kelasPilih = $siswa->kelas_id;
            $namaKelasDipilih = $siswa->kelas->nama_kelas ?? 'Tanpa Kelas';
        }

        return view('admin.siswa.cetak-kartu', compact('siswas', 'semuaKelas', 'kelasPilih', 'namaKelasDipilih'));
    }

    /**
     * Cetak kartu ujian massal berdasarkan kelas
     */
    public function cetakKartuKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        $sekolahId = $this->getSekolahId();
        $kelasId = $request->kelas_id;

        // Ambil semua daftar kelas untuk dropdown filter
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

        return view('admin.siswa.cetak-kartu', compact('siswas', 'semuaKelas', 'kelasPilih', 'namaKelasDipilih'));
    }

    /**
     * Menampilkan halaman preview cetak murni tanpa layout/sidebar admin
     */
    /**
     * Menampilkan halaman khusus cetak tanpa layout admin
     */
    public function printPreview(Request $request)
    {
        $sekolahId = $this->getSekolahId();
        $kelasId = $request->kelas_id;

        $query = Siswa::with(['kelas', 'sekolah']);
        if ($sekolahId && Schema::hasColumn('siswas', 'sekolah_id')) {
            $query->where('sekolah_id', $sekolahId);
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
            $kelasObj = Kelas::find($kelasId);
            $namaKelasDipilih = $kelasObj ? $kelasObj->nama_kelas : '';
        } else {
            $namaKelasDipilih = 'Semua Kelas';
        }

        $siswas = $query->orderBy('nama_siswa', 'asc')->get();

        return view('admin.siswa.print-pure', compact('siswas', 'namaKelasDipilih'));
    }
}