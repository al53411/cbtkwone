<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta &amp; Nomor Meja (9x5.3 cm) - {{ $namaKelasDipilih ?? 'Semua Kelas' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 6mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000000;
            background: #ffffff;
        }

        .doc-header {
            text-align: center;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .doc-header h1 {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .doc-header p {
            font-size: 7.5pt;
            font-weight: bold;
            margin-top: 1px;
        }

        .row-block {
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 3mm;
        }

        .pair-table {
            border-collapse: separate;
            border-spacing: 4mm 0;
            margin: 0 auto;
        }

        /* Ukuran kartu presisi 9 cm x 5,3 cm */
        .card-box {
            width: 90mm;
            height: 53mm;
            min-width: 90mm;
            max-width: 90mm;
            min-height: 53mm;
            max-height: 53mm;
            vertical-align: top;
            border: 1.2px solid #000000;
            padding: 2mm 2.5mm;
            overflow: hidden;
            background: #ffffff;
        }

        .card-inner {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #000000;
            padding-bottom: 1.5px;
            margin-bottom: 2px;
        }

        .kop-logo {
            width: 22px;
            text-align: left;
            vertical-align: middle;
        }

        .kop-logo img {
            width: 19px;
            height: 19px;
            object-fit: contain;
            display: block;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
            line-height: 1.1;
        }

        .kop-title {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-sub {
            font-size: 6.2pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-school {
            font-size: 5.8pt;
            text-transform: uppercase;
        }

        .badge-peserta {
            border: 0.8px solid #000000;
            padding: 1px 3px;
            font-size: 5.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .meja-box {
            width: 100%;
            border: 1px solid #000000;
            background-color: #f8fafc;
            padding: 1.5px 4px;
            margin-bottom: 2px;
        }

        .meja-box td {
            vertical-align: middle;
        }

        .meja-label {
            font-size: 5.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meja-ruang {
            font-size: 6.2pt;
            font-weight: bold;
        }

        .meja-kode {
            text-align: right;
            font-family: "Courier New", Courier, monospace;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .bio-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.6pt;
            line-height: 1.18;
        }

        .bio-table td {
            padding: 0.3px 0;
            vertical-align: top;
        }

        .bio-label {
            width: 35%;
            color: #1e293b;
        }

        .bio-sep {
            width: 3%;
            font-weight: bold;
        }

        .bio-val {
            width: 62%;
            font-weight: bold;
        }

        .mono {
            font-family: "Courier New", Courier, monospace;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 0.8px solid #000000;
            margin-top: 1.5px;
        }

        .footer-table td {
            vertical-align: bottom;
            padding-top: 1.5px;
        }

        .foto-box {
            width: 28px;
            height: 36px;
            border: 0.8px solid #000000;
            text-align: center;
            vertical-align: middle !important;
            font-size: 5pt;
            font-weight: bold;
            line-height: 1.1;
        }

        .foto-box img {
            width: 26px;
            height: 34px;
            object-fit: cover;
            display: block;
        }

        .ttd-cell {
            padding-left: 4px;
            padding-right: 3px;
            font-size: 5.6pt;
            line-height: 1.15;
        }

        .ttd-space {
            height: 11px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            font-size: 6pt;
        }

        .qr-cell {
            width: 26px;
            text-align: right;
        }

        .qr-box {
            width: 24px;
            height: 24px;
            border: 0.8px solid #000000;
            padding: 0.5px;
            display: inline-block;
        }

        .qr-box img {
            width: 22px;
            height: 22px;
            display: block;
        }

        .cut-line {
            border-top: 0.8px dashed #64748b;
            margin: 3mm auto 0;
            width: 184mm;
            text-align: center;
            font-size: 5.5pt;
            letter-spacing: 1px;
            color: #475569;
        }
    </style>
</head>
<body>
    @php
        $judulUjianAktif = $judulUjian ?? request('judul_ujian', 'TES KEMAMPUAN AKADEMIK SD/MI');
        $taAktif = \App\Models\TahunAjaran::getAktif(auth()->user()->sekolah_id ?? null);
        $tahunPelajaranDipilih = $taAktif ? ($taAktif->tahun . ' (' . $taAktif->semester . ')') : '2026/2027';

        $toBase64Image = function ($fullPath) {
            if (!$fullPath || !file_exists($fullPath)) return null;
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
        };

        $currentKelasId = null;
        $noMejaPerKelas = 0;
    @endphp

    <div class="doc-header">
        <h1>KARTU PESERTA &amp; NOMOR MEJA {{ strtoupper($judulUjianAktif) }} (9 × 5,3 CM)</h1>
        <p>
            {{ strtoupper(Auth::user()->sekolah->nama_sekolah ?? 'SD NEGERI KAWU 1') }}
            • TP {{ $tahunPelajaranDipilih }}
            {{ !empty($namaKelasDipilih) && $namaKelasDipilih !== 'Semua Kelas' ? ' • KELAS ' . strtoupper($namaKelasDipilih) : '' }}
        </p>
    </div>

    @foreach($siswas as $siswa)
        @php
            if ($currentKelasId !== $siswa->kelas_id) {
                $currentKelasId = $siswa->kelas_id;
                $noMejaPerKelas = 1;
            } else {
                $noMejaPerKelas++;
            }

            $sekolah = $siswa->sekolah ?? Auth::user()->sekolah;
            $namaSekolah = $sekolah->nama_sekolah ?? 'SD NEGERI KAWU 1';
            $kotaTanggal = $sekolah->kabupaten_kota ?? ($sekolah->kabupaten ?? 'Ngawi');
            $tanggalCetak = now()->translatedFormat('d F Y');
            $namaKepsek = $sekolah->nama_kepsek ?? ($sekolah->kepala_sekolah ?? '........................................');
            $nipKepsek = $sekolah->nip_kepsek ?? ($sekolah->nip ?? '-');
            $ttlSiswa = $siswa->tempat_tanggal_lahir
                ?? trim(($siswa->tempat_lahir ?? '') . (!empty($siswa->tempat_lahir) && !empty($siswa->tanggal_lahir) ? ', ' : '') . ($siswa->tanggal_lahir ?? ''))
                ?: '-';

            $inisialKelas = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $siswa->kelas->nama_kelas ?? 'UMUM'));
            $nomorMejaLengkap = 'MJ-' . $inisialKelas . '-' . str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT);

            $logoPath = (!empty($sekolah->logo) && file_exists(public_path('storage/' . $sekolah->logo)))
                ? public_path('storage/' . $sekolah->logo)
                : public_path('logo.png');
            $logoBase64 = $toBase64Image($logoPath);

            $fotoPath = (!empty($siswa->foto) && file_exists(public_path('storage/' . $siswa->foto)))
                ? public_path('storage/' . $siswa->foto)
                : null;
            $fotoBase64 = $toBase64Image($fotoPath);

            $qrPayload = urlencode("NO_MEJA:{$nomorMejaLengkap}|NISN:{$siswa->nisn}|NAMA:{$siswa->nama_siswa}");
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={$qrPayload}";
        @endphp

        <div class="row-block">
            <table class="pair-table">
                <tr>
                    <!-- KIRI: NOMOR MEJA (9 x 5,3 cm) -->
                    <td class="card-box">
                        <table class="card-inner">
                            <tr>
                                <td style="vertical-align: top;">
                                    <table class="kop-table">
                                        <tr>
                                            <td class="kop-logo">
                                                @if($logoBase64)<img src="{{ $logoBase64 }}" alt="Logo">@endif
                                            </td>
                                            <td class="kop-text">
                                                <div class="kop-title">TANDA PENGENAL NOMOR MEJA</div>
                                                <div class="kop-sub">{{ $judulUjianAktif }}</div>
                                                <div class="kop-school">{{ $namaSekolah }} • {{ date('Y') }}</div>
                                            </td>
                                            <td style="width: 22px;"></td>
                                        </tr>
                                    </table>

                                    <table class="meja-box">
                                        <tr>
                                            <td>
                                                <div class="meja-label">NOMOR MEJA</div>
                                                <div class="meja-ruang">R.{{ $siswa->ruang ?? '01' }} / S.{{ $siswa->sesi ?? '01' }}</div>
                                            </td>
                                            <td class="meja-kode">{{ $nomorMejaLengkap }}</td>
                                        </tr>
                                    </table>

                                    <table class="bio-table">
                                        <tr>
                                            <td class="bio-label">NISN</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val mono">{{ $siswa->nisn }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bio-label">Nama</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val">{{ strtoupper($siswa->nama_siswa) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bio-label">Kelas</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: bottom;">
                                    <table class="footer-table">
                                        <tr>
                                            <td style="font-size: 5.5pt; line-height: 1.15;">
                                                Tempel pada sudut kanan atas meja ujian.
                                            </td>
                                            <td class="qr-cell">
                                                <div class="qr-box"><img src="{{ $qrUrl }}" alt="QR"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- KANAN: KARTU PESERTA (9 x 5,3 cm) -->
                    <td class="card-box">
                        <table class="card-inner">
                            <tr>
                                <td style="vertical-align: top;">
                                    <table class="kop-table">
                                        <tr>
                                            <td class="kop-logo">
                                                @if($logoBase64)<img src="{{ $logoBase64 }}" alt="Logo">@endif
                                            </td>
                                            <td class="kop-text">
                                                <div class="kop-title">KARTU TANDA PESERTA UJIAN</div>
                                                <div class="kop-sub">{{ $judulUjianAktif }}</div>
                                                <div class="kop-school">{{ $namaSekolah }} • {{ $tahunPelajaranDipilih }}</div>
                                            </td>
                                            <td style="width: 30px; text-align: right; vertical-align: middle;">
                                                <span class="badge-peserta">PESERTA</span>
                                            </td>
                                        </tr>
                                    </table>

                                    <table class="bio-table">
                                        <tr>
                                            <td class="bio-label">NISN</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val mono">{{ $siswa->nisn }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bio-label">Nama</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val">{{ strtoupper($siswa->nama_siswa) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bio-label">Kelas</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bio-label">PIN Ujian</td>
                                            <td class="bio-sep">:</td>
                                            <td class="bio-val mono">{{ $siswa->nisn }}*</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: bottom;">
                                    <table class="footer-table">
                                        <tr>
                                            <td class="foto-box">
                                                @if($fotoBase64)
                                                    <img src="{{ $fotoBase64 }}" alt="Foto">
                                                @else
                                                    3×4
                                                @endif
                                            </td>
                                            <td class="ttd-cell">
                                                <div>{{ $kotaTanggal }}, {{ $tanggalCetak }}</div>
                                                <div style="font-weight: bold;">Kepala Sekolah,</div>
                                                <div class="ttd-space"></div>
                                                <div class="ttd-nama">{{ $namaKepsek }}</div>
                                                <div class="mono">NIP. {{ $nipKepsek }}</div>
                                            </td>
                                            <td class="qr-cell">
                                                <div class="qr-box"><img src="{{ $qrUrl }}" alt="QR"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            @if(!$loop->last)
                <div class="cut-line">POTONG</div>
            @endif
        </div>
    @endforeach

    @if(!request()->boolean('pdf'))
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
</body>
</html>