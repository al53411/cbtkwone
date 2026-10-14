<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    /**
     * Nama tabel (opsional, eksplisit)
     */
    protected $table = 'ujians';

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment)
     */
    protected $fillable = [
        'user_id',       // Tambahkan user_id sesuai kolom database HeidiSQL
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

    /**
     * Casting tipe data kolom otomatis
     */
    protected $casts = [
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi'        => 'integer',
        'acak_soal'     => 'boolean',
        'acak_jawaban'  => 'boolean',
    ];

    /**
     * Relasi ke Model Mapel
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    /**
     * Relasi ke Model Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Model User (Pembuat / Pembuat Ujian)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope Query untuk mengambil Ujian yang berstatus 'aktif'
     * Penggunaan: Ujian::aktif()->get();
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}