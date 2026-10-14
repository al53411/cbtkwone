<?php

namespace App\Services;

use App\Models\Ujian;

class TokenService
{
    /**
     * Mengambil data token aktif berdasarkan sekolah_id
     */
    public static function getTokenData($sekolahId)
    {
        // Cari ujian yang sedang AKTIF sesuai dengan sekolah_id siswa
        $ujianAktif = Ujian::where('status', 'aktif')
            ->whereNotNull('token')
            ->when($sekolahId, function ($query) use ($sekolahId) {
                return $query->where('sekolah_id', $sekolahId);
            })
            ->latest('updated_at')
            ->first();

        return [
            'token' => $ujianAktif->token ?? null,
            'nama_ujian' => $ujianAktif->nama_ujian ?? null,
        ];
    }
}