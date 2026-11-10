<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiUjian extends Model
{
    use HasFactory;

    protected $table = 'nilai_ujians'; // Sesuaikan dengan nama tabel rekap nilai di database Anda

    protected $fillable = [
        'siswa_id',
        'ujian_id',
        'jumlah_benar',
        'jumlah_salah',
        'tidak_dijawab',
        'nilai',
        'waktu_selesai',
        'status',
    ];

    protected $casts = [
        'waktu_selesai' => 'datetime',
        'nilai' => 'float',
    ];

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi ke Ujian
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }
}
