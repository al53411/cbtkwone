<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulAjar extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'modul_ajars';

    /**
     * Kolom yang dilindungi dari mass assignment.
     */
    protected $guarded = ['id'];

    /**
     * Casting tipe data kolom.
     */
    protected $casts = [
        'profil_pelajar_pancasila' => 'array',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    // ==========================================
    // RELASI ELOQUENT
    // ==========================================

    /**
     * Relasi ke Sekolah
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id')->withDefault([
            'nama_sekolah' => '-',
        ]);
    }

    /**
     * Relasi ke User / Guru (Pembuat Modul)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Tanpa Nama',
        ]);
    }

    /**
     * Relasi ke Kelas (TAMBAHAN BARU)
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id')->withDefault([
            'nama_kelas' => '-',
        ]);
    }

    /**
     * Relasi ke Alur Tujuan Pembelajaran (ATP)
     */
    public function alurTujuanPembelajaran()
    {
        return $this->belongsTo(AlurTujuanPembelajaran::class, 'atp_id')->withDefault([
            'judul' => '-',
        ]);
    }

    /**
     * Relasi ke Mata Pelajaran (Mapel)
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id')->withDefault([
            'nama_mapel' => '-',
        ]);
    }

    /**
     * Relasi ke Capaian Pembelajaran (CP)
     */
    public function capaianPembelajaran()
    {
        return $this->belongsTo(CapaianPembelajaran::class, 'cp_id')->withDefault([
            'deskripsi_cp' => '-',
            'deskripsi'    => '-',
            'elemen'       => '-',
        ]);
    }

    /**
     * Accessor untuk mendapatkan teks CP dengan aman tanpa error properti
     */
    public function getDeskripsiCpAttribute()
    {
        if ($this->capaianPembelajaran) {
            return $this->capaianPembelajaran->deskripsi_cp 
                ?? $this->capaianPembelajaran->deskripsi 
                ?? $this->capaianPembelajaran->elemen 
                ?? '-';
        }
        return $this->attributes['capaian_pembelajaran'] ?? '-';
    }

    /**
     * Relasi ke Tujuan Pembelajaran (TP)
     */
    public function tujuanPembelajarans()
    {
        return $this->hasMany(TujuanPembelajaran::class, 'modul_ajar_id');
    }

    /**
     * Relasi ke Langkah Pembelajaran
     */
    public function langkahPembelajarans()
    {
        return $this->hasMany(LangkahPembelajaran::class, 'modul_ajar_id')->orderBy('pertemuan_ke', 'asc');
    }

    /**
     * Relasi ke Asesmen / Penilaian
     */
    public function asesmens()
    {
        return $this->hasMany(Asesmen::class, 'modul_ajar_id');
    }

    /**
     * Relasi ke Lampiran Modul
     */
    public function lampirans()
    {
        return $this->hasMany(ModulLampiran::class, 'modul_ajar_id');
    }
}