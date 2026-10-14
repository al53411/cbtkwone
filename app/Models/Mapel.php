<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapels';

    protected $fillable = [
        'sekolah_id',
        'kode_mapel',
        'nama_mapel',
    ];

    /**
     * Relasi ke Sekolah
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi Many-to-Many ke Kelas melalui tabel pivot kelas_mapel
     */
    public function kelases()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mapel', 'mapel_id', 'kelas_id')
                    ->withTimestamps();
    }

    /**
     * Relasi ke Soal (TAMBAHKAN INI)
     */
    public function soals()
    {
        return $this->hasMany(Soal::class, 'mapel_id');
    }
}