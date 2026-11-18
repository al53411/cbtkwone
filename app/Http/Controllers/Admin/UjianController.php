<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiUjian;
use App\Models\Soal;
use App\Models\JawabanSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel = class_exists(Mapel::class) ? Mapel::all() : collect();
        $kelases   = $listKelas;

        $query = Ujian::with(['kelas', 'mapel']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_ujian', 'LIKE', "%{$search}%")
                    ->orWhereHas('mapel', function ($qm) use ($search) {
                        $qm->where('nama_mapel', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(10)->withQueryString();

        return view('admin.ujian.index', compact('ujians', 'listKelas', 'listMapel', 'kelases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapels,id',
            'token'         => 'nullable|string|max:10',
            'durasi'        => 'required|integer|min:1',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable',
            'acak_jawaban'  => 'nullable',
        ]);

        $tokenUjian = $request->filled('token')
            ? strtoupper(trim($request->token))
            : strtoupper(Str::random(6));

        $acakSoalVal    = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        $dataUjian = [
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'token'         => $tokenUjian,
            'durasi'        => $validated['durasi'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'status'        => $validated['status'],
            'acak_soal'     => DB::raw($acakSoalVal),
            'acak_jawaban'  => DB::raw($acakJawabanVal),
        ];

        if (Schema::hasColumn('ujians', 'sekolah_id') && Auth::user()?->sekolah_id) {
            $dataUjian['sekolah_id'] = Auth::user()->sekolah_id;
        }

        Ujian::create($dataUjian);

        return redirect()->route('admin.ujian.index')->with('success', 'Sesi ujian dan token mapel berhasil dibuat!');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapels,id',
            'token'         => 'nullable|string|max:10',
            'durasi'        => 'required|integer|min:1',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'status'        => 'required|in:draft,aktif,selesai',
            'acak_soal'     => 'nullable',
            'acak_jawaban'  => 'nullable',
        ]);

        $ujian = Ujian::findOrFail($id);

        $tokenUjian = $request->filled('token')
            ? strtoupper(trim($request->token))
            : $ujian->token;

        $acakSoalVal    = $request->has('acak_soal') ? 'true' : 'false';
        $acakJawabanVal = $request->has('acak_jawaban') ? 'true' : 'false';

        $ujian->update([
            'nama_ujian'    => $validated['nama_ujian'],
            'kelas_id'      => $validated['kelas_id'],
            'mapel_id'      => $validated['mapel_id'],
            'token'         => $tokenUjian,
            'durasi'        => $validated['durasi'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'status'        => $validated['status'],
            'acak_soal'     => DB::raw($acakSoalVal),
            'acak_jawaban'  => DB::raw($acakJawabanVal),
        ]);

        return redirect()->route('admin.ujian.index')->with('success', 'Data sesi ujian dan mapel berhasil diperbarui!');
    }

    // 1. Halaman Rekap Semua Hasil Ujian (Menu Sidebar)
    public function rekapNilai(Request $request)
    {
        $listUjian = Ujian::with('mapel')->latest()->get();
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $query = NilaiUjian::with(['siswa.kelas', 'ujian.mapel']);

        if ($request->filled('ujian_id')) {
            $query->where('ujian_id', $request->ujian_id);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }

        $hasilUjians = $query->latest()->paginate(15)->withQueryString();

        return view('admin.ujian.rekap', compact('hasilUjians', 'listUjian', 'listKelas'));
    }

    // 2. Halaman Detail Hasil Per Sesi Ujian
    public function hasil(Request $request, string $id)
    {
        $ujian = Ujian::with(['kelas', 'mapel'])->findOrFail($id);

        $query = NilaiUjian::with(['siswa.kelas'])
            ->where('ujian_id', $id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }

        $hasilUjians = $query->orderBy('nilai', 'desc')->get();

        $statistik = [
            'total_peserta' => $hasilUjians->count(),
            'rata_rata'     => $hasilUjians->avg('nilai') ? round($hasilUjians->avg('nilai'), 2) : 0,
            'tertinggi'     => $hasilUjians->max('nilai') ?? 0,
            'terendah'      => $hasilUjians->min('nilai') ?? 0,
        ];

        return view('admin.ujian.hasil', compact('ujian', 'hasilUjians', 'statistik'));
    }

    // 3. Halaman Cetak Rekap Nilai Per Sesi Ujian
    public function cetakHasil(string $id)
    {
        $ujian = Ujian::with(['kelas', 'mapel'])->findOrFail($id);

        $hasilUjians = NilaiUjian::with(['siswa.kelas'])
            ->where('ujian_id', $id)
            ->orderBy('nilai', 'desc')
            ->get();

        return view('admin.ujian.cetak-hasil', compact('ujian', 'hasilUjians'));
    }

    // 4. Halaman Daftar Sesi Ujian untuk Menu Berita Acara (Sidebar)
    public function indexBeritaAcara(Request $request)
    {
        $user      = Auth::user();
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $query = Ujian::with(['kelas', 'mapel'])
            ->withCount('nilaiUjians');

        // Tampilkan ujian milik sekolah ini ATAU ujian lama yang sekolah_id-nya masih NULL
        if ($user && $user->role !== 'superadmin' && $user->sekolah_id && Schema::hasColumn('ujians', 'sekolah_id')) {
            $query->where(function ($q) use ($user) {
                $q->where('sekolah_id', $user->sekolah_id)
                    ->orWhereNull('sekolah_id');
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_ujian', 'LIKE', "%{$search}%")
                    ->orWhereHas('mapel', fn($qm) => $qm->where('nama_mapel', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $ujians = $query->latest()->paginate(12)->withQueryString();

        return view('admin.ujian.berita-acara-index', compact('ujians', 'listKelas'));
    }

    // 5. Halaman Cetak Berita Acara Pelaksanaan Ujian CAT
    public function cetakBeritaAcara(Request $request, string $id)
    {
        /** @var \App\Models\User|null $user */
        $user  = Auth::user();
        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($id);

        // Ambil sekolah ujian atau sekolah milik user yang login
        $sekolahId = $ujian->sekolah_id ?? ($user->sekolah_id ?? null);

        // Proteksi multi-tenant sekolah
        if ($user && $user->role !== 'superadmin' && $user->sekolah_id && $ujian->sekolah_id && $ujian->sekolah_id !== $user->sekolah_id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ujian sekolah lain.');
        }

        // Ambil daftar hasil ujian peserta
        $hasilUjians = NilaiUjian::with(['siswa.kelas'])
            ->where('ujian_id', $id)
            ->get();

        // Hitung total peserta seharusnya (berdasarkan kelas ujian & sekolah)
        $totalTerdaftar = Siswa::query()
            ->when($sekolahId && Schema::hasColumn('siswas', 'sekolah_id'), fn($q) => $q->where('sekolah_id', $sekolahId))
            ->when($ujian->kelas_id, fn($q) => $q->where('kelas_id', $ujian->kelas_id))
            ->count();

        $jumlahHadir      = $hasilUjians->count();
        $jumlahTidakHadir = max(0, $totalTerdaftar - $jumlahHadir);

        // Default pengawas dari relasi guru (jika ada) atau user yang mencetak
        $defaultPengawas    = $user->name ?? '........................................';
        $defaultNipPengawas = $user->nip ?? '-';

        if (method_exists($ujian, 'guru') && $ujian->guru) {
            $defaultPengawas    = $ujian->guru->name ?? $defaultPengawas;
            $defaultNipPengawas = $ujian->guru->nip ?? $defaultNipPengawas;
        }

        // Input kustom dari form toolbar berita acara
        $nomorBa         = $request->input('nomor_ba', 'BA-CAT/' . str_pad($ujian->id, 3, '0', STR_PAD_LEFT) . '/' . date('Y'));
        $ruangUjian      = $request->input('ruang', 'Ruang Lab Komputer / CAT 01');
        $namaPengawas    = $request->input('pengawas', $defaultPengawas);
        $nipPengawas     = $request->input('nip_pengawas', $defaultNipPengawas);
        $catatanKejadian = $request->input('catatan', 'Pelaksanaan ujian berjalan dengan tertib, lancar, dan aman tanpa kendala teknis yang berarti.');

        return view('admin.ujian.cetak-berita-acara', compact(
            'ujian',
            'hasilUjians',
            'totalTerdaftar',
            'jumlahHadir',
            'jumlahTidakHadir',
            'nomorBa',
            'ruangUjian',
            'namaPengawas',
            'nipPengawas',
            'catatanKejadian'
        ));
    }

    /**
     * Halaman Monitoring Peserta Live per Sesi Ujian
     */
    public function monitoringPeserta(Request $request, $id)
    {
        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($id);

        $query = NilaiUjian::with(['siswa.kelas'])
            ->where('ujian_id', $id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pesertas = $query->orderBy('updated_at', 'desc')->get();

        return view('admin.ujian.monitoring', compact('ujian', 'pesertas'));
    }

    /**
     * 1. RESET LOGIN: Melepas kunci sesi perangkat siswa tanpa menghapus jawaban
     */
    public function resetLogin($nilaiUjianId)
    {
        $nilaiUjian = NilaiUjian::with('siswa')->findOrFail($nilaiUjianId);

        if ($nilaiUjian->siswa) {
            DB::table('siswas')->where('id', $nilaiUjian->siswa->id)->update([
                'session_token'  => null,
                'remember_token' => null,
                'updated_at'     => now(),
            ]);

            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $nilaiUjian->siswa->id)->delete();
            }
        }

        $namaSiswa = $nilaiUjian->siswa->nama ?? $nilaiUjian->siswa->nama_lengkap ?? 'Siswa';

        return redirect()->back()->with('success', "Sesi login {$namaSiswa} berhasil direset. Siswa dapat masuk kembali dan melanjutkan ujian.");
    }

    /**
     * 2. PAKSA SELESAI: Menghitung skor akhir dari jawaban tersimpan & mengunci ujian
     */
    public function paksaSelesai($nilaiUjianId)
    {
        $nilaiUjian = NilaiUjian::with(['siswa', 'ujian'])->findOrFail($nilaiUjianId);

        if ($nilaiUjian->status === 'selesai') {
            return redirect()->back()->with('error', 'Peserta ini sudah berstatus selesai.');
        }

        DB::transaction(function () use ($nilaiUjian) {
            $totalSoal = Soal::where('ujian_id', $nilaiUjian->ujian_id)->count();

            if ($totalSoal === 0 && $nilaiUjian->ujian) {
                $totalSoal = Soal::where('mapel_id', $nilaiUjian->ujian->mapel_id)
                    ->where('kelas_id', $nilaiUjian->ujian->kelas_id)
                    ->count();
            }

            $jawabans = JawabanSiswa::with('soal')
                ->where('ujian_id', $nilaiUjian->ujian_id)
                ->where('siswa_id', $nilaiUjian->siswa_id)
                ->get();

            $benar = 0;
            $salah = 0;

            foreach ($jawabans as $jwb) {
                if (empty($jwb->jawaban)) {
                    continue;
                }

                $kunci        = strtoupper(trim($jwb->soal->kunci_jawaban ?? ''));
                $pilihanSiswa = strtoupper(trim($jwb->jawaban));

                if ($kunci !== '' && $pilihanSiswa === $kunci) {
                    $benar++;
                } else {
                    $salah++;
                }
            }

            $tidakDijawab = max(0, $totalSoal - ($benar + $salah));
            $skorAkhir    = $totalSoal > 0 ? round(($benar / $totalSoal) * 100, 2) : 0;

            $nilaiUjian->update([
                'jumlah_benar'  => $benar,
                'jumlah_salah'  => $salah,
                'tidak_dijawab' => $tidakDijawab,
                'nilai'         => $skorAkhir,
                'status'        => 'selesai',
                'waktu_selesai' => now(),
            ]);
        });

        $namaSiswa = $nilaiUjian->siswa->nama ?? $nilaiUjian->siswa->nama_lengkap ?? 'Siswa';

        return redirect()->back()->with('success', "Ujian {$namaSiswa} berhasil diselesaikan paksa dan nilai telah dikalkulasi.");
    }

    /**
     * 3. RESET UJIAN (SUSULAN / REMEDIAL): Menghapus riwayat ujian siswa agar mengulang dari awal
     */
    public function resetUjian($nilaiUjianId)
    {
        $nilaiUjian = NilaiUjian::with('siswa')->findOrFail($nilaiUjianId);
        $namaSiswa  = $nilaiUjian->siswa->nama ?? $nilaiUjian->siswa->nama_lengkap ?? 'Siswa';

        DB::transaction(function () use ($nilaiUjian) {
            JawabanSiswa::where('ujian_id', $nilaiUjian->ujian_id)
                ->where('siswa_id', $nilaiUjian->siswa_id)
                ->delete();

            if ($nilaiUjian->siswa) {
                DB::table('siswas')->where('id', $nilaiUjian->siswa->id)->update([
                    'session_token' => null,
                ]);
            }

            $nilaiUjian->delete();
        });

        return redirect()->back()->with('success', "Data ujian {$namaSiswa} berhasil direset total untuk ujian susulan/remedial.");
    }
}
