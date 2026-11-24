<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplateSoalDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Template_Soal';
    }

    public function headings(): array
    {
        return [
            'nomor_soal',
            'kode_soal',
            'mapel',
            'kelas',
            'tipe_soal',
            'soal',
            'opsi_a',
            'opsi_b',
            'opsi_c',
            'opsi_d',
            'opsi_e',
            'kunci_jawaban',
            'bobot',
        ];
    }

    public function array(): array
    {
        return [
            // Contoh 1: Pilihan Ganda (PG)
            [
                1,
                'MTK-KLS6-UTS',
                'Matematika',
                'Kelas 6',
                'pg',
                'Hasil dari 15 x 4 + 20 adalah ...',
                '60',
                '70',
                '80',
                '90',
                '100',
                'C',
                1,
            ],
            // Contoh 2: Menjodohkan (Gunakan pemisah tanda | antara Premis Kiri dan Jawaban Kanan)
            [
                2,
                'MTK-KLS6-UTS',
                'Matematika',
                'Kelas 6',
                'menjodohkan',
                'Pasangkan bangun datar di sebelah kiri dengan jumlah sisinya di sebelah kanan!',
                'Segitiga | 3 Sisi',
                'Persegi | 4 Sisi',
                'Segilima | 5 Sisi',
                'Segienam | 6 Sisi',
                '',
                '',
                2,
            ],
            // Contoh 3: Esai / Uraian
            [
                3,
                'MTK-KLS6-UTS',
                'Matematika',
                'Kelas 6',
                'esai',
                'Jelaskan langkah-langkah menghitung luas lingkaran jika diketahui diameternya 14 cm!',
                '',
                '',
                '',
                '',
                '',
                '1) Cari jari-jari r = d/2 = 7 cm. 2) Hitung Luas = 22/7 x 7 x 7 = 154 cm2.',
                5,
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header (Baris 1)
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'], // Emerald 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Warna pembeda lembut pada 3 baris contoh
        $sheet->getStyle('A2:M2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF'); // PG (Biru lembut)
        $sheet->getStyle('A3:M3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEF2FF'); // Menjodohkan (Indigo lembut)
        $sheet->getStyle('A4:M4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB'); // Esai (Kuning lembut)

        // Border seluruh tabel contoh + area siap isi sampai baris 50
        $sheet->getStyle('A1:M50')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        return [];
    }
}
