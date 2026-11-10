<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_siswas'; // Sesuaikan dengan nama tabel di database Anda

    protected $fillable = [
        'siswa_id',
        'ujian_id',
        'soal_id',
        'jawaban',
        'is_benar',
    ];

    // Relasi ke Siswa (opsional, jika diperlukan)
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi ke Ujian
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    // Relasi ke Soal
    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}
