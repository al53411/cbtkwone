<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
    ];

    /**
     * Relasi ke data siswa di kelas ini
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}