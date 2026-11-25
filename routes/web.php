<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import Controller SuperAdmin / Admin Pusat
use App\Http\Controllers\SuperAdmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\SuperAdmin\SekolahController as SuperadminSekolahController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\KepsekController;
use App\Http\Controllers\SuperAdmin\UjianController as SuperadminUjianController;
use App\Http\Controllers\SuperAdmin\SoalController as SuperadminSoalController;
use App\Http\Controllers\SuperAdmin\KelasController as SuperadminKelasController;
use App\Http\Controllers\SuperAdmin\MapelController as SuperadminMapelController;
use App\Http\Controllers\SuperAdmin\JadwalController as SuperadminJadwalController;

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
use App\Http\Controllers\Admin\MapelController as AdminMapelController;
use App\Http\Controllers\Admin\RuangSesiController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\SoalController as AdminSoalController;
use App\Http\Controllers\Admin\UjianController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;
use App\Http\Controllers\Admin\KorektorController as AdminKorektorController;

// Import Controller Korektor
use App\Http\Controllers\Korektor\DashboardController as KorektorDashboardController;
use App\Http\Controllers\Korektor\KoreksiController;

// Import Controller Siswa
use App\Http\Controllers\Siswa\AuthController as SiswaAuthController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\UjianController as SiswaUjianController;

// Redirect Halaman Utama langsung ke Login Admin/Staff
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

    // Deteksi role korektor maupun guru dengan jenis_guru = 'korektor'
    $isKorektor = ($role === 'korektor')
        || \App\Models\Guru::where('user_id', $user->id)
        ->where('jenis_guru', 'korektor')
        ->exists();

    if ($isKorektor) {
        return redirect()->route('korektor.dashboard');
    }

    if ($role === 'guru') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login')->withErrors(['login' => 'Role pengguna tidak terdaftar dengan benar.']);
})->middleware(['auth'])->name('dashboard');


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

    // Rute Kustom Soal (Wajib di atas Route::resource('soal', ...))
    Route::get('/soal/download-template', [SuperadminSoalController::class, 'downloadTemplate'])->name('soal.download-template');
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
    Route::patch('/ujian/{id}/toggle-tampil-soal', [SuperadminUjianController::class, 'toggleTampilSoal'])->name('ujian.toggle-tampil-soal');

    // Pengaman jika ada form PUT tanpa ID ujian
    Route::put('/ujian', fn() => redirect()->route('superadmin.ujian.index')
        ->with('error', 'ID sesi ujian tidak ditemukan. Pilih sesi ujian lalu klik tombol Settings untuk mengubah pengaturan.'));

    // Rute Rekap & Cetak Rekap Nilai Ujian
    Route::get('/rekap-nilai/{id?}', [SuperadminUjianController::class, 'rekapNilai'])->name('ujian.rekap');
    Route::get('/cetak-rekap/{id?}', [SuperadminUjianController::class, 'cetakRekap'])->name('ujian.cetak-rekap');

    // CRUD Resource Pengaturan Ujian
    Route::resource('ujian', SuperadminUjianController::class);

    // Jadwal Ujian & Monitoring Berita Acara Superadmin
    Route::get('/jadwal-ujian', [SuperadminJadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/berita-acara', [SuperadminUjianController::class, 'superadminBeritaAcara'])->name('berita-acara.index');
});


// ==========================================
// GROUP ROUTE KHUSUS ADMIN SEKOLAH / KEPSEK / PROKTOR
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

        // Fitur Tambahan Guru (Wajib di atas Route::resource('guru', ...))
        Route::get('/guru/template', [AdminGuruController::class, 'downloadTemplate'])->name('guru.template');
        Route::post('/guru/import', [AdminGuruController::class, 'import'])->name('guru.import');
        Route::post('/guru/{guru}/reset-password', [AdminGuruController::class, 'resetPassword'])->name('guru.reset-password');

        // Siswa & Cetak Kartu Ujian
        Route::post('/siswa/import', [AdminSiswaController::class, 'import'])->name('siswa.import');
        Route::get('/siswa/cetak-kartu/{id?}', [AdminSiswaController::class, 'cetakKartu'])->name('siswa.cetak-kartu');
        Route::get('/siswa/cetak-kartu-kelas', [AdminSiswaController::class, 'cetakKartuKelas'])->name('siswa.cetak-kartu-kelas');
        Route::get('/siswa/print-preview', [AdminSiswaController::class, 'printPreview'])->name('siswa.print-preview');
        Route::get('/siswa/print-pure', [AdminSiswaController::class, 'printPreview'])->name('siswa.print-pure');

        // Cetak Absensi Mapel (Wajib di atas Route::resource('absensi', ...))
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
        Route::resource('korektor', AdminKorektorController::class);

        // Route Ruang & Sesi Ujian
        Route::get('/sesi', [RuangSesiController::class, 'index'])->name('sesi.index');
        Route::post('/ruang/store', [RuangSesiController::class, 'storeRuang'])->name('ruang.store');
        Route::delete('/ruang/{id}', [RuangSesiController::class, 'destroyRuang'])->name('ruang.destroy');
        Route::post('/sesi/store', [RuangSesiController::class, 'storeSesi'])->name('sesi.store');
        Route::put('/sesi/{id}', [RuangSesiController::class, 'updateSesi'])->name('sesi.update');
        Route::delete('/sesi/{id}', [RuangSesiController::class, 'destroySesi'])->name('sesi.destroy');

        // Paket Soal Admin
        Route::get('/soal/paket', [AdminSoalController::class, 'paketIndex'])->name('soal.paket');

        // Rute Ujian, Monitoring Live, Hasil Ujian & Berita Acara
        Route::get('/ujian', [UjianController::class, 'index'])->name('ujian.index');
        Route::get('/ujian/{id}/monitoring', [UjianController::class, 'monitoringPeserta'])->name('ujian.monitoring');
        Route::post('/ujian/peserta/{nilaiUjianId}/reset-login', [UjianController::class, 'resetLogin'])->name('ujian.reset-login');
        Route::post('/ujian/peserta/{nilaiUjianId}/paksa-selesai', [UjianController::class, 'paksaSelesai'])->name('ujian.paksa-selesai');
        Route::delete('/ujian/peserta/{nilaiUjianId}/reset-ujian', [UjianController::class, 'resetUjian'])->name('ujian.reset-ujian');

        Route::get('/hasil-ujian', [UjianController::class, 'rekapNilai'])->name('ujian.rekap');
        Route::get('/ujian/{id}/hasil', [UjianController::class, 'hasil'])->name('ujian.hasil');
        Route::get('/ujian/{id}/cetak-hasil', [UjianController::class, 'cetakHasil'])->name('ujian.cetak-hasil');

        // Berita Acara Ujian CAT (Menu Sidebar & Cetak PDF)
        Route::get('/berita-acara-ujian', [UjianController::class, 'indexBeritaAcara'])->name('ujian.berita-acara-index');
        Route::get('/ujian/{id}/berita-acara', [UjianController::class, 'cetakBeritaAcara'])->name('ujian.berita-acara');
        Route::post('/ujian/{id}/upload-berita-acara', [UjianController::class, 'uploadBeritaAcara'])->name('ujian.upload-berita-acara');
        Route::delete('/ujian/{id}/hapus-berita-acara', [UjianController::class, 'hapusBeritaAcara'])->name('ujian.hapus-berita-acara');

        // Route untuk memproses ganti sandi bawaan dari popup login pertama
        Route::post('/ganti-sandi-awal', [AdminProfilController::class, 'updateSandiAwal'])
            ->name('ganti-sandi-awal');
    });


// ==========================================
// GROUP ROUTE KHUSUS KOREKTOR
// ==========================================
Route::middleware(['auth', 'role:korektor,guru,superadmin'])
    ->prefix('korektor')
    ->name('korektor.')
    ->group(function () {
        Route::get('/dashboard', [KorektorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/koreksi', [KoreksiController::class, 'index'])->name('koreksi.index');
        Route::get('/koreksi/{ujian}', [KoreksiController::class, 'show'])->name('koreksi.show');
        Route::post('/koreksi/{jawaban}', [KoreksiController::class, 'simpanSkor'])->name('koreksi.simpan');
        Route::get('/nilai', [KoreksiController::class, 'rekapNilai'])->name('nilai.index');
        Route::get('/nilai/{ujian}/cetak', [KoreksiController::class, 'cetakNilai'])->name('nilai.cetak');
        Route::get('/soal', [KoreksiController::class, 'pedomanSoal'])->name('soal.index');
    });


// ==========================================
// GROUP ROUTE KHUSUS SISWA
// ==========================================
Route::prefix('siswa')->name('siswa.')->group(function () {
    // Bebas dari middleware guest/auth agar /siswa/login tidak pernah ter-redirect ke /login admin
    Route::get('/login', [SiswaAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SiswaAuthController::class, 'login'])->name('login.post');

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
