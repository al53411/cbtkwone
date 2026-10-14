<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Siswa extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'siswas';

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sekolah_id',
        'user_id',
        'nisn',
        'nama_siswa',
        'password',
        'kelas_id',
        'kelas',
        'nama_kelas',
        'ruang',
        'sesi',
        'jenis_kelamin',
        'alamat',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi (JSON/Array)
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke model Sekolah
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi ke model Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke model User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}