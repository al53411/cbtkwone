<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\NilaiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();
        $sekarang = Carbon::now('Asia/Jakarta');

        // Muat relasi mapel & kelas dengan aman
        $relasi = [];
        if (method_exists(Ujian::class, 'mapel')) $relasi[] = 'mapel';
        if (method_exists(Ujian::class, 'kelas')) $relasi[] = 'kelas';

        // Ambil daftar jadwal ujian khusus kelas siswa atau umum (null)
        $query = Ujian::with($relasi)
            ->where(function ($q) use ($siswa) {
                if ($siswa && isset($siswa->kelas_id)) {
                    $q->where('kelas_id', $siswa->kelas_id)
                        ->orWhereNull('kelas_id');
                }
            });

        // Filter juga berdasarkan sekolah siswa jika kolom sekolah_id tersedia
        if ($siswa && !empty($siswa->sekolah_id) && Schema::hasColumn('ujians', 'sekolah_id')) {
            $query->where(function ($q) use ($siswa) {
                $q->where('sekolah_id', $siswa->sekolah_id)
                    ->orWhereNull('sekolah_id');
            });
        }

        // Urutkan jadwal: yang berstatus 'aktif' di atas, lalu urut berdasarkan waktu_mulai
        if (Schema::hasColumn('ujians', 'waktu_mulai')) {
            $query->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
                ->orderBy('waktu_mulai', 'asc');
        } else {
            $query->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
                ->latest();
        }

        $ujianAktif = $query->get();

        // Ambil riwayat pengerjaan siswa ini (jika model NilaiUjian ada)
        $riwayatPengerjaan = collect();
        if ($siswa && class_exists(NilaiUjian::class)) {
            $riwayatPengerjaan = NilaiUjian::where('siswa_id', $siswa->id)
                ->get()
                ->keyBy('ujian_id');
        }

        return view('siswa.dashboard', compact(
            'siswa',
            'ujianAktif',
            'riwayatPengerjaan',
            'sekarang'
        ));
    }

    public function konfirmasiUjian(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:10',
        ], [
            'token.required' => 'Token ujian wajib diisi.',
        ]);

        $siswa = Auth::guard('siswa')->user();

        if (!$siswa) {
            return redirect()->route('siswa.login')
                ->with('error', 'Sesi Anda telah berakhir, silakan login kembali.');
        }

        $inputToken = strtoupper(trim($request->token));

        // Cari ujian yang statusnya 'aktif', token cocok, dan sesuai kelas siswa
        $query = Ujian::where('status', 'aktif')
            ->where('token', $inputToken)
            ->where(function ($q) use ($siswa) {
                if (isset($siswa->kelas_id)) {
                    $q->where('kelas_id', $siswa->kelas_id)
                        ->orWhereNull('kelas_id');
                }
            });

        if (!empty($siswa->sekolah_id) && Schema::hasColumn('ujians', 'sekolah_id')) {
            $query->where(function ($q) use ($siswa) {
                $q->where('sekolah_id', $siswa->sekolah_id)
                    ->orWhereNull('sekolah_id');
            });
        }

        $ujian = $query->first();

        if (!$ujian) {
            return back()
                ->withErrors(['token' => 'Token ujian tidak valid, belum aktif, atau bukan untuk kelas Anda!'])
                ->with('error', 'Token ujian tidak valid, belum aktif, atau bukan untuk kelas Anda!')
                ->withInput();
        }

        // Cek apakah siswa sudah menyelesaikan ujian ini sebelumnya
        if (class_exists(NilaiUjian::class)) {
            $sudahSelesai = NilaiUjian::where('siswa_id', $siswa->id)
                ->where('ujian_id', $ujian->id)
                ->where(function ($q) {
                    $q->where('status', 'selesai')
                        ->orWhereNotNull('waktu_selesai');
                })
                ->exists();

            if ($sudahSelesai) {
                return back()
                    ->with('error', 'Anda sudah menyelesaikan ujian ' . ($ujian->mapel->nama_mapel ?? 'ini') . ' sebelumnya.');
            }
        }

        // Simpan ID ujian ke dalam session siswa
        session(['current_ujian_id' => $ujian->id]);

        return redirect()->route('siswa.ujian.mulai')
            ->with('success', 'Token valid! Selamat mengerjakan ujian ' . ($ujian->mapel->nama_mapel ?? ''));
    }
}
