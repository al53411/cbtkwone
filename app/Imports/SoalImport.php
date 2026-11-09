<?php

namespace App\Imports;

use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SoalImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Helper fleksibel untuk mengambil isi kolom (mengabaikan spasi & variasi header)
        $rawMapel = $row['mapel_id'] ?? $row['mapel'] ?? $row['kode_mapel'] ?? $row['mata_pelajaran'] ?? null;
        $rawKelas = $row['kelas_id'] ?? $row['kelas'] ?? $row['nama_kelas'] ?? $row['kode_kelas'] ?? null;
        $soalTeks  = $row['soal'] ?? $row['pertanyaan'] ?? $row['teks_soal'] ?? null;

        if (empty($rawMapel) || empty($rawKelas) || empty($soalTeks)) {
            return null; // Dilewati jika baris kosong / header tidak sesuai
        }

        // Pencarian Mapel (ID / Kode / Nama)
        $mapelId = null;
        if (is_numeric($rawMapel)) {
            $mapelId = (int) $rawMapel;
        } else {
            $mapel = Mapel::where('nama_mapel', 'like', "%{$rawMapel}%")
                ->orWhere('kode_mapel', 'like', "%{$rawMapel}%")
                ->first();
            $mapelId = $mapel ? $mapel->id : null;
        }

        // Pencarian Kelas (ID / Kode / Nama)
        $kelasId = null;
        if (is_numeric($rawKelas)) {
            $kelasId = (int) $rawKelas;
        } else {
            $kelas = Kelas::where('nama_kelas', 'like', "%{$rawKelas}%")->first();
            $kelasId = $kelas ? $kelas->id : null;
        }

        // Ambil Opsi & Kunci
        $optA = $row['option_a'] ?? $row['opsi_a'] ?? $row['jawaban_a'] ?? null;
        $optB = $row['option_b'] ?? $row['opsi_b'] ?? $row['jawaban_b'] ?? null;

        // Validasi kelengkapan syarat minimal
        if (!$mapelId || !$kelasId || empty($optA) || empty($optB)) {
            return null;
        }

        $jumlahOpsi = isset($row['jumlah_opsi']) && is_numeric($row['jumlah_opsi']) ? (int)$row['jumlah_opsi'] : 5;

        return new Soal([
            'mapel_id'      => $mapelId,
            'kelas_id'      => $kelasId,
            'kode_soal'     => 'SOAL-' . strtoupper(uniqid()),
            'nomor_soal'    => isset($row['nomor_soal']) && is_numeric($row['nomor_soal']) ? (int)$row['nomor_soal'] : null,
            'soal'          => $soalTeks,
            'jumlah_opsi'   => $jumlahOpsi,
            'option_a'      => $optA,
            'option_b'      => $optB,
            'option_c'      => $jumlahOpsi >= 3 ? ($row['option_c'] ?? $row['opsi_c'] ?? $row['jawaban_c'] ?? null) : null,
            'option_d'      => $jumlahOpsi >= 4 ? ($row['option_d'] ?? $row['opsi_d'] ?? $row['jawaban_d'] ?? null) : null,
            'option_e'      => $jumlahOpsi >= 5 ? ($row['option_e'] ?? $row['opsi_e'] ?? $row['jawaban_e'] ?? null) : null,
            'kunci_jawaban' => strtoupper($row['kunci_jawaban'] ?? $row['kunci'] ?? 'A'),
        ]);
    }
}
