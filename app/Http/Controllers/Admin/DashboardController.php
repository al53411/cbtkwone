<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id ?? null;

        $siswaQuery = Siswa::query();
        $guruQuery  = Guru::query();
        $kelasQuery = Kelas::query();
        $logQuery   = class_exists(ActivityLog::class) ? ActivityLog::query() : null;

        // Terapkan filter berdasarkan sekolah_id jika ada
        if ($sekolahId) {
            // Cek skema sekali/kondisional agar tidak membebani query
            if (Schema::hasColumn('siswas', 'sekolah_id')) {
                $siswaQuery->where('sekolah_id', $sekolahId);
            }

            if (Schema::hasColumn('gurus', 'sekolah_id')) {
                $guruQuery->where('sekolah_id', $sekolahId);
            }

            if (Schema::hasColumn('kelas', 'sekolah_id')) {
                $kelasQuery->where('sekolah_id', $sekolahId);
            }

            if ($logQuery && Schema::hasColumn('activity_logs', 'sekolah_id')) {
                $logQuery->where('sekolah_id', $sekolahId);
            }
        }

        // Ambil data aktivitas lengkap dengan eager loading user agar tidak terjadi N+1 Problem pada Blade
        $aktivitas = collect();
        if ($logQuery) {
            $aktivitas = $logQuery->with('user')->latest()->take(5)->get();
        }

        return view('admin.dashboard.index', [
            'total_siswa' => $siswaQuery->count(),
            'total_guru'  => $guruQuery->count(),
            'total_kelas' => $kelasQuery->count(),
            'aktivitas'   => $aktivitas,
        ]);
    }
}