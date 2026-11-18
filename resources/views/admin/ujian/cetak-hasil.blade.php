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
            background: #e2e8f0;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            margin: 0 auto;
            padding: 10mm 12mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Watermark Background dari public/logo.png */
        .watermark-logo {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 340px;
            height: 340px;
            object-fit: contain;
            opacity: 0.065;
            filter: grayscale(15%);
            pointer-events: none;
            z-index: 0;
        }

        .bg-cat-pattern {
            background-image: radial-gradient(#cbd5e1 0.65px, transparent 0.65px);
            background-size: 14px 14px;
        }

        .content-layer {
            position: relative;
            z-index: 10;
        }

        table.cat-table {
            border-collapse: collapse;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.88);
        }

        table.cat-table th,
        table.cat-table td {
            border: 1px solid #1e3a8a;
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
                box-shadow: none !important;
                width: 100%;
                min-height: 277mm;
                padding: 0;
            }

            .watermark-logo {
                position: fixed;
                top: 50%;
                left: 50%;
                opacity: 0.06;
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

    <!-- Toolbar Pratinjau (Hilang saat Print) -->
    <div id="toolbar-print"
        class="max-w-[210mm] mx-auto mb-4 bg-white p-3.5 rounded-xl border border-slate-300 shadow-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span
                class="px-2.5 py-1 rounded bg-blue-950 text-amber-300 text-[10px] font-extrabold uppercase tracking-wider">
                Format CAT BKN
            </span>
            <span class="text-xs text-slate-600 font-semibold">
                Pratinjau Dokumen Hasil Ujian (A4 Portrait)
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="px-4 py-2 bg-blue-800 hover:bg-blue-900 text-white rounded-lg text-xs font-bold shadow-2xs cursor-pointer">
                Cetak / Simpan PDF
            </button>
            <button type="button" onclick="window.close()"
                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Lembar Dokumen Resmi CAT -->
    <div class="sheet bg-cat-pattern shadow-md">

        <!-- WATERMARK BACKGROUND DARI public/logo.png -->
        <img src="{{ $logoUtama }}" alt="Watermark Logo" class="watermark-logo">

        <!-- LAYER KONTEN UTAMA -->
        <div class="content-layer">

            <!-- HEADER RESMI GAYA CAT BKN -->
            <div class="border-2 border-blue-950 rounded-md overflow-hidden mb-3 bg-white">
                <div class="h-1.5 bg-gradient-to-r from-blue-950 via-blue-700 to-amber-500"></div>

                <div
                    class="flex items-center justify-between p-3 border-b border-blue-900/30 gap-3 bg-gradient-to-r from-blue-50/90 via-white to-amber-50/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-lg bg-white border border-blue-200 p-1 shadow-2xs flex items-center justify-center shrink-0">
                            <img src="{{ $logoSekolah }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <p class="text-[9px] font-extrabold uppercase tracking-widest text-blue-800">
                                Computer Assisted Test (CAT) — Panitia Penyelenggara Ujian
                            </p>
                            <h1 class="text-sm font-black uppercase tracking-tight text-slate-900 leading-tight">
                                {{ $namaSekolah }}
                            </h1>
                            <p class="text-[9.5px] text-slate-600">
                                NPSN: <span class="font-mono font-bold text-blue-950">{{ $npsnSekolah }}</span> •
                                {{ $alamatSekolah }}
                            </p>
                        </div>
                    </div>

                    <!-- Barcode Batang Nomor Dokumen di Pojok Kanan Kop -->
                    <div class="text-right shrink-0 border-l border-blue-200 pl-3 flex flex-col items-end">
                        <span
                            class="inline-block bg-blue-950 text-amber-300 text-[8.5px] font-extrabold uppercase px-2 py-0.5 rounded-xs tracking-wider mb-1">
                            DOKUMEN HASIL UJIAN
                        </span>
                        <svg id="barcode-dokumen" class="h-7"></svg>
                        <p class="text-[8px] font-mono text-slate-600 mt-0.5">
                            Dicetak: {{ $waktuCetak->format('d/m/Y H:i') }} WIB
                        </p>
                    </div>
                </div>

                <!-- Bar Judul Kegiatan -->
                <div class="bg-blue-950 text-white px-3 py-1.5 text-center">
                    <h2 class="text-[10.5px] font-extrabold uppercase tracking-widest">
                        REKAPITULASI HASIL NILAI PESERTA UJIAN BERBASIS KOMPUTER (CAT)
                    </h2>
                </div>
            </div>

            <!-- PANEL METADATA UJIAN & RINGKASAN STATISTIK -->
            <div class="grid grid-cols-12 gap-2.5 mb-3 text-[9.5px]">
                <!-- Kiri: Identitas Pelaksanaan -->
                <div class="col-span-7 border border-blue-950 rounded-md p-2.5 bg-white/90">
                    <div
                        class="text-[8.5px] font-extrabold uppercase tracking-wider text-blue-900 border-b border-blue-100 pb-1 mb-1.5">
                        Informasi Pelaksanaan Ujian
                    </div>
                    <table class="w-full">
                        <tr>
                            <td class="w-28 py-0.5 text-slate-600 font-semibold">Nama Kegiatan</td>
                            <td class="w-3 py-0.5">:</td>
                            <td class="py-0.5 font-extrabold text-slate-900 uppercase">{{ $ujian->nama_ujian ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-600 font-semibold">Mata Pelajaran</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-bold text-blue-900">{{ $ujian->mapel->nama_mapel ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-600 font-semibold">Kelas / Rombel</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-bold text-slate-900">{{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-600 font-semibold">Tanggal & Durasi</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-medium text-slate-800">
                                {{ !empty($ujian->waktu_mulai) ? \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->translatedFormat('d F Y') : $waktuCetak->locale('id')->translatedFormat('d F Y') }}
                                ({{ $ujian->durasi ?? '-' }} Menit)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-600 font-semibold">Ambang Batas (KKM)</td>
                            <td class="py-0.5">:</td>
                            <td class="py-0.5 font-mono font-bold text-amber-700">{{ number_format($kkm, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Kanan: Ringkasan Statistik Hasil -->
                <div class="col-span-5 border border-blue-950 rounded-md p-2.5 bg-blue-50/80">
                    <div
                        class="text-[8.5px] font-extrabold uppercase tracking-wider text-blue-950 border-b border-blue-200 pb-1 mb-1.5">
                        Statistik Hasil Sesi
                    </div>
                    <table class="w-full">
                        <tr>
                            <td class="py-0.5 text-slate-700">Total Peserta Hadir</td>
                            <td class="py-0.5 text-right font-mono font-bold text-slate-900">{{ $totalPeserta }} Siswa
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700">Memenuhi KKM (P/L)</td>
                            <td class="py-0.5 text-right font-mono font-bold text-emerald-700">{{ $jumlahTuntas }} Siswa
                            </td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700">Di Bawah KKM (TL)</td>
                            <td class="py-0.5 text-right font-mono font-bold text-rose-700">{{ $jumlahBelumTuntas }}
                                Siswa</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 text-slate-700">Tertinggi / Terendah</td>
                            <td class="py-0.5 text-right font-mono font-bold text-slate-900">
                                {{ number_format($nilaiTertinggi, 1) }} / {{ number_format($nilaiTerendah, 1) }}
                            </td>
                        </tr>
                        <tr class="border-t border-blue-200">
                            <td class="pt-1 font-extrabold text-blue-950">Rata-Rata Kelas</td>
                            <td class="pt-1 text-right font-mono font-black text-blue-950">
                                {{ number_format($rataRata, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- TABEL UTAMA PERINGKAT & SKOR CAT -->
            <table class="cat-table text-[9.5px]">
                <thead>
                    <tr class="bg-blue-950 text-white uppercase tracking-wider text-[8.5px]">
                        <th class="px-1.5 py-2 w-10 text-center">Rank</th>
                        <th class="px-2 py-2 w-28 text-center">No. Peserta / NISN</th>
                        <th class="px-2.5 py-2 text-left">Nama Peserta Ujian</th>
                        <th class="px-1.5 py-2 w-14 text-center">Benar</th>
                        <th class="px-1.5 py-2 w-14 text-center">Salah</th>
                        <th class="px-2 py-2 w-20 text-center">Waktu Selesai</th>
                        <th class="px-2 py-2 w-20 text-center bg-blue-900 text-amber-300">Skor Total</th>
                        <th class="px-2 py-2 w-24 text-center">Status</th>
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
                        <tr class="{{ $index % 2 === 1 ? 'bg-blue-50/45' : 'bg-white/85' }}">
                            <td class="px-1.5 py-1.5 text-center font-mono font-bold text-blue-950">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-mono font-semibold text-slate-900">
                                {{ $item->siswa->nisn ?? '-' }}
                            </td>
                            <td class="px-2.5 py-1.5 font-bold uppercase text-slate-900">
                                {{ $item->siswa->nama_siswa ?? ($item->siswa->nama ?? '-') }}
                            </td>
                            <td class="px-1.5 py-1.5 text-center font-mono text-emerald-800 font-semibold">
                                {{ $item->jumlah_benar ?? $item->benar ?? '-' }}
                            </td>
                            <td class="px-1.5 py-1.5 text-center font-mono text-rose-800 font-semibold">
                                {{ $item->jumlah_salah ?? $item->salah ?? '-' }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-mono text-[8.5px] text-slate-600">
                                {{ $jamSelesai }}
                            </td>
                            <td
                                class="px-2 py-1.5 text-center font-mono font-black text-[10.5px] bg-amber-50/85 text-blue-950">
                                {{ number_format($nilai, 2) }}
                            </td>
                            <td class="px-2 py-1.5 text-center font-extrabold text-[8px]">
                                @if($tuntas)
                                    <span
                                        class="inline-block px-1.5 py-0.5 rounded-xs border border-emerald-700 bg-emerald-50 text-emerald-800">
                                        LULUS KKM
                                    </span>
                                @else
                                    <span
                                        class="inline-block px-1.5 py-0.5 rounded-xs border border-rose-700 bg-rose-50 text-rose-800">
                                        REMIDI
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center italic text-slate-500 bg-white/90">
                                Belum ada data hasil pengerjaan peserta pada sesi ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- FOOTER VALIDASI: QR CODE + BARCODE BATANG + TANDA TANGAN RESMI -->
            <div class="mt-5 border border-blue-950 rounded-md bg-white/95 p-3"
                style="page-break-inside: avoid; break-inside: avoid;">
                <div class="grid grid-cols-12 gap-4 items-center">

                    <!-- Kolom 1: QR Code Validasi Digital (3 Kolom) -->
                    <div
                        class="col-span-3 flex flex-col items-center justify-center border-r border-slate-200 pr-2 text-center">
                        <div id="qrcode-validasi" class="p-1.5 bg-white border border-slate-300 rounded"></div>
                        <span class="text-[7.5px] font-mono font-bold text-blue-950 mt-1">
                            {{ $kodeValidasi }}
                        </span>
                        <span class="text-[7px] text-slate-500 leading-tight">
                            Scan QR untuk verifikasi keaslian berita acara
                        </span>
                    </div>

                    <!-- Kolom 2: Tanda Tangan Kepala Sekolah (4 Kolom) -->
                    <div class="col-span-4 text-center text-[9.5px]">
                        <p class="text-slate-600">Mengetahui,</p>
                        <p class="font-bold text-slate-900">Kepala Sekolah</p>
                        <div class="h-14"></div>
                        <p class="font-extrabold underline uppercase text-slate-900 leading-tight">
                            {{ $namaKepsek }}
                        </p>
                        <p class="font-mono text-[8.5px] text-slate-700 mt-0.5">
                            NIP. {{ $nipKepsek }}
                        </p>
                    </div>

                    <!-- Kolom 3: Tanda Tangan Proktor Ujian CAT (5 Kolom) -->
                    <div class="col-span-5 text-center text-[9.5px]">
                        <p class="text-slate-600">
                            {{ $kotaSekolah }},
                            {{ $waktuCetak->locale('id')->translatedFormat('d F Y') }}
                        </p>
                        <p class="font-bold text-slate-900">Proktor Ujian CAT</p>
                        <div class="h-14"></div>
                        <p class="font-extrabold underline uppercase text-slate-900 leading-tight">
                            {{ $namaProktor }}
                        </p>
                        <p class="font-mono text-[8.5px] text-slate-700 mt-0.5">
                            NIP. {{ $nipProktor }}
                        </p>
                    </div>

                </div>

                <!-- Catatan Kaki Sistem -->
                <div
                    class="mt-2.5 pt-2 border-t border-slate-200 text-[8px] text-slate-500 flex items-center justify-between">
                    <span>
                        * Hasil nilai dihitung otomatis oleh sistem Computer Assisted Test (CAT) berdasarkan jawaban
                        terkunci peserta.
                    </span>
                    <span class="font-mono font-semibold text-slate-700">
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
                    colorDark: '#0f172a',
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
                    lineColor: '#0f172a'
                });
            }
        });
    </script>
</body>

</html>