<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'sekolah_id',
        'tahun',
        'semester',
        'is_aktif',
    ];

    /**
     * Cast atribut ke tipe data murni.
     * $casts ini akan otomatis mengubah angka 0/1 dari database menjadi true/false di Eloquent.
     */
    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Helper untuk mengambil data Tahun Ajaran yang aktif.
     */
    public static function getAktif($sekolahId = null)
    {
        // where('is_aktif', true) sekarang aman 100% di PostgreSQL & MySQL
        // karena atribut $casts di atas sudah mendaftarkan tipe 'boolean'.
        $query = static::where('is_aktif', true);

        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }

        return $query->first();
    }
}