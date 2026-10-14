<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    use HasFactory;

    protected $table = 'sesis';

    protected $fillable = [
        'sekolah_id',
        'nama_sesi',
        'waktu_mulai',
        'waktu_selesai',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
}