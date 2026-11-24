<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplateSoalPetunjukSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Petunjuk_Pengisian';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 22,
            'C' => 65,
            'D' => 35,
        ];
    }

    public function array(): array
    {
        return [
            ['PANDUAN PENGISIAN TEMPLATE IMPOR BANK SOAL (PG, MENJODOHKAN, & ESAI)', '', '', ''],
            ['', '', '', ''],
            ['Nama Kolom', 'Status Wajib', 'Keterangan & Aturan Penulisan', 'Contoh Pengisian'],
            ['nomor_soal', 'Opsional (Disarankan)', 'Nomor urut butir soal dalam satu paket. Dipakai untuk pencocokan saat upload gambar terpisah.', '1, 2, 3, ...'],
            ['kode_soal', 'Opsional (Disarankan)', 'Kode identitas paket soal. Gunakan kode yang sama untuk seluruh nomor dalam 1 paket mapel.', 'MTK-KLS6-UTS'],
            ['mapel', 'WAJIB DIISI', 'Tulis nama mata pelajaran (harus sesuai dengan data Master Mapel di aplikasi) atau ID Mapel.', 'Matematika'],
            ['kelas', 'WAJIB DIISI', 'Tulis nama kelas (harus sesuai dengan data Master Kelas di aplikasi) atau ID Kelas.', 'Kelas 6'],
            ['tipe_soal', 'WAJIB DIISI', 'Isi salah satu dari 3 kode tipe berikut: pg (Pilihan Ganda), menjodohkan, atau esai. Jika kosong dianggap pg.', 'pg / menjodohkan / esai'],
            ['soal', 'WAJIB DIISI', 'Tulis teks pertanyaan utama atau instruksi soal secara lengkap.', 'Hasil dari 15 x 4 adalah ...'],
            ['opsi_a s/d opsi_e', 'Wajib untuk PG & Menjodohkan', '• Jika tipe pg: Isi teks pilihan jawaban A sampai D (atau E).\n• Jika tipe menjodohkan: Isi pasangan dengan tanda pemisah garis tegak "|" (Kiri | Kanan), minimal 2 pasangan (opsi_a & opsi_b).\n• Jika tipe esai: Kosongkan seluruh kolom opsi.', 'PG: 80\nMenjodohkan: Segitiga | 3 Sisi\nEsai: (kosong)'],
            ['kunci_jawaban', 'Wajib untuk PG', '• Jika tipe pg: Isi huruf kunci jawaban (A, B, C, D, atau E).\n• Jika tipe menjodohkan: Kosongkan (kunci otomatis dibaca dari pasangan opsi_a..e).\n• Jika tipe esai: Isi pedoman/acuan jawaban untuk membantu guru mengoreksi (opsional).', 'PG: C\nMenjodohkan: (kosong)\nEsai: Rumus L = pi x r x r'],
            ['bobot', 'Opsional', 'Bobot skor per butir soal (angka desimal/bulat). Jika kosong otomatis bernilai 1.', '1 (untuk PG) atau 5 (untuk Esai)'],
            ['', '', '', ''],
            ['CATATAN PENTING TAMBAHAN:', '', '', ''],
            ['1. Satu Paket Campuran', 'Info', 'Anda boleh mencampur soal pg, menjodohkan, dan esai dalam satu file Excel yang sama.', 'No 1-20 pg, No 21-25 menjodohkan, No 26-30 esai'],
            ['2. Upload Gambar Soal', 'Info', 'Setelah Excel berhasil diimpor, gunakan tombol "Upload Gambar Soal" di halaman Bank Soal lalu pilih kode_soal dan nomor_soal yang sesuai.', 'Pilih Kode Paket -> Nomor Soal -> Upload JPG/PNG'],
            ['3. Jangan Ubah Header', 'Penting', 'Jangan mengubah atau menghapus baris ke-1 (header kolom) pada sheet "Template_Soal". Hapus 3 baris contoh sebelum mengisi soal asli.', 'Baris 1: nomor_soal, kode_soal, dst.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Judul Utama (Baris 1)
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Header Tabel Panduan (Baris 3)
        $sheet->getStyle('A3:D3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(24);

        // Border & Wrap Text Tabel Panduan (Baris 3 - 12)
        $sheet->getStyle('A3:D12')->applyFromArray([
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

        // Subheader Catatan Penting (Baris 14)
        $sheet->mergeCells('A14:D14');
        $sheet->getStyle('A14:D14')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
        ]);

        // Border Catatan Penting (Baris 14 - 17)
        $sheet->getStyle('A14:D17')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FDE68A'],
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
