<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Nilai - {{ $ujian->nama_ujian }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="bg-white text-slate-900 p-6 sm:p-10 font-sans">

    <!-- Tombol Aksi (Hilang saat dicetak) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex justify-end gap-2">
        <button onclick="window.close()"
            class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold rounded-lg">
            Tutup
        </button>
        <button onclick="window.print()"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Kop Laporan -->
        <div class="flex items-center gap-4 border-b-2 border-slate-800 pb-4 mb-6">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="w-14 h-14 object-contain">
            <div>
                <h1 class="text-lg font-bold uppercase tracking-wide">Laporan Hasil Ujian Computer Assisted Test (CAT)
                </h1>
                <p class="text-xs text-slate-600">Rekapitulasi Nilai Peserta Ujian</p>
            </div>
        </div>

        <!-- Informasi Ujian -->
        <table class="w-full text-xs mb-6">
            <tr>
                <td class="py-1 w-36 font-semibold">Nama Ujian</td>
                <td class="py-1">: {{ $ujian->nama_ujian }}</td>
                <td class="py-1 w-32 font-semibold">Tanggal Cetak</td>
                <td class="py-1">: {{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Mata Pelajaran</td>
                <td class="py-1">: {{ $ujian->mapel->nama_mapel ?? '-' }}</td>
                <td class="py-1 font-semibold">Token Sesi</td>
                <td class="py-1 font-mono">: {{ $ujian->token }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Tingkat / Kelas</td>
                <td class="py-1">: {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}</td>
                <td class="py-1 font-semibold">Jumlah Peserta</td>
                <td class="py-1">: {{ $hasilUjians->count() }} Siswa</td>
            </tr>
        </table>

        <!-- Tabel Nilai -->
        <table class="w-full border-collapse border border-slate-400 text-xs">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-400 py-2 px-3 text-center w-10">No</th>
                    <th class="border border-slate-400 py-2 px-3 text-left w-32">NISN</th>
                    <th class="border border-slate-400 py-2 px-3 text-left">Nama Peserta</th>
                    <th class="border border-slate-400 py-2 px-3 text-center w-24">Kelas</th>
                    <th class="border border-slate-400 py-2 px-3 text-center w-16">Benar</th>
                    <th class="border border-slate-400 py-2 px-3 text-center w-16">Salah</th>
                    <th class="border border-slate-400 py-2 px-3 text-center w-20">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hasilUjians as $index => $hasil)
                    <tr>
                        <td class="border border-slate-400 py-1.5 px-3 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-400 py-1.5 px-3 font-mono">
                            {{ $hasil->nama_siswa ?? $siswa->nama_lengkap ?? '-' }}</td>
                        <td class="border border-slate-400 py-1.5 px-3 font-medium">
                            {{ $hasil->nama_siswa ?? $siswa->nama_lengkap ?? '-' }}</td>
                        <td class="border border-slate-400 py-1.5 px-3 text-center">
                            {{ $hasil->nama_siswa ?? $siswa->nama_lengkap ?? '-' }}
                        </td>
                        <td class="border border-slate-400 py-1.5 px-3 text-center">
                            {{ $hasil->jumlah_benar ?? $hasil->benar ?? 0 }}
                        </td>
                        <td class="border border-slate-400 py-1.5 px-3 text-center">
                            {{ $hasil->jumlah_salah ?? $hasil->salah ?? 0 }}
                        </td>
                        <td class="border border-slate-400 py-1.5 px-3 text-center font-bold">
                            {{ number_format($hasil->nilai ?? 0, 1) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border border-slate-400 py-4 text-center text-slate-500">Belum ada data nilai
                            peserta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>

</html>