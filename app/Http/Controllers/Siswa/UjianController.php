<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\JawabanSiswa; // Model tabel jawaban
use App\Models\NilaiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UjianController extends Controller
{
    /**
     * Memproses verifikasi token ujian dari siswa
     */
    public function verifikasiToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:10'
        ], [
            'token.required' => 'Token ujian wajib diisi!',
        ]);

        $siswa = Auth::guard('siswa')->user();

        if (!$siswa || !$siswa->kelas_id) {
            return back()->with('error', 'Data kelas siswa tidak ditemukan. Silakan hubungi proktor/admin.');
        }

        $tokenInput = strtoupper(trim($request->token));
        $now = Carbon::now();

        // Cari ujian yang aktif, sesuai token, sesuai kelas siswa/semua kelas, dan waktunya valid
        $ujian = Ujian::where('token', $tokenInput)
            ->where('status', 'aktif')
            ->where(function ($query) use ($siswa) {
                $query->where('kelas_id', $siswa->kelas_id)
                    ->orWhereNull('kelas_id');
            })
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->first();

        if (!$ujian) {
            return back()->with('error', 'Token salah, ujian tidak aktif, waktu ujian belum/sudah berakhir, atau bukan untuk kelas Anda!');
        }

        // Simpan session ujian aktif siswa
        session(['current_ujian_id' => $ujian->id]);

        return redirect()->route('siswa.ujian.mulai')
            ->with('success', 'Token valid! Selamat mengerjakan.');
    }

    /**
     * Menampilkan halaman pengerjaan soal ujian
     */
    public function mulai()
    {
        $siswa = Auth::guard('siswa')->user();
        $ujianId = session('current_ujian_id');

        if (!$ujianId) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Silakan masukkan token ujian terlebih dahulu.');
        }

        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId);

        // Ambil daftar soal berdasarkan mapel_id ujian DAN kelas_id siswa yang sedang login
        $querySoal = Soal::where('mapel_id', $ujian->mapel_id)
            ->where('kelas_id', $siswa->kelas_id);

        // Terapkan pengaturan acak soal jika diaktifkan oleh Superadmin/Admin
        if ($ujian->acak_soal == 1 || $ujian->acak_soal === true || $ujian->acak_soal === 'true') {
            $querySoal->inRandomOrder();
        }

        $soals = $querySoal->get();

        return view('siswa.ujian.mulai', compact('siswa', 'ujian', 'soals'));
    }
    public function simpanJawaban(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required',
            'soal_id'  => 'required',
            'jawaban'  => 'required'
        ]);

        $siswa = Auth::guard('siswa')->user();

        // 1. Ambil data soal untuk mengecek kunci jawaban
        $soal = Soal::findOrFail($request->soal_id);

        // 2. Cek apakah jawaban siswa benar (Case-insensitive)
        $isBenar = (strtoupper($request->jawaban) === strtoupper($soal->kunci_jawaban)) ? 1 : 0;

        // 3. Simpan atau Update jawaban di database
        // Menggunakan updateOrCreate agar jika siswa mengganti jawaban, data lama akan tertimpa
        JawabanSiswa::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'ujian_id' => $request->ujian_id,
                'soal_id'  => $request->soal_id,
            ],
            [
                'jawaban'  => strtoupper($request->jawaban),
                'is_benar' => $isBenar
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Jawaban berhasil disimpan'
        ]);
    }

    /**
     * Mengakhiri ujian dan menghitung nilai (Benar, Salah, Kosong, Skor)
     */
    public function selesaiUjian(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required'
        ]);

        $siswa = Auth::guard('siswa')->user();
        $ujian = Ujian::findOrFail($request->ujian_id);

        // 1. Hitung total soal untuk mapel dan kelas ini
        $totalSoal = Soal::where('mapel_id', $ujian->mapel_id)
            ->where('kelas_id', $siswa->kelas_id)
            ->count();

        // 2. Hitung jumlah jawaban BENAR dan SALAH dari tabel jawaban_siswa
        $jumlahBenar = JawabanSiswa::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->where('is_benar', 1)
            ->count();

        $jumlahSalah = JawabanSiswa::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->where('is_benar', 0)
            ->whereNotNull('jawaban')
            ->count();

        // 3. Hitung soal yang tidak dijawab (kosong)
        $tidakDijawab = $totalSoal - ($jumlahBenar + $jumlahSalah);

        // 4. Kalkulasi Nilai Akhir (Skala 1-100)
        $nilaiAkhir = $totalSoal > 0 ? ($jumlahBenar / $totalSoal) * 100 : 0;

        // 5. Simpan Rekap Nilai ke Database
        NilaiUjian::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'ujian_id' => $ujian->id,
            ],
            [
                'jumlah_benar'  => $jumlahBenar,
                'jumlah_salah'  => $jumlahSalah,
                'tidak_dijawab' => $tidakDijawab,
                'nilai'         => round($nilaiAkhir, 2), // Bulatkan 2 angka desimal
                'waktu_selesai' => Carbon::now(),
                'status'        => 'selesai'
            ]
        );

        // 6. Hapus sesi ujian agar siswa tidak bisa kembali mengerjakan
        session()->forget('current_ujian_id');

        return response()->json([
            'status'  => 'success',
            'message' => 'Ujian selesai, nilai berhasil dihitung.'
        ]);
    }
}
