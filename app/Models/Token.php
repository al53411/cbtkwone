<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'tokens';

    protected $fillable = [
        'sekolah_id',
        'token',
        'is_active',
    ];

    /**
     * Konversi tipe data otomatis (Casting)
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke model Sekolah (opsional jika menggunakan multi-sekolah)
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }
}