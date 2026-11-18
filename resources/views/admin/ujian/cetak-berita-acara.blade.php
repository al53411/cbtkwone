<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Ujian CAT - {{ $ujian->nama_ujian ?? 'Ujian CAT' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        body {
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            background: #e5e7eb;
            color: #111827;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            margin: 0 auto;
            padding: 12mm 15mm;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
        }

        .watermark-logo {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            object-fit: contain;
            opacity: 0.04;
            filter: grayscale(100%);
            pointer-events: none;
            z-index: 0;
        }

        .content-layer {
            position: relative;
            z-index: 10;
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
                border: none;
                width: 100%;
                min-height: auto;
                padding: 0;
            }

            .watermark-logo {
                position: fixed;
                top: 50%;
                left: 50%;
            }
        }
    </style>
</head>

<body class="p-4">

    @php
        $user = Auth::user();
        $sekolah = null;

        if (!empty($ujian->sekolah_id)) {
            $sekolah = \App\Models\Sekolah::find($ujian->sekolah_id);
        }
        if (!$sekolah && !empty($user?->sekolah_id)) {
            $sekolah = \App\Models\Sekolah::find($user->sekolah_id);
        }
        if (!$sekolah) {
            $sekolah = \App\Models\Sekolah::first();
        }

        $namaSekolah = $sekolah->nama_sekolah ?? 'NAMA SEKOLAH BELUM DIATUR';
        $npsnSekolah = $sekolah->npsn ?? '00000000';
        $alamatSekolah = $sekolah->alamat ?? 'Alamat Satuan Pendidikan';
        $kotaSekolah = $sekolah->kabupaten_kota ?? 'Ngawi';
        $logoSekolah = !empty($sekolah->logo) ? asset('storage/' . $sekolah->logo) : asset('logo.png');
        $logoUtama = asset('logo.png');

        // 1. Data Kepala Sekolah Dinamis
        $kepsekUser = \App\Models\User::where('role', 'kepsek')
            ->when($sekolah, fn($q) => $q->where('sekolah_id', $sekolah->id))
            ->first();
        $namaKepsek = !empty($sekolah->nama_kepsek)
            ? $sekolah->nama_kepsek
            : ($kepsekUser->name ?? '........................................');
        $nipKepsek = !empty($sekolah->nip_kepsek)
            ? $sekolah->nip_kepsek
            : ($kepsekUser->nip ?? '-');

        // 2. Data Proktor Dinamis
        $proktorUser = \App\Models\User::where('role', 'proktor')
            ->when($sekolah, fn($q) => $q->where('sekolah_id', $sekolah->id))
            ->first();
        $namaProktor = !empty($sekolah->nama_proktor)
            ? $sekolah->nama_proktor
            : ($proktorUser->name ?? ($user->name ?? '........................................'));
        $nipProktor = !empty($sekolah->nip_proktor)
            ? $sekolah->nip_proktor
            : ($proktorUser->nip ?? ($user->nip ?? '-'));

        // 3. Data Pengawas Ruang Dinamis (Prioritas: Input Form -> Profil Sekolah -> Controller)
        $pengawasTampil = request()->filled('pengawas')
            ? request('pengawas')
            : (!empty($sekolah->nama_pengawas) ? $sekolah->nama_pengawas : ($namaPengawas ?? '........................................'));

        $nipPengawasTampil = request()->filled('nip_pengawas')
            ? request('nip_pengawas')
            : (!empty($sekolah->nip_pengawas) ? $sekolah->nip_pengawas : ($nipPengawas ?? '-'));

        // Format Hari & Tanggal Pelaksanaan
        $tanggalUjian = !empty($ujian->waktu_mulai)
            ? \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')
            : \Carbon\Carbon::now('Asia/Jakarta')->locale('id');

        $hariText = $tanggalUjian->translatedFormat('l');
        $tanggalText = $tanggalUjian->translatedFormat('d');
        $bulanText = $tanggalUjian->translatedFormat('F');
        $tahunText = $tanggalUjian->translatedFormat('Y');

        $jamMulaiText = !empty($ujian->waktu_mulai) ? \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') : '07:30';
        $jamSelesaiText = !empty($ujian->waktu_selesai)
            ? \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i')
            : \Carbon\Carbon::parse($jamMulaiText)->addMinutes((int) ($ujian->durasi ?? 60))->format('H:i');

        $kodeValidasi = 'BA-' . ($ujian->id ?? '1') . '-' . strtoupper(substr(md5($nomorBa . $npsnSekolah), 0, 8));
        $payloadQr = "BERITA ACARA UJIAN CAT\nNomor: {$nomorBa}\nSekolah: {$namaSekolah}\nUjian: " . ($ujian->nama_ujian ?? '-') . "\nHadir: {$jumlahHadir}/{$totalTerdaftar} Peserta\nPengawas: {$pengawasTampil}\nProktor: {$namaProktor}\nKode: {$kodeValidasi}";
    @endphp

    <!-- PANEL PENGATURAN CEPAT BERITA ACARA (FLAT - HILANG SAAT PRINT) -->
    <div id="toolbar-print" class="max-w-[210mm] mx-auto mb-4 bg-white p-4 border border-slate-300 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider">
                    Berita Acara CAT
                </span>
                <span class="text-xs font-semibold text-slate-700">Lengkapi detail berita acara sebelum mencetak</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()"
                    class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold cursor-pointer">
                    Cetak / Simpan PDF
                </button>
                <button type="button" onclick="window.close()"
                    class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-semibold cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.ujian.berita-acara', $ujian->id) }}"
            class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 text-xs">
            <div>
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Nomor Berita Acara</label>
                <input type="text" name="nomor_ba" value="{{ $nomorBa }}"
                    class="w-full px-2.5 py-1.5 border border-slate-300 bg-slate-50 focus:bg-white focus:border-slate-900 outline-none text-xs font-mono">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Ruang / Laboratorium</label>
                <input type="text" name="ruang" value="{{ $ruangUjian }}"
                    class="w-full px-2.5 py-1.5 border border-slate-300 bg-slate-50 focus:bg-white focus:border-slate-900 outline-none text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Nama Pengawas Ruang</label>
                <input type="text" name="pengawas" value="{{ $pengawasTampil }}"
                    class="w-full px-2.5 py-1.5 border border-slate-300 bg-slate-50 focus:bg-white focus:border-slate-900 outline-none text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">NIP Pengawas</label>
                <input type="text" name="nip_pengawas" value="{{ $nipPengawasTampil }}"
                    class="w-full px-2.5 py-1.5 border border-slate-300 bg-slate-50 focus:bg-white focus:border-slate-900 outline-none text-xs font-mono">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Catatan Kejadian Selama
                    Ujian</label>
                <input type="text" name="catatan" value="{{ $catatanKejadian }}"
                    class="w-full px-2.5 py-1.5 border border-slate-300 bg-slate-50 focus:bg-white focus:border-slate-900 outline-none text-xs">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold cursor-pointer">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- LEMBAR DOKUMEN BERITA ACARA FLAT -->
    <div class="sheet">

        <!-- Watermark Logo Tipis -->
        <img src="{{ $logoUtama }}" alt="Watermark Logo" class="watermark-logo">

        <div class="content-layer">

            <!-- KOP DOKUMEN FLAT -->
            <div class="border-b-2 border-slate-900 pb-3 mb-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ $logoSekolah }}" alt="Logo" class="w-12 h-12 object-contain shrink-0">
                        <div>
                            <p class="text-[9.5px] font-bold uppercase tracking-wider text-slate-600">
                                Panitia Penyelenggara Computer Assisted Test (CAT)
                            </p>
                            <h1 class="text-base font-extrabold uppercase tracking-tight text-slate-900 leading-snug">
                                {{ $namaSekolah }}
                            </h1>
                            <p class="text-[10px] text-slate-600">
                                NPSN: <span class="font-mono font-semibold text-slate-900">{{ $npsnSekolah }}</span> •
                                {{ $alamatSekolah }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0 border-l border-slate-300 pl-3 flex flex-col items-end">
                        <span
                            class="border border-slate-900 px-2 py-0.5 text-[8.5px] font-bold uppercase tracking-wider text-slate-900 mb-1">
                            DOKUMEN BERITA ACARA
                        </span>
                        <svg id="barcode-ba" class="h-6"></svg>
                    </div>
                </div>
            </div>

            <!-- JUDUL BERITA ACARA -->
            <div class="text-center mb-5">
                <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 underline underline-offset-4">
                    BERITA ACARA PELAKSANAAN UJIAN BERBASIS KOMPUTER (CAT)
                </h2>
                <p class="text-[10.5px] font-mono text-slate-700 mt-1">
                    Nomor: {{ $nomorBa }}
                </p>
            </div>

            <!-- ISI BERITA ACARA -->
            <div class="text-[11px] leading-relaxed text-justify text-slate-900 space-y-4">
                <p>
                    Pada hari ini <strong>{{ $hariText }}</strong> tanggal <strong>{{ $tanggalText }}</strong> bulan
                    <strong>{{ $bulanText }}</strong> tahun <strong>{{ $tahunText }}</strong>, bertempat di
                    <strong>{{ $namaSekolah }}</strong> ({{ $ruangUjian }}), telah diselenggarakan kegiatan
                    <strong>{{ strtoupper($ujian->nama_ujian ?? 'Ujian Berbasis Komputer') }}</strong> berbasis
                    <em>Computer Assisted Test (CAT)</em> mulai pukul <strong>{{ $jamMulaiText }} WIB</strong> sampai
                    dengan pukul <strong>{{ $jamSelesaiText }} WIB</strong> dengan rincian pelaksanaan sebagai berikut:
                </p>

                <!-- TABEL RINCIAN FLAT -->
                <table class="w-full text-[10.5px] border-collapse border border-slate-900 bg-white">
                    <tr class="border-b border-slate-400">
                        <td class="w-52 px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Satuan Pendidikan
                        </td>
                        <td class="px-3 py-2 font-bold text-slate-900">
                            {{ $namaSekolah }} (NPSN: {{ $npsnSekolah }})
                        </td>
                    </tr>
                    <tr class="border-b border-slate-400">
                        <td class="px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Mata Pelajaran / Sesi Ujian
                        </td>
                        <td class="px-3 py-2 font-bold text-slate-900">
                            {{ $ujian->mapel->nama_mapel ?? '-' }} — {{ $ujian->nama_ujian ?? '-' }}
                        </td>
                    </tr>
                    <tr class="border-b border-slate-400">
                        <td class="px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Kelas / Rombongan Belajar
                        </td>
                        <td class="px-3 py-2 font-semibold text-slate-900">
                            {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                        </td>
                    </tr>
                    <tr class="border-b border-slate-400">
                        <td class="px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Ruang / Lokasi Ujian
                        </td>
                        <td class="px-3 py-2 font-semibold text-slate-900">
                            {{ $ruangUjian }}
                        </td>
                    </tr>
                    <tr class="border-b border-slate-400">
                        <td class="px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Jumlah Peserta Terdaftar
                        </td>
                        <td class="px-3 py-2 font-mono font-bold text-slate-900">
                            {{ $totalTerdaftar }} Peserta
                        </td>
                    </tr>
                    <tr class="border-b border-slate-400">
                        <td class="px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Jumlah Peserta Hadir
                        </td>
                        <td class="px-3 py-2 font-mono font-bold text-slate-900">
                            {{ $jumlahHadir }} Peserta
                        </td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 bg-slate-100 font-semibold text-slate-800 border-r border-slate-400">
                            Jumlah Peserta Tidak Hadir
                        </td>
                        <td class="px-3 py-2 font-mono font-bold text-slate-900">
                            {{ $jumlahTidakHadir }} Peserta
                        </td>
                    </tr>
                </table>

                <!-- KOTAK CATATAN KEJADIAN FLAT -->
                <div class="border border-slate-900 p-3 bg-white">
                    <p
                        class="text-[9.5px] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 pb-1 mb-1.5">
                        Catatan Selama Pelaksanaan Ujian:
                    </p>
                    <p class="text-[10.5px] text-slate-900 min-h-[42px]">
                        {{ $catatanKejadian }}
                    </p>
                </div>

                <p>
                    Demikian Berita Acara Pelaksanaan Ujian Berbasis Komputer (CAT) ini dibuat dengan sesungguhnya
                    berdasarkan kondisi nyata di ruang ujian untuk dapat dipergunakan sebagaimana mestinya.
                </p>
            </div>

            <!-- BLOK TANDA TANGAN 3 PIHAK + QR CODE FLAT -->
            <div class="mt-8 pt-2" style="page-break-inside: avoid; break-inside: avoid;">
                <div class="text-right text-[10.5px] text-slate-800 mb-3">
                    {{ $kotaSekolah }}, {{ $tanggalText }} {{ $bulanText }} {{ $tahunText }}
                </div>

                <div class="grid grid-cols-3 gap-4 text-center text-[10px]">
                    <!-- 1. Pengawas Ruang -->
                    <div>
                        <p class="text-slate-700">Pengawas Ruang Ujian,</p>
                        <div class="h-16"></div>
                        <p class="font-bold underline uppercase text-slate-900 leading-tight">
                            {{ $pengawasTampil }}
                        </p>
                        <p class="font-mono text-[8.5px] text-slate-700 mt-0.5">
                            NIP. {{ $nipPengawasTampil }}
                        </p>
                    </div>

                    <!-- 2. Proktor Ujian CAT -->
                    <div>
                        <p class="text-slate-700">Proktor Ujian CAT,</p>
                        <div class="h-16"></div>
                        <p class="font-bold underline uppercase text-slate-900 leading-tight">
                            {{ $namaProktor }}
                        </p>
                        <p class="font-mono text-[8.5px] text-slate-700 mt-0.5">
                            NIP. {{ $nipProktor }}
                        </p>
                    </div>

                    <!-- 3. Kepala Sekolah -->
                    <div>
                        <p class="text-slate-700">Mengetahui, Kepala Sekolah</p>
                        <div class="h-16"></div>
                        <p class="font-bold underline uppercase text-slate-900 leading-tight">
                            {{ $namaKepsek }}
                        </p>
                        <p class="font-mono text-[8.5px] text-slate-700 mt-0.5">
                            NIP. {{ $nipKepsek }}
                        </p>
                    </div>
                </div>

                <!-- Bar QR Validasi Digital Flat -->
                <div class="mt-6 pt-3 border-t border-slate-400 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div id="qrcode-ba" class="p-1 bg-white border border-slate-400 shrink-0"></div>
                        <div class="text-[8px] text-slate-600 leading-snug">
                            <p class="font-bold text-slate-900 font-mono">KODE VALIDASI: {{ $kodeValidasi }}</p>
                            <p>Dokumen resmi dicetak melalui sistem CAT {{ $namaSekolah }}.</p>
                            <p>Pindai QR Code untuk verifikasi keaslian berita acara pelaksanaan.</p>
                        </div>
                    </div>
                    <div class="text-right text-[8px] font-mono text-slate-500">
                        Lampiran: Daftar Hadir &amp; Rekap Nilai CAT
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrContainer = document.getElementById('qrcode-ba');
            if (qrContainer && typeof QRCode !== 'undefined') {
                new QRCode(qrContainer, {
                    text: @json($payloadQr),
                    width: 56,
                    height: 56,
                    colorDark: '#111827',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            if (typeof JsBarcode !== 'undefined') {
                JsBarcode('#barcode-ba', @json($kodeValidasi), {
                    format: 'CODE128',
                    width: 1.05,
                    height: 20,
                    displayValue: true,
                    fontSize: 8,
                    font: 'monospace',
                    margin: 0,
                    background: 'transparent',
                    lineColor: '#111827'
                });
            }
        });
    </script>
</body>

</html>