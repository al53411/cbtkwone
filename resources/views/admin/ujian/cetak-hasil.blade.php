<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian CAT - {{ $ujian->nama_ujian ?? ($ujian->mapel->nama_mapel ?? 'Ujian CAT') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library QR Code & Barcode Batang (Code 128) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        body {
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            margin: 0 auto;
            padding: 10mm 12mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Watermark Background Tipis dari public/logo.png */
        .watermark-logo {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            object-fit: contain;
            opacity: 0.035;
            filter: grayscale(100%);
            pointer-events: none;
            z-index: 0;
        }

        .content-layer {
            position: relative;
            z-index: 10;
        }

        table.cat-table {
            border-collapse: collapse;
            width: 100%;
            background-color: #ffffff;
        }

        table.cat-table th,
        table.cat-table td {
            border: 1px solid #000000;
        }

        table.cat-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            #toolbar-print {
                display: none !important;
            }

            .sheet {
                border: none !important;
                width: 100%;
                min-height: 277mm;
                padding: 0;
            }

            .watermark-logo {
                position: fixed;
                top: 50%;
                left: 50%;
                opacity: 0.03;
            }
        }
    </style>
</head>

<body class="p-4">

    @php
        $user = Auth::user();
        $sekolah = $ujian->sekolah ?? ($user->sekolah ?? null);
        $namaSekolah = $sekolah->nama_sekolah ?? 'NAMA SEKOLAH BELUM DIATUR';
        $npsnSekolah = $sekolah->npsn ?? '00000000';
        $alamatSekolah = $sekolah->alamat ?? ($sekolah->alamat_sekolah ?? 'Alamat Satuan Pendidikan');
        $kotaSekolah = $sekolah->kabupaten_kota ?? ($sekolah->kabupaten ?? 'Ngawi');
        $logoSekolah = !empty($sekolah->logo) ? asset('storage/' . $sekolah->logo) : asset('logo.png');
        $logoUtama = asset('logo.png');

        // Data Kepala Sekolah Dinamis
        $kepsekUser = \App\Models\User::where('role', 'kepsek')
            ->when($sekolah, fn($q) => $q->where('sekolah_id', $sekolah->id))
            ->first();
        $namaKepsek = !empty($sekolah->nama_kepsek)
            ? $sekolah->nama_kepsek
            : ($sekolah->kepala_sekolah ?? ($kepsekUser->name ?? '........................................'));
        $nipKepsek = !empty($sekolah->nip_kepsek)
            ? $sekolah->nip_kepsek
            : ($sekolah->nip ?? ($kepsekUser->nip ?? '-'));

        // Data Proktor Dinamis (Prioritas dari Profil Sekolah -> Akun Proktor -> Guru Mapel -> User Login)
        $proktorUser = \App\Models\User::where('role', 'proktor')
            ->when($sekolah, fn($q) => $q->where('sekolah_id', $sekolah->id))
            ->first();
        $namaProktor = !empty($sekolah->nama_proktor)
            ? $sekolah->nama_proktor
            : ($proktorUser->name ?? ($ujian->guru->name ?? ($user->name ?? '........................................')));
        $nipProktor = !empty($sekolah->nip_proktor)
            ? $sekolah->nip_proktor
            : ($proktorUser->nip ?? ($ujian->guru->nip ?? ($user->nip ?? '-')));

        // Ambil & urutkan peringkat berdasarkan nilai tertinggi (gaya CAT BKN)
        $rawNilai = $hasilUjians ?? $nilaiUjians ?? $pesertas ?? ($ujian->nilaiUjians ?? collect());
        $daftarNilai = collect($rawNilai)->sortByDesc(function ($item) {
            return (float) ($item->nilai ?? $item->skor ?? 0);
        })->values();

        $kkm = (float) ($ujian->kkm ?? 75);
        $totalPeserta = $daftarNilai->count();
        $jumlahTuntas = $daftarNilai->filter(fn($i) => (float) ($i->nilai ?? $i->skor ?? 0) >= $kkm)->count();
        $jumlahBelumTuntas = $totalPeserta - $jumlahTuntas;
        $nilaiTertinggi = $totalPeserta > 0 ? $daftarNilai->max(fn($i) => (float) ($i->nilai ?? $i->skor ?? 0)) : 0;
        $nilaiTerendah = $totalPeserta > 0 ? $daftarNilai->min(fn($i) => (float) ($i->nilai ?? $i->skor ?? 0)) : 0;
        $rataRata = $totalPeserta > 0 ? $daftarNilai->avg(fn($i) => (float) ($i->nilai ?? $i->skor ?? 0)) : 0;

        $waktuCetak = \Carbon\Carbon::now('Asia/Jakarta');
        $kodeValidasi = 'CAT-' . ($ujian->id ?? '1') . '-' . strtoupper(substr(md5(($ujian->id ?? '1') . $npsnSekolah . $totalPeserta), 0, 8));
        $payloadQr = "DOKUMEN SAH HASIL UJIAN CAT\nSekolah: {$namaSekolah}\nUjian: " . ($ujian->nama_ujian ?? '-') . "\nProktor: {$namaProktor}\nPeserta: {$totalPeserta} Siswa | Rata-rata: " . number_format($rataRata, 2) . "\nKode: {$kodeValidasi}";
    @endphp

    <!-- Toolbar Pratinjau Bersih (Hilang saat Print) -->
    <div id="toolbar-print"
        class="max-w-[210mm] mx-auto mb-3 bg-white p-3 border border-slate-300 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span
                class="px-2.5 py-1 border border-slate-800 bg-white text-slate-900 text-[10px] font-bold uppercase tracking-wider">
                Format CAT BKN
            </span>
            <span class="text-xs text-slate-700 font-semibold">
                Pratinjau Dokumen Hasil Ujian (A4 Portrait)
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="px-4 py-1.5 bg-slate-900 hover:bg-black text-white text-xs font-bold cursor-pointer">
                Cetak / Simpan PDF
            </button>
            <button type="button" onclick="window.close()"
                class="px-3.5 py-1.5 bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Lembar Dokumen Resmi CAT (Bersih Putih Monokrom) -->
    <div class="sheet">

        <!-- WATERMARK BACKGROUND DARI public/logo.png -->
        <img src="{{ $logoUtama }}" alt="Watermark Logo" class="watermark-logo">

        <!-- LAYER KONTEN UTAMA -->
        <div class="content-layer">

            <!-- HEADER RESMI BERSIH GAYA CAT BKN -->
            <div class="border border-black mb-3 bg-white">
                <div class="flex items-center justify-between p-3 border-b border-black gap-3 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white p-0.5 flex items-center justify-center shrink-0">
                            <img src="{{ $logoSekolah }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-700">
                                Computer Assisted Test (CAT) — Panitia Penyelenggara Ujian
                            </p>
                            <h1 class="text-sm font-black uppercase tracking-tight text-black leading-tight">
                                {{ $namaSekolah }}
                            </h1>
                            <p class="text-[9.5px] text-slate-700">
                                NPSN: <span class="font-mono font-bold text-black">{{ $npsnSekolah }}</span> •
                                {{ $alamatSekolah }}
                            </p>
                        </div>
                    </div>

                    <!-- Barcode Batang Nomor Dokumen di Pojok Kanan Kop -->
                    <div class="text-right shrink-0 border-l border-black pl-3 flex flex-col items-end">
                        <span
                            class="inline-block border border-black bg-white text-black text-[8px] font-bold uppercase px-2 py-0.5 tracking-wider mb-1">
                            DOKUMEN HASIL UJIAN
                        </span>
                        <svg id="barcode-dokumen" class="h-7"></svg>
                        <p class="text-[8px] font-mono text-slate-700 mt-0.5">
                            Dicetak: {{ $waktuCetak->format('d/m/Y H:i') }} WIB
                        </p>
                    </div>
                </div>

                <!-- Bar Judul Kegiatan Bersih (Putih + Border Bawah Tegas) -->
                <div class="bg-white text-black px-3 py-1.5 text-center border-b-2 border-black">
                    <h2 class="text-[10.5px] font-extrabold uppercase tracking-widest">
                        REKAPITULASI HASIL NILAI PESERTA UJIAN BERBASIS KOMPUTER (CAT)
                    </h2>
                </div>
            </div>

            <!-- PANEL METADATA UJIAN & RINGKASAN STATISTIK (BERSIH PUTIH) -->
            <div class="grid grid-cols-12 gap-2.5 mb-3 text-[9.5px]">
                <!-- Kiri: Identitas Pelaksanaan -->
                <div class="col-span-7 border border-black p-2.5 bg-white">
                    <div
                        class="text-[8.5px] font-extrabold uppercase tracking-wider text-black border-b border-black pb-1 mb-1.5">
                        Informasi Pelaksanaan Ujian
                    </div>
                    <table class="w-full">
                        <tr>
                            <td class="w-28 py-0.5 text-slate-700 font-semibold">Nama Kegiatan</td>
                            <td class="w-3 py-0.5">:</td>
                            <td class="py-0.5 font-extrabold text-black uppercase">{{ $ujian->nama_ujian ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700 font-semibold">Mata Pelajaran</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-bold text-black">{{ $ujian->mapel->nama_mapel ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700 font-semibold">Kelas / Rombel</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-bold text-black">{{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700 font-semibold">Tanggal & Durasi</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-medium text-black">
                                {{ !empty($ujian->waktu_mulai) ? \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->translatedFormat('d F Y') : $waktuCetak->locale('id')->translatedFormat('d F Y') }}
                                ({{ $ujian->durasi ?? '-' }} Menit)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700 font-semibold">Ambang Batas (KKM)</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-mono font-bold text-black">{{ number_format($kkm, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Kanan: Ringkasan Statistik Hasil -->
                <div class="col-span-5 border border-black p-2.5 bg-white">
                    <div
                        class="text-[8.5px] font-extrabold uppercase tracking-wider text-black border-b border-black pb-1 mb-1.5">
                        Statistik Hasil Sesi
                    </div>
                    <table class="w-full">
                        <tr>
                            <td class="py-0.5 text-slate-700">Total Peserta Hadir</td>
                            <td class="py-0.5 text-right font-mono font-bold text-black">{{ $totalPeserta }} Siswa
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700">Memenuhi KKM (P/L)</td>
                            <td class="py-0.5 text-right font-mono font-bold text-black">{{ $jumlahTuntas }} Siswa
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700">Di Bawah KKM (TL)</td>
                            <td class="py-0.5 text-right font-mono font-bold text-black">{{ $jumlahBelumTuntas }}
                                Siswa</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700">Tertinggi / Terendah</td>
                            <td class="py-0.5 text-right font-mono font-bold text-black">
                                {{ number_format($nilaiTertinggi, 1) }} / {{ number_format($nilaiTerendah, 1) }}
                            </td>
                        </tr>
                        <tr class="border-t border-black">
                            <td class="pt-1 font-extrabold text-black">Rata-Rata Kelas</td>
                            <td class="pt-1 text-right font-mono font-black text-black">
                                {{ number_format($rataRata, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- TABEL UTAMA PERINGKAT & SKOR CAT (PUTIH BERSIH) -->
            <table class="cat-table text-[9.5px]">
                <thead>
                    <tr class="bg-white text-black uppercase tracking-wider text-[8.5px] border-b-2 border-black">
                        <th class="px-1.5 py-2 w-10 text-center font-extrabold">Rank</th>
                        <th class="px-2 py-2 w-28 text-center font-extrabold">No. Peserta / NISN</th>
                        <th class="px-2.5 py-2 text-left font-extrabold">Nama Peserta Ujian</th>
                        <th class="px-1.5 py-2 w-14 text-center font-extrabold">Benar</th>
                        <th class="px-1.5 py-2 w-14 text-center font-extrabold">Salah</th>
                        <th class="px-2 py-2 w-20 text-center font-extrabold">Waktu Selesai</th>
                        <th class="px-2 py-2 w-20 text-center font-extrabold">Skor Total</th>
                        <th class="px-2 py-2 w-24 text-center font-extrabold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarNilai as $index => $item)
                        @php
                            $nilai = (float) ($item->nilai ?? $item->skor ?? 0);
                            $tuntas = $nilai >= $kkm;
                            $jamSelesai = !empty($item->waktu_selesai)
                                ? \Carbon\Carbon::parse($item->waktu_selesai)->format('H:i:s')
                                : (!empty($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('H:i:s') : '-');
                        @endphp
                        <tr class="bg-white">
                            <td class="px-1.5 py-1.5 text-center font-mono font-bold text-black">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-mono font-semibold text-black">
                                {{ $item->siswa->nisn ?? '-' }}
                            </td>
                            <td class="px-2.5 py-1.5 font-bold uppercase text-black">
                                {{ $item->siswa->nama_siswa ?? ($item->siswa->nama ?? '-') }}
                            </td>
                            <td class="px-1.5 py-1.5 text-center font-mono text-black font-semibold">
                                {{ $item->jumlah_benar ?? $item->benar ?? '-' }}
                            </td>
                            <td class="px-1.5 py-1.5 text-center font-mono text-black font-semibold">
                                {{ $item->jumlah_salah ?? $item->salah ?? '-' }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-mono text-[8.5px] text-slate-700">
                                {{ $jamSelesai }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-mono font-black text-[10.5px] bg-white text-black">
                                {{ number_format($nilai, 2) }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-bold text-[8px]">
                                @if($tuntas)
                                    <span class="inline-block px-1.5 py-0.5 border border-black bg-white text-black">
                                        LULUS KKM
                                    </span>
                                @else
                                    <span class="inline-block px-1.5 py-0.5 border border-slate-500 bg-white text-slate-700">
                                        REMIDI
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center italic text-slate-600 bg-white">
                                Belum ada data hasil pengerjaan peserta pada sesi ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- FOOTER VALIDASI BERSIH: QR CODE + TANDA TANGAN RESMI -->
            <div class="mt-4 border border-black bg-white p-3" style="page-break-inside: avoid; break-inside: avoid;">
                <div class="grid grid-cols-12 gap-4 items-center">

                    <!-- Kolom 1: QR Code Validasi Digital (3 Kolom) -->
                    <div
                        class="col-span-3 flex flex-col items-center justify-center border-r border-black pr-2 text-center">
                        <div id="qrcode-validasi" class="p-1.5 bg-white border border-black"></div>
                        <span class="text-[7.5px] font-mono font-bold text-black mt-1">
                            {{ $kodeValidasi }}
                        </span>
                        <span class="text-[7px] text-slate-600 leading-tight">
                            Scan QR untuk verifikasi keaslian dokumen
                        </span>
                    </div>

                    <!-- Kolom 2: Tanda Tangan Kepala Sekolah (4 Kolom) -->
                    <div class="col-span-4 text-center text-[9.5px]">
                        <p class="text-slate-700">Mengetahui,</p>
                        <p class="font-bold text-black">Kepala Sekolah</p>
                        <div class="h-14"></div>
                        <p class="font-extrabold underline uppercase text-black leading-tight">
                            {{ $namaKepsek }}
                        </p>
                        <p class="font-mono text-[8.5px] text-black mt-0.5">
                            NIP. {{ $nipKepsek }}
                        </p>
                    </div>

                    <!-- Kolom 3: Tanda Tangan Proktor Ujian CAT (5 Kolom) -->
                    <div class="col-span-5 text-center text-[9.5px]">
                        <p class="text-slate-700">
                            {{ $kotaSekolah }},
                            {{ $waktuCetak->locale('id')->translatedFormat('d F Y') }}
                        </p>
                        <p class="font-bold text-black">Proktor Ujian CAT</p>
                        <div class="h-14"></div>
                        <p class="font-extrabold underline uppercase text-black leading-tight">
                            {{ $namaProktor }}
                        </p>
                        <p class="font-mono text-[8.5px] text-black mt-0.5">
                            NIP. {{ $nipProktor }}
                        </p>
                    </div>

                </div>

                <!-- Catatan Kaki Sistem -->
                <div
                    class="mt-2.5 pt-2 border-t border-black text-[8px] text-slate-600 flex items-center justify-between">
                    <span>
                        * Hasil nilai dihitung otomatis oleh sistem Computer Assisted Test (CAT) berdasarkan jawaban
                        terkunci peserta.
                    </span>
                    <span class="font-mono font-semibold text-black">
                        {{ $namaSekolah }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Render QR Code & Barcode Batang -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Render QR Code Validasi Dokumen
            const qrContainer = document.getElementById('qrcode-validasi');
            if (qrContainer && typeof QRCode !== 'undefined') {
                new QRCode(qrContainer, {
                    text: @json($payloadQr),
                    width: 68,
                    height: 68,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            // 2. Render Barcode Batang (Code 128) di Header Dokumen
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode('#barcode-dokumen', @json($kodeValidasi), {
                    format: 'CODE128',
                    width: 1.15,
                    height: 24,
                    displayValue: true,
                    fontSize: 8,
                    font: 'monospace',
                    margin: 0,
                    background: 'transparent',
                    lineColor: '#000000'
                });
            }
        });
    </script>
</body>

</html>