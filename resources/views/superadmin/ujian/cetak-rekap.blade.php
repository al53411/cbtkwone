<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Nilai - {{ $ujian->nama_ujian }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body class="bg-white text-slate-900 p-8" onload="window.print()">

    <!-- Tombol Kembali / Aksi (Hilang saat diprint) -->
    <div class="no-print mb-6 flex justify-end">
        <button onclick="window.print()"
            class="px-4 py-2 bg-blue-700 text-white text-xs font-bold rounded-lg shadow hover:bg-blue-800">
            Cetak Ulang / Simpan PDF
        </button>
    </div>

    <!-- Kop Laporan Resmi -->
    <div class="text-center border-b-2 border-slate-900 pb-4 mb-6">
        <h2 class="text-lg font-bold uppercase tracking-wider">Laporan Hasil Evaluasi / Ujian Peserta Didik</h2>
        <h1 class="text-xl font-black uppercase tracking-wide mt-1">Sistem CBT Sekolah &bull; MAHESAPP</h1>
        <p class="text-xs text-slate-600 mt-1">Dokumen Rekapitulasi Nilai Resmi Berbasis Komputer</p>
    </div>

    <!-- Detail Ujian -->
    <div class="mb-6 grid grid-cols-2 text-xs gap-y-1 bg-slate-50 p-4 border border-slate-200 rounded">
        <div><b>Nama Ujian:</b> {{ $ujian->nama_ujian }}</div>
        <div><b>Mata Pelajaran:</b> {{ $ujian->mapel->nama_mapel ?? '-' }}</div>
        <div><b>Token Ujian:</b> {{ $ujian->token }}</div>
        <div><b>Waktu Cetak:</b> {{ date('d/m/Y H:i') }}</div>
    </div>

    <!-- Tabel Rekap -->
    <table class="w-full text-left text-xs border-collapse border border-slate-400">
        <thead>
            <tr class="bg-slate-200 text-slate-900 uppercase">
                <th class="border border-slate-400 py-2 px-3 text-center w-10">No</th>
                <th class="border border-slate-400 py-2 px-3">Nama Siswa</th>
                <th class="border border-slate-400 py-2 px-3">NISN</th>
                <th class="border border-slate-400 py-2 px-3 text-center">Benar</th>
                <th class="border border-slate-400 py-2 px-3 text-center">Salah</th>
                <th class="border border-slate-400 py-2 px-3 text-center">Kosong</th>
                <th class="border border-slate-400 py-2 px-3 text-center">Nilai Akhir</th>
                <th class="border border-slate-400 py-2 px-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapNilais as $index => $item)
                <tr>
                    <td class="border border-slate-400 py-2 px-3 text-center">{{ $index + 1 }}</td>
                    <td class="border border-slate-400 py-2 px-3 font-bold">
                        {{ $item->siswa->nama_siswa ?? ($item->siswa->nama ?? ($item->siswa->nama_lengkap ?? '-')) }}
                    </td>
                    <td class="border border-slate-400 py-2 px-3 font-mono">{{ $item->siswa->nisn ?? '-' }}</td>
                    <td class="border border-slate-400 py-2 px-3 text-center text-emerald-700 font-bold">
                        {{ $item->jumlah_benar }}</td>
                    <td class="border border-slate-400 py-2 px-3 text-center text-rose-700 font-bold">
                        {{ $item->jumlah_salah }}</td>
                    <td class="border border-slate-400 py-2 px-3 text-center text-amber-700 font-bold">
                        {{ $item->tidak_dijawab }}</td>
                    <td class="border border-slate-400 py-2 px-3 text-center font-black">
                        {{ number_format($item->nilai, 2) }}</td>
                    <td class="border border-slate-400 py-2 px-3 text-center font-bold">
                        @php
                            $isLulus = $item->nilai >= 75.00;
                        @endphp
                        {{ $isLulus ? 'LULUS' : 'TIDAK LULUS' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border border-slate-400 py-6 text-center text-slate-500 italic">
                        Tidak ada data peserta ujian yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan / Pengesahan -->
    <div class="mt-12 flex justify-between text-xs pt-4">
        <div class="text-center">
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Sekolah / Administrator</p>
            <br><br><br>
            <p class="border-b border-slate-900 inline-block px-12">&nbsp;</p>
            <p class="mt-1">NIP. ........................................</p>
        </div>
        <div class="text-center">
            <p>{{ date('d F Y') }}</p>
            <p class="font-bold">Proktor / Panitia Ujian</p>
            <br><br><br>
            <p class="border-b border-slate-900 inline-block px-12">&nbsp;</p>
            <p class="mt-1">NIP. ........................................</p>
        </div>
    </div>

</body>

</html>