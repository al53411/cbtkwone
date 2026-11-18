<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolahs';

    protected $fillable = [
        'npsn',
        'nama_sekolah',
        'jenjang',
        'status',
        'alamat',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'kode_pos',
        'email',
        'telepon',
        'nama_kepsek',
        'nip_kepsek',
        'nama_proktor',
        'nip_proktor',
        'nama_pengawas',
        'nip_pengawas',
        'logo',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'sekolah_id');
    }

    public function gurus()
    {
        return $this->hasMany(Guru::class, 'sekolah_id');
    }

    public function kelases()
    {
        return $this->hasMany(Kelas::class, 'sekolah_id');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'sekolah_id');
    }

    public function jurnals()
    {
        return $this->hasManyThrough(
            JurnalGuru::class,
            User::class,
            'sekolah_id',
            'user_id',
            'id',
            'id'
        );
    }
}
