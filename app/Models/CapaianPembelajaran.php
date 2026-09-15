<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapaianPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'capaian_pembelajarans';

    protected $fillable = [
        'mapel_id',
        'fase',
        'elemen',
        'deskripsi_cp',
    ];

    /**
     * Relasi ke model Mapel (Many-to-One)
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    /**
     * Relasi ke model ModulAjar (One-to-Many)
     */
    public function modulAjars()
    {
        return $this->hasMany(ModulAjar::class, 'cp_id');
    }

    /**
     * Relasi ke model TujuanPembelajaran (One-to-Many)
     * Ditambahkan untuk mengatasi RelationNotFoundException
     */
    public function tujuanPembelajarans()
    {
        return $this->hasMany(TujuanPembelajaran::class, 'cp_id');
    }
}