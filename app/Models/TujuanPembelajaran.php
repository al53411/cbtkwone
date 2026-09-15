<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TujuanPembelajaran extends Model
{
    use HasFactory;

    /**
     * Nama tabel (opsional, Laravel otomatis mendeteksi 'tujuan_pembelajarans')
     */
    protected $table = 'tujuan_pembelajarans';

    /**
     * Kolom yang tidak boleh diisi secara mass-assignment
     */
    protected $guarded = ['id'];

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
     * Relasi ke Model Sekolah (Opsional, jika ada model Sekolah)
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }
}