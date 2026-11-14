<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\DB;

// Import Controller Superadmin / Admin Pusat
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\SekolahController as SuperadminSekolahController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Superadmin\KepsekController;
use App\Http\Controllers\Superadmin\SoalController as SuperadminSoalController;
use App\Http\Controllers\Superadmin\KelasController as SuperadminKelasController;
use App\Http\Controllers\Superadmin\MapelController as SuperadminMapelController;
use App\Http\Controllers\Superadmin\UjianController as SuperadminUjianController;

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
use App\Http\Controllers\Admin\MapelController as AdminMapelController;
use App\Http\Controllers\Admin\TujuanPembelajaranController;
use App\Http\Controllers\Admin\AlurTujuanPembelajaranController;
use App\Http\Controllers\Admin\ModulAjarController;
use App\Http\Controllers\Admin\RuangSesiController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\SoalController as AdminSoalController;
use App\Http\Controllers\Admin\UjianController;

// Import Controller Guru
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\GuruSiswaController;
use App\Http\Controllers\Guru\JurnalController as GuruJurnalController;
use App\Http\Controllers\Guru\AbsensiController as GuruAbsensiController;
use App\Http\Controllers\Guru\SekolahController as GuruSekolahController;
use App\Http\Controllers\Guru\ProfilController as GuruProfilController;
use App\Http\Controllers\Guru\ModulAjarController as GuruModulAjarController;

// Import Controller Siswa
use App\Http\Controllers\Siswa\AuthController as SiswaAuthController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\UjianController as SiswaUjianController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

    if (in_array($role, ['admin', 'admin_sekolah', 'kepsek', 'proktor'], true)) {
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

    // CRUD Master Data oleh Superadmin
    Route::resource('sekolah', SuperadminSekolahController::class);
    Route::resource('users', UserController::class);
    Route::resource('kelas', SuperadminKelasController::class);
    Route::resource('mapel', SuperadminMapelController::class);

    // Rute Kustom Soal
    Route::get('/soal/import', [SuperadminSoalController::class, 'import'])->name('soal.import');
    Route::post('/soal/import', [SuperadminSoalController::class, 'processImport'])->name('soal.process-import');
    Route::get('/soal/paket', [SuperadminSoalController::class, 'paketIndex'])->name('soal.paket');
    Route::get('/soal/upload-gambar', fn() => redirect()->route('superadmin.soal.index'))->name('soal.upload-gambar');
    Route::post('/soal/upload-gambar', [SuperadminSoalController::class, 'processUploadGambar'])->name('soal.process-upload-gambar');
    Route::resource('soal', SuperadminSoalController::class);

    // Custom Route Ujian (Harus di atas Resource Ujian)
    Route::get('/ujian/generate-token', [SuperadminUjianController::class, 'generateToken'])->name('ujian.generate-token');
    Route::patch('/ujian/{ujian}/status', [SuperadminUjianController::class, 'updateStatus'])->name('ujian.update-status');
    Route::patch('/ujian/{id}/toggle-rilis-nilai', [SuperadminUjianController::class, 'toggleRilisNilai'])->name('ujian.toggle-rilis');

    // Rute Rekap & Cetak Rekap Nilai Ujian
    Route::get('/rekap-nilai/{id?}', [SuperadminUjianController::class, 'rekapNilai'])->name('ujian.rekap');
    Route::get('/cetak-rekap/{id?}', [SuperadminUjianController::class, 'cetakRekap'])->name('ujian.cetak-rekap');

    // CRUD Resource Pengaturan Ujian
    Route::resource('ujian', SuperadminUjianController::class);
});


// ==========================================
// GROUP ROUTE KHUSUS ADMIN SEKOLAH / KEPSEK
// ==========================================
Route::middleware(['auth', 'role:admin,admin_sekolah,kepsek,superadmin,guru,proktor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Rilis & Management Token Ujian
        Route::get('/token', [TokenController::class, 'index'])->name('token.index');
        Route::get('/token/fetch', [TokenController::class, 'fetchToken'])->name('token.fetch');
        Route::post('/token/generate', [TokenController::class, 'generate'])->name('token.generate');
        Route::post('/token/refresh', [TokenController::class, 'refreshToken'])->name('token.refresh');
        Route::get('/token/progress/{id}', [TokenController::class, 'progressUjian'])->name('token.progress');

        // Tahun Ajaran
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
        Route::put('/tahun-ajaran/{id}/set-aktif', [TahunAjaranController::class, 'setAktif'])->name('tahun-ajaran.set-aktif');

        // Validasi Jurnal oleh Kepala Sekolah / Admin
        Route::get('/validasi-jurnal', [AdminKepalaSekolahController::class, 'indexValidasiJurnal'])->name('kepala-sekolah.jurnal.index');
        Route::put('/validasi-jurnal/{id}', [AdminKepalaSekolahController::class, 'updateStatusJurnal'])->name('kepala-sekolah.jurnal.update');

        // Fitur Tambahan Guru
        Route::get('/guru/template', [AdminGuruController::class, 'downloadTemplate'])->name('guru.template');
        Route::post('/guru/import', [AdminGuruController::class, 'import'])->name('guru.import');
        Route::post('/guru/{guru}/reset-password', [AdminGuruController::class, 'resetPassword'])->name('guru.reset-password');
        Route::get('/guru/get-elemen-tp', [TujuanPembelajaranController::class, 'getElemenAndTp'])->name('guru.get_elemen_tp');

        // Siswa & Cetak Kartu Ujian
        Route::post('/siswa/import', [AdminSiswaController::class, 'import'])->name('siswa.import');
        Route::get('/siswa/cetak-kartu/{id?}', [AdminSiswaController::class, 'cetakKartu'])->name('siswa.cetak-kartu');
        Route::get('/siswa/cetak-kartu-kelas', [AdminSiswaController::class, 'cetakKartuKelas'])->name('siswa.cetak-kartu-kelas');
        Route::get('/siswa/print-preview', [AdminSiswaController::class, 'printPreview'])->name('siswa.print-preview');
        Route::get('/siswa/print-pure', [AdminSiswaController::class, 'printPreview'])->name('siswa.print-pure');
        Route::get('/siswa/admin.cetak-kartu', [AdminSiswaController::class, 'cetakKartu'])->name('admin.cetak-kartu');

        // Cetak Absensi Mapel
        Route::get('/absensi/cetak-mapel', [CetakAbsensiMapelController::class, 'index'])->name('absensi.cetak-mapel');

        // Route Resource Utama
        Route::resource('absensi', AdminAbsensiController::class);
        Route::resource('guru', AdminGuruController::class);
        Route::resource('kelas', AdminKelasController::class);
        Route::resource('sekolah', AdminSekolahController::class);
        Route::resource('siswa', AdminSiswaController::class);
        Route::resource('jurnal', AdminJurnalController::class);
        Route::resource('kepala-sekolah', AdminKepalaSekolahController::class);
        Route::resource('mapel', AdminMapelController::class);
        Route::resource('modul_ajar', ModulAjarController::class);

        // Route Kurikulum (CP, TP, ATP)
        Route::resource('capaian-pembelajaran', CapaianPembelajaranController::class)->except(['create', 'edit', 'show']);
        Route::get('/get-elemen-cp', [TujuanPembelajaranController::class, 'getElemenByMapelFase'])->name('get_elemen_cp');
        Route::resource('tujuan-pembelajaran', TujuanPembelajaranController::class)->except(['create', 'edit', 'show']);
        Route::resource('atp', AlurTujuanPembelajaranController::class)->except(['create', 'edit', 'show']);
        Route::get('/api/atp/by-tp', [AlurTujuanPembelajaranController::class, 'getByTp'])->name('api.atp.get_by_tp');

        // Route Ruang & Sesi Ujian
        Route::get('/sesi', [RuangSesiController::class, 'index'])->name('sesi.index');
        Route::post('/ruang/store', [RuangSesiController::class, 'storeRuang'])->name('ruang.store');
        Route::delete('/ruang/{id}', [RuangSesiController::class, 'destroyRuang'])->name('ruang.destroy');
        Route::post('/sesi/store', [RuangSesiController::class, 'storeSesi'])->name('sesi.store');
        Route::put('/sesi/{id}', [RuangSesiController::class, 'updateSesi'])->name('sesi.update');
        Route::delete('/sesi/{id}', [RuangSesiController::class, 'destroySesi'])->name('sesi.destroy');

        // Paket Soal Admin
        Route::get('/soal/paket', [AdminSoalController::class, 'paketIndex'])->name('soal.paket');

        // Rute Ujian, Monitoring Live & Hasil Ujian
        Route::get('/ujian', [UjianController::class, 'index'])->name('ujian.index');
        Route::get('/ujian/{id}/monitoring', [UjianController::class, 'monitoringPeserta'])->name('ujian.monitoring');
        Route::post('/ujian/peserta/{nilaiUjianId}/reset-login', [UjianController::class, 'resetLogin'])->name('ujian.reset-login');
        Route::post('/ujian/peserta/{nilaiUjianId}/paksa-selesai', [UjianController::class, 'paksaSelesai'])->name('ujian.paksa-selesai');
        Route::delete('/ujian/peserta/{nilaiUjianId}/reset-ujian', [UjianController::class, 'resetUjian'])->name('ujian.reset-ujian');

        Route::get('/hasil-ujian', [UjianController::class, 'rekapNilai'])->name('ujian.rekap');
        Route::get('/ujian/{id}/hasil', [UjianController::class, 'hasil'])->name('ujian.hasil');
        Route::get('/ujian/{id}/cetak-hasil', [UjianController::class, 'cetakHasil'])->name('ujian.cetak-hasil');
    });


// ==========================================
// GROUP ROUTE KHUSUS GURU
// ==========================================
Route::middleware(['auth', 'role:guru,superadmin'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [GuruProfilController::class, 'index'])->name('profil.index');
    Route::put('/profil', [GuruProfilController::class, 'update'])->name('profil.update');
    Route::get('/absensi', [GuruAbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi', [GuruAbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/absensi/rekap', [GuruAbsensiController::class, 'rekap'])->name('absensi.rekap');
    Route::get('/absensi/cetak', [GuruAbsensiController::class, 'cetakRekap'])->name('absensi.cetak');
    Route::get('/sekolah', [GuruSekolahController::class, 'index'])->name('sekolah.index');
    Route::resource('siswa', GuruSiswaController::class)->only(['index', 'show']);
    Route::get('/kelas', [GuruSiswaController::class, 'kelasIndex'])->name('kelas.index');
    Route::get('/kelas/{id}', [GuruSiswaController::class, 'kelasShow'])->name('kelas.show');
    Route::get('/get-cp-filtered', [GuruModulAjarController::class, 'getCpFiltered'])->name('get_cp_filtered');
    Route::get('/get-tp-by-cp/{cp_id}', [GuruModulAjarController::class, 'getTpByCp'])->name('get_tp_by_cp');
    Route::get('/get-tp-filtered', [GuruModulAjarController::class, 'getTpFiltered'])->name('get_tp_filtered');
    Route::get('/modul-ajar/{id}/print', [GuruModulAjarController::class, 'print'])->name('modul_ajar.print');
    Route::resource('modul_ajar', GuruModulAjarController::class);
    Route::get('/jurnal/cetak-pdf', [GuruJurnalController::class, 'cetakWord'])->name('jurnal.cetak');
    Route::resource('jurnal', GuruJurnalController::class);
});


// ==========================================
// GROUP ROUTE KHUSUS SISWA
// ==========================================
Route::prefix('siswa')->name('siswa.')->group(function () {
    Route::middleware('guest:siswa')->group(function () {
        Route::get('/login', [SiswaAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SiswaAuthController::class, 'login'])->name('login.post');
    });

    // Tambahkan 'single.siswa' di sini setelah alias didaftarkan di bootstrap/app.php
    Route::middleware(['auth:siswa', 'single.siswa'])->group(function () {
        Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
        Route::post('/konfirmasi', [SiswaDashboardController::class, 'konfirmasiUjian'])->name('konfirmasi');

        Route::post('/ujian/verifikasi', [SiswaUjianController::class, 'verifikasiToken'])->name('ujian.verifikasi');
        Route::get('/ujian/mulai', [SiswaUjianController::class, 'mulai'])->name('ujian.mulai');
        Route::post('/ujian/simpan-jawaban', [SiswaUjianController::class, 'simpanJawaban'])->name('ujian.simpan-jawaban');
        Route::post('/ujian/selesai', [SiswaUjianController::class, 'selesaiUjian'])->name('ujian.selesai');

        Route::post('/logout', [SiswaAuthController::class, 'logout'])->name('logout');
    });
});


// ==========================================
// PROFILE MANAGEMENT (USER STANDARD)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
