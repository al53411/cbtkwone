<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;

    protected $table = 'ruangs';

    protected $fillable = [
        'sekolah_id',
        'kode_ruang',
        'nama_ruang',
        'kapasitas',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
}