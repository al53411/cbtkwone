<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'user_id',
        'nama_ujian',
        'kelas_id',
        'mapel_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi',
        'token',
        'acak_soal',
        'acak_jawaban',
        'status',
    ];

    // Hapus casting boolean di sini agar tidak memaksa konversi PDO PostgreSQL
    protected $casts = [
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi'        => 'integer',
    ];

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

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}