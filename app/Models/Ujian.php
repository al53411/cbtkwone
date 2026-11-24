<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'sekolah_id',
        'user_id',
        'nama_ujian',
        'kelas_id',
        'mapel_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi',
        'kkm',
        'token',
        'acak_soal',
        'acak_jawaban',
        'tampilkan_nilai',
        'tampilkan_soal',
        'tampil_pg',
        'tampil_esai',
        'tampil_menjodohkan',
        'bobot_pg',
        'bobot_esai',
        'bobot_menjodohkan',
        'status',
        'aturan_ujian',
        'file_berita_acara',
        'uploaded_ba_at',
    ];

    // Tanpa casting boolean agar aman untuk PDO PostgreSQL maupun MySQL/MariaDB
    protected $casts = [
        'waktu_mulai'       => 'datetime',
        'waktu_selesai'     => 'datetime',
        'uploaded_ba_at'    => 'datetime',
        'durasi'            => 'integer',
        'bobot_pg'          => 'integer',
        'bobot_esai'        => 'integer',
        'bobot_menjodohkan' => 'integer',
        'kkm'               => 'float',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias relasi guru agar kompatibel dengan pemanggilan $ujian->guru
    public function guru()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'ujian_id');
    }

    public function nilaiUjians()
    {
        return $this->hasMany(NilaiUjian::class, 'ujian_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Helper aman untuk mengecek status acak soal & jawaban lintas database
    public function isSoalAcak(): bool
    {
        return filter_var($this->acak_soal ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function isJawabanAcak(): bool
    {
        return filter_var($this->acak_jawaban ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    // Helper aman untuk mengecek status rilis nilai lintas database (MySQL / PostgreSQL)
    public function isNilaiDirilis(): bool
    {
        return filter_var($this->tampilkan_nilai ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    // Helper aman untuk mengecek izin lihat soal oleh Admin Sekolah
    public function isSoalDitampilkan(): bool
    {
        return filter_var($this->tampilkan_soal ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    // Helper aman untuk mengecek tipe soal yang aktif pada ujian ini
    public function isPgAktif(): bool
    {
        return $this->tampil_pg === null
            ? true
            : filter_var($this->tampil_pg, FILTER_VALIDATE_BOOLEAN);
    }

    public function isEsaiAktif(): bool
    {
        return filter_var($this->tampil_esai ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function isMenjodohkanAktif(): bool
    {
        return filter_var($this->tampil_menjodohkan ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    // Daftar tipe soal aktif dalam bentuk array untuk query whereIn('tipe_soal', ...)
    public function getTipeSoalAktif(): array
    {
        $tipe = [];

        if ($this->isPgAktif()) {
            $tipe[] = 'pg';
        }
        if ($this->isMenjodohkanAktif()) {
            $tipe[] = 'menjodohkan';
        }
        if ($this->isEsaiAktif()) {
            $tipe[] = 'esai';
        }

        $tipe = array_values(array_unique(array_map('strtolower', $tipe)));

        return empty($tipe) ? ['pg'] : $tipe;
    }
}