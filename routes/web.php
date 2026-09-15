<?php

use App\Http\Controllers\ProfileController;

// Import Controller Superadmin / Admin Pusat  
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\SekolahController as SuperadminSekolahController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Superadmin\KepsekController;

// Import Controller Admin Sekolah
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\CetakAbsensiMapelController;
use App\Http\Controllers\Admin\GuruController as AdminGuruController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\KepalaSekolahController as AdminKepalaSekolahController;
use App\Http\Controllers\Admin\SekolahController as AdminSekolahController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\Admin\JurnalController as AdminJurnalController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\CapaianPembelajaranController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\TujuanPembelajaranController;
use App\Http\Controllers\Admin\AlurTujuanPembelajaranController;
use App\Http\Controllers\Admin\ModulAjarController;

// Import Controller Guru
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\GuruSiswaController;
use App\Http\Controllers\Guru\JurnalController as GuruJurnalController;
use App\Http\Controllers\Guru\AbsensiController as GuruAbsensiController;
use App\Http\Controllers\Guru\SekolahController as GuruSekolahController;
use App\Http\Controllers\Guru\ProfilController as GuruProfilController;
use App\Http\Controllers\Guru\ModulAjarController as GuruModulAjarController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

// Redirect Halaman Utama langsung ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// REDIRECT DASHBOARD BERDASARKAN ROLE
// ==========================================
Route::get('/dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    $role = strtolower(trim($user->role ?? ''));

    if ($role === 'superadmin') {
        return redirect()->route('superadmin.dashboard');
    } 

    if (in_array($role, ['admin', 'admin_sekolah', 'kepsek'], true)) {
        return redirect()->route('admin.dashboard');
    } 

    if ($role === 'guru') {
        return redirect()->route('guru.dashboard');
    }

    return redirect()->route('login')->withErrors(['login' => 'Role pengguna tidak terdaftar dengan benar.']);
})->middleware(['auth', 'verified'])->name('dashboard');


// ==========================================
// GROUP ROUTE KHUSUS SUPERADMIN
// ==========================================
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');

    // Route Manajemen Data Kepala Sekolah
    Route::get('/kepsek', [KepsekController::class, 'index'])->name('kepsek.index');
    Route::get('/kepsek/create', [KepsekController::class, 'create'])->name('kepsek.create');
    Route::post('/kepsek', [KepsekController::class, 'store'])->name('kepsek.store');
    Route::post('/kepsek/{id}/reset-password', [KepsekController::class, 'resetPassword'])->name('kepsek.reset-password');

    // CRUD Sekolah oleh Superadmin
    Route::resource('sekolah', SuperadminSekolahController::class);

    // CRUD Management Account User oleh Superadmin
    Route::resource('users', UserController::class);
});


// ==========================================
// GROUP ROUTE KHUSUS ADMIN SEKOLAH / KEPSEK
// ==========================================
Route::middleware(['auth', 'role:admin,admin_sekolah,kepsek,superadmin,guru'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Tahun Ajaran
    Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
    Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
    Route::put('/tahun-ajaran/{id}/set-aktif', [TahunAjaranController::class, 'setAktif'])->name('tahun-ajaran.set-aktif');

    // Validasi Jurnal oleh Kepala Sekolah / Admin
    Route::get('/validasi-jurnal', [AdminKepalaSekolahController::class, 'indexValidasiJurnal'])->name('kepala-sekolah.jurnal.index');
    Route::put('/validasi-jurnal/{id}', [AdminKepalaSekolahController::class, 'updateStatusJurnal'])->name('kepala-sekolah.jurnal.update');

    // Reset Password Guru
    Route::post('/guru/{guru}/reset-password', [AdminGuruController::class, 'resetPassword'])->name('guru.reset-password');

    // Import & Template Guru
    Route::post('/guru/import', [AdminGuruController::class, 'import'])->name('guru.import');
    Route::get('/guru/template', [AdminGuruController::class, 'downloadTemplate'])->name('guru.template');

    // Import Siswa
    Route::post('/siswa/import', [AdminSiswaController::class, 'import'])->name('siswa.import');

    // Cetak Absensi Mapel
    Route::get('/absensi/cetak-mapel', [CetakAbsensiMapelController::class, 'index'])->name('absensi.cetak-mapel');

    // Route Master Capaian Pembelajaran (CP)
    Route::resource('capaian-pembelajaran', CapaianPembelajaranController::class)->except(['create', 'edit', 'show']);

    // Route Resource Fitur Admin Sekolah
    Route::resource('absensi', AdminAbsensiController::class);
    Route::resource('guru', AdminGuruController::class);
    Route::resource('kelas', AdminKelasController::class);
    Route::resource('sekolah', AdminSekolahController::class);
    Route::resource('siswa', AdminSiswaController::class);
    Route::resource('jurnal', AdminJurnalController::class);
    Route::resource('kepala-sekolah', AdminKepalaSekolahController::class);

    // Route Mata Pelajaran
    Route::resource('mapel', MapelController::class);

    // Route Tujuan Pembelajaran
    Route::resource('tujuan-pembelajaran', TujuanPembelajaranController::class)->except(['create', 'edit', 'show']);
    Route::get('/guru/get-elemen-tp', [TujuanPembelajaranController::class, 'getElemenAndTp'])->name('guru.get_elemen_tp');
    Route::get('/get-elemen-cp', [TujuanPembelajaranController::class, 'getElemenByMapelFase'])->name('get_elemen_cp');

    // Route Alur Tujuan Pembelajaran (ATP) - DIPERBAIKI / DITAMBAHKAN KEMBALI
    Route::resource('atp', AlurTujuanPembelajaranController::class)->except(['create', 'edit', 'show']);

    // Route API AJAX untuk dropdown dinamis
    Route::get('/api/atp/by-tp', [AlurTujuanPembelajaranController::class, 'getByTp'])->name('api.atp.get_by_tp');

    // Route Modul Ajar Admin
    Route::resource('modul_ajar', ModulAjarController::class);

});


// ==========================================
// GROUP ROUTE KHUSUS GURU
// ==========================================
Route::middleware(['auth', 'role:guru,superadmin'])->prefix('guru')->name('guru.')->group(function () {

    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

    // Modul Profil Guru
    Route::get('/profil', [GuruProfilController::class, 'index'])->name('profil.index');
    Route::put('/profil', [GuruProfilController::class, 'update'])->name('profil.update');

    // Modul Absensi Siswa oleh Guru
    Route::get('/absensi', [GuruAbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi', [GuruAbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/absensi/rekap', [GuruAbsensiController::class, 'rekap'])->name('absensi.rekap');
    Route::get('/absensi/cetak', [GuruAbsensiController::class, 'cetakRekap'])->name('absensi.cetak');

    // Profil Sekolah khusus Guru (Read-Only)
    Route::get('/sekolah', [GuruSekolahController::class, 'index'])->name('sekolah.index');

    // Siswa khusus modul Guru
    Route::resource('siswa', GuruSiswaController::class)->only(['index', 'show']);
    
    // Kelas Guru
    Route::get('/kelas', [GuruSiswaController::class, 'kelasIndex'])->name('kelas.index');
    Route::get('/kelas/{id}', [GuruSiswaController::class, 'kelasShow'])->name('kelas.show');

    // Modul Ajar Guru (Pindahkan rute kustom ke ATAS resource)
    Route::get('/get-cp-filtered', [GuruModulAjarController::class, 'getCpFiltered'])->name('get_cp_filtered');
    Route::get('/get-tp-by-cp/{cp_id}', [GuruModulAjarController::class, 'getTpByCp'])->name('get_tp_by_cp');
    Route::get('/get-tp-filtered', [GuruModulAjarController::class, 'getTpFiltered'])->name('get_tp_filtered');
    Route::get('/modul-ajar/{id}/print', [GuruModulAjarController::class, 'print'])->name('modul_ajar.print');

    // Route Resource diletakkan di bawah rute kustom
    Route::resource('modul_ajar', GuruModulAjarController::class);
   
    // Cetak Rekap Jurnal
    Route::get('/jurnal/cetak-pdf', [GuruJurnalController::class, 'cetakWord'])->name('jurnal.cetak');
    Route::resource('jurnal', GuruJurnalController::class);

});

// ==========================================
// PROFILE MANAGEMENT (DEFAULT BREEZE/JETSTREAM)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


// ==========================================
// ROUTE UTILITY
// ==========================================
Route::get('/run-migrate', function () {
    if (request('key') !== '12345') {
        abort(403, 'Akses ditolak: Key salah!');
    }

    try {
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed'  => true,
        ]);

        return '
            <div style="font-family: sans-serif; padding: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; color: #166534;">
                <h2 style="margin-top:0;">✅ Migration & Seeding Berhasil!</h2>
                <pre style="background: #ffffff; padding: 15px; border-radius: 5px; border: 1px solid #e2e8f0; overflow-x: auto;">' . Artisan::output() . '</pre>
            </div>
        ';
    } catch (\Exception $e) {
        return '
            <div style="font-family: sans-serif; padding: 20px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b;">
                <h2 style="margin-top:0;">❌ Migration Gagal!</h2>
                <pre style="background: #ffffff; padding: 15px; border-radius: 5px; border: 1px solid #e2e8f0; overflow-x: auto;">' . $e->getMessage() . '</pre>
            </div>
        ';
    }
});