<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public static function getAktif($sekolahId = null)
    {
        // Paksa query PostgreSQL menggunakan sintaks boolean murni 'true'
        $query = static::whereRaw("is_aktif IS TRUE");

        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }

        return $query->first();
    }
}