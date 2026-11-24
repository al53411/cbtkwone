<?php

namespace App\Imports;

use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SoalImport implements WithMultipleSheets, ToCollection, WithHeadingRow
{
    private string $batchKodeSoal;
    public int $importedCount = 0;
    public array $skippedRows = [];

    public function __construct(?string $kodeSoal = null)
    {
        $this->batchKodeSoal = $kodeSoal ?: ('SOAL-' . strtoupper(uniqid()));
    }

    /**
     * Hanya proses Sheet ke-1 (Template_Soal), abaikan Sheet Petunjuk_Pengisian
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function collection(Collection $rows)
    {
        $allMapels = Mapel::all();
        $allKelass = Kelas::all();

        foreach ($rows as $index => $row) {
            $barisExcel = $index + 2; // Baris ke-1 adalah header

            $rawMapel = trim((string) ($row['mapel_id'] ?? $row['mapel'] ?? $row['kode_mapel'] ?? $row['mata_pelajaran'] ?? ''));
            $rawKelas = trim((string) ($row['kelas_id'] ?? $row['kelas'] ?? $row['nama_kelas'] ?? $row['kode_kelas'] ?? ''));
            $soalTeks = trim((string) ($row['soal'] ?? $row['pertanyaan'] ?? $row['teks_soal'] ?? ''));

            // Lewati baris yang benar-benar kosong
            if ($rawMapel === '' && $rawKelas === '' && $soalTeks === '') {
                continue;
            }

            if ($rawMapel === '' || $rawKelas === '' || $soalTeks === '') {
                $this->skippedRows[] = "Baris {$barisExcel}: Kolom mapel, kelas, atau soal kosong.";
                continue;
            }

            // 1. Pencocokan Mapel Fleksibel (ID, Kode, Nama Exact, atau Substring Dua Arah)
            $mapelId = $this->resolveMapelId($rawMapel, $allMapels);
            if (!$mapelId) {
                $this->skippedRows[] = "Baris {$barisExcel}: Mapel '{$rawMapel}' tidak ditemukan di Master Mapel.";
                continue;
            }

            // 2. Pencocokan Kelas Fleksibel (ID, Angka '5' <-> 'Kelas 5', atau Nama)
            $kelasId = $this->resolveKelasId($rawKelas, $allKelass);
            if (!$kelasId) {
                $this->skippedRows[] = "Baris {$barisExcel}: Kelas '{$rawKelas}' tidak ditemukan di Master Kelas.";
                continue;
            }

            // 3. Deteksi Tipe Soal
            $rawTipe  = strtolower(trim((string) ($row['tipe_soal'] ?? $row['tipe'] ?? $row['jenis_soal'] ?? 'pg')));
            $tipeSoal = match ($rawTipe) {
                'esai', 'essay', 'uraian'          => 'esai',
                'menjodohkan', 'jodohkan', 'match' => 'menjodohkan',
                default                            => 'pg',
            };

            $kodePaket = !empty($row['kode_soal'])
                ? strtoupper(trim((string) $row['kode_soal']))
                : $this->batchKodeSoal;

            $nomorSoal = isset($row['nomor_soal']) && is_numeric($row['nomor_soal'])
                ? (int) $row['nomor_soal']
                : (isset($row['no']) && is_numeric($row['no']) ? (int) $row['no'] : null);

            $bobotSoal = isset($row['bobot']) && is_numeric($row['bobot'])
                ? (float) $row['bobot']
                : (isset($row['bobot_soal']) && is_numeric($row['bobot_soal']) ? (float) $row['bobot_soal'] : 1.0);

            $data = [
                'mapel_id'   => $mapelId,
                'kelas_id'   => $kelasId,
                'kode_soal'  => $kodePaket,
                'nomor_soal' => $nomorSoal,
                'soal'       => $soalTeks,
                'tipe_soal'  => $tipeSoal,
                'bobot_soal' => $bobotSoal,
            ];

            if (Schema::hasColumn('soals', 'tipe')) {
                $data['tipe'] = $tipeSoal;
            }

            $opsiList = [
                $row['option_a'] ?? $row['opsi_a'] ?? $row['jawaban_a'] ?? null,
                $row['option_b'] ?? $row['opsi_b'] ?? $row['jawaban_b'] ?? null,
                $row['option_c'] ?? $row['opsi_c'] ?? $row['jawaban_c'] ?? null,
                $row['option_d'] ?? $row['opsi_d'] ?? $row['jawaban_d'] ?? null,
                $row['option_e'] ?? $row['opsi_e'] ?? $row['jawaban_e'] ?? null,
            ];

            if ($tipeSoal === 'menjodohkan') {
                $pasangan = [];
                foreach ($opsiList as $itemOpsi) {
                    $teks = trim((string) ($itemOpsi ?? ''));
                    if ($teks !== '' && str_contains($teks, '|')) {
                        [$kiri, $kanan] = array_pad(explode('|', $teks, 2), 2, '');
                        $kiri  = trim($kiri);
                        $kanan = trim($kanan);
                        if ($kiri !== '' && $kanan !== '') {
                            $pasangan[] = [
                                'kiri'  => $kiri,
                                'kanan' => $kanan,
                            ];
                        }
                    }
                }

                if (count($pasangan) < 2) {
                    $this->skippedRows[] = "Baris {$barisExcel}: Soal menjodohkan minimal butuh 2 pasangan dengan pemisah '|'.";
                    continue;
                }

                $data['pasangan_menjodohkan'] = json_encode($pasangan, JSON_UNESCAPED_UNICODE);
                $data['jumlah_opsi']          = count($pasangan);
                $data['kunci_jawaban']        = null;
            } elseif ($tipeSoal === 'esai') {
                $pedomanKunci = $row['kunci_jawaban'] ?? $row['kunci'] ?? $row['pedoman'] ?? null;

                $data['jumlah_opsi']          = 0;
                $data['pasangan_menjodohkan'] = null;
                $data['kunci_jawaban']        = !empty($pedomanKunci) ? trim((string) $pedomanKunci) : null;
            } else {
                // Pilihan Ganda
                $optA = trim((string) ($opsiList[0] ?? ''));
                $optB = trim((string) ($opsiList[1] ?? ''));

                if ($optA === '' || $optB === '') {
                    $this->skippedRows[] = "Baris {$barisExcel}: Soal PG wajib memiliki minimal opsi_a dan opsi_b.";
                    continue;
                }

                $jumlahOpsi = isset($row['jumlah_opsi']) && is_numeric($row['jumlah_opsi'])
                    ? (int) $row['jumlah_opsi']
                    : (!empty($opsiList[4]) ? 5 : (!empty($opsiList[3]) ? 4 : 3));

                $data['jumlah_opsi']          = $jumlahOpsi;
                $data['option_a']             = $optA;
                $data['option_b']             = $optB;
                $data['option_c']             = $jumlahOpsi >= 3 && !empty($opsiList[2]) ? trim((string) $opsiList[2]) : null;
                $data['option_d']             = $jumlahOpsi >= 4 && !empty($opsiList[3]) ? trim((string) $opsiList[3]) : null;
                $data['option_e']             = $jumlahOpsi >= 5 && !empty($opsiList[4]) ? trim((string) $opsiList[4]) : null;
                $data['pasangan_menjodohkan'] = null;
                $data['kunci_jawaban']        = strtoupper(trim((string) ($row['kunci_jawaban'] ?? $row['kunci'] ?? 'A')));
            }

            Soal::create($data);
            $this->importedCount++;
        }
    }

    private function resolveMapelId(string $raw, Collection $mapels): ?int
    {
        if (is_numeric($raw)) {
            $found = $mapels->firstWhere('id', (int) $raw);
            return $found?->id;
        }

        $needle = mb_strtolower($raw);

        // 1. Cocok persis nama_mapel atau kode_mapel
        foreach ($mapels as $m) {
            $nama = mb_strtolower(trim((string) ($m->nama_mapel ?? '')));
            $kode = mb_strtolower(trim((string) ($m->kode_mapel ?? '')));
            if ($nama === $needle || ($kode !== '' && $kode === $needle)) {
                return $m->id;
            }
        }

        // 2. Cocok sebagian dua arah (misal "PAI" <-> "Pendidikan Agama Islam")
        foreach ($mapels as $m) {
            $nama = mb_strtolower(trim((string) ($m->nama_mapel ?? '')));
            if ($nama !== '' && (str_contains($nama, $needle) || str_contains($needle, $nama))) {
                return $m->id;
            }
        }

        return null;
    }

    private function resolveKelasId(string $raw, Collection $kelass): ?int
    {
        if (is_numeric($raw)) {
            // Cek ID dulu, jika tidak ada cek nama kelas yang mengandung angka tsb (misal "5" -> "Kelas 5")
            $byId = $kelass->firstWhere('id', (int) $raw);
            if ($byId) {
                return $byId->id;
            }
        }

        $needle      = mb_strtolower($raw);
        $needleAngka = preg_replace('/[^0-9]/', '', $needle);

        foreach ($kelass as $k) {
            $nama = mb_strtolower(trim((string) ($k->nama_kelas ?? '')));
            if ($nama === $needle || ($nama !== '' && (str_contains($nama, $needle) || str_contains($needle, $nama)))) {
                return $k->id;
            }
        }

        // Cocokkan berdasarkan angka tingkat (misal Excel "Kelas 5" <-> DB "5" atau "V")
        if ($needleAngka !== '') {
            foreach ($kelass as $k) {
                $namaAngka = preg_replace('/[^0-9]/', '', (string) ($k->nama_kelas ?? ''));
                if ($namaAngka !== '' && $namaAngka === $needleAngka) {
                    return $k->id;
                }
            }
        }

        return null;
    }
}
