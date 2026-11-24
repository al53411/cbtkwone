<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'soals';
    protected $guarded = ['id'];

    protected $casts = [
        'pasangan_menjodohkan' => 'array',
        'jumlah_opsi'          => 'integer',
        'nomor_soal'           => 'integer',
        'bobot_soal'           => 'float',
    ];

    // Relasi ke Mapel
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Relasi ke Ujian (jika butir soal terikat ke ujian_id tertentu)
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    // Helper normalisasi tipe soal ('pg', 'menjodohkan', atau 'esai')
    public function getTipeNormalized(): string
    {
        $tipe = strtolower(trim((string) ($this->tipe_soal ?? $this->tipe ?? 'pg')));
        if ($tipe === 'essay') {
            return 'esai';
        }
        return in_array($tipe, ['pg', 'menjodohkan', 'esai'], true) ? $tipe : 'pg';
    }

    // Helper mengambil daftar pasangan menjodohkan yang valid
    public function getPasanganList(): array
    {
        $data = $this->pasangan_menjodohkan;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($data) ? $data : [];
    }
}
