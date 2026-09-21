<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah; 
use App\Models\User;
use App\Models\Soal; // Atau BankSoal
use App\Models\Ujian; // Atau JadwalUjian
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung Total Sekolah
        $totalSekolah = Sekolah::count();

        // 2. Hitung Sekolah yang Sudah Menggelar Ujian (CBT Aktif)
        $sekolahAktifUjian = Sekolah::whereHas('users.ujians', function ($query) {
            $query->where('status', 'aktif'); // Atau filter berdasarkan tanggal ujian
        })->count();

        // 3. Persentase Keaktifan Sekolah dalam CBT
        $persenSekolahAktif = $totalSekolah > 0 
            ? round(($sekolahAktifUjian / $totalSekolah) * 100, 1) 
            : 0;

        // 4. Hitung Statistik Pengguna & Konten CBT
        $totalKepsek = Sekolah::whereNotNull('nama_kepsek')->where('nama_kepsek', '!=', '')->count();
        $totalGuru   = User::where('role', 'guru')->count();
        $totalSiswa  = User::where('role', 'siswa')->count(); // Disesuaikan dari tendik ke siswa
        
        // 5. Hitung Metrics Khas CBT (Opsional)
        $totalBankSoal = class_exists(Soal::class) ? Soal::count() : 0;
        $totalUjian    = class_exists(Ujian::class) ? Ujian::count() : 0;

        // 6. Data Sekolah dengan eager loading relasi pengguna & ujian
        $sekolahs = Sekolah::withCount(['users as total_siswa' => function ($q) {
            $q->where('role', 'siswa');
        }])->paginate(10);

        return view('superadmin.dashboard', compact(
            'totalSekolah',
            'totalKepsek',
            'totalGuru',
            'totalSiswa',
            'sekolahAktifUjian',
            'persenSekolahAktif',
            'totalBankSoal',
            'totalUjian',
            'sekolahs'
        ));
    }
}