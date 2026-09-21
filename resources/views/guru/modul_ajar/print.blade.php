<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Modul Ajar - {{ $modul->judul }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 11pt;
            }
            .print-container {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                border: none !important;
                box-shadow: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen p-4 md:p-8">

    <!-- BAR AKSI / TOMBOL (Tidak Ikut Tercetak) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ route('guru.modul_ajar.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition flex items-center gap-2 shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / Simpan PDF
        </button>
    </div>

    <!-- DOKUMEN CETAK -->
    <div class="print-container max-w-4xl mx-auto bg-white p-8 md:p-12 shadow-md border border-slate-200 leading-relaxed text-sm">
        <div class="text-center mb-8">
            <h2 class="text-lg font-bold uppercase tracking-wider underline">MODUL AJAR KURIKULUM MERDEKA</h2>
            <p class="text-base font-semibold mt-1">{{ $modul->judul }}</p>
        </div>

        <!-- I. INFORMASI UMUM -->
        <div class="mb-6">
            <h3 class="font-bold text-sm uppercase bg-slate-100 px-3 py-1 border-l-4 border-slate-800 mb-3">I. INFORMASI UMUM</h3>
            <table class="w-full text-left border-collapse">
                <tbody>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 w-1/3 font-semibold">Nama Penyusun</td>
                        <td class="py-1.5 w-4 text-center">:</td>
                        <td class="py-1.5">{{ $modul->user->name ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Satuan Pendidikan</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ Auth::user()?->sekolah?->nama_sekolah ?? $profilSekolah?->nama_sekolah ?? 'Sekolah' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Mata Pelajaran</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->mapel->nama_mapel ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Fase / Kelas</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">Fase {{ $modul->fase ?? '-' }} / Kelas {{ $modul->kelas ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Semester / Tahun Ajaran</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">Semester {{ $modul->semester ?? '-' }} / {{ $modul->tahun_ajaran ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Alokasi Waktu</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->alokasi_waktu ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Kompetensi Awal</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->kompetensi_awal ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Profil Pelajar Pancasila</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->profil_pelajar_pancasila ?? 'Mandiri, Bernalar Kritis, Gotong Royong' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Sarana dan Prasarana</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->sarana_prasarana ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Target Peserta Didik</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->target_peserta_didik ?? 'Peserta didik reguler/tipikal' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold">Model Pembelajaran</td>
                        <td class="py-1.5 text-center">:</td>
                        <td class="py-1.5">{{ $modul->model_pembelajaran ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- II. KOMPONEN INTI -->
        <div class="mb-6">
            <h3 class="font-bold text-sm uppercase bg-slate-100 px-3 py-1 border-l-4 border-slate-800 mb-3">II. KOMPONEN INTI</h3>
            
            <!-- Capaian Pembelajaran -->
            <div class="mb-4">
                <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">A. Capaian Pembelajaran (CP)</h4>
                <p class="text-justify pl-4 border-l-2 border-slate-300">
                    {{ $modul->deskripsi_cp ?? $modul->capaianPembelajaran->deskripsi_cp ?? $modul->capaianPembelajaran->deskripsi ?? '-' }}
                </p>
            </div>

            <!-- Tujuan Pembelajaran -->
            <div class="mb-4">
                <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">B. Tujuan Pembelajaran (TP)</h4>
                <ol class="list-decimal list-inside pl-4 space-y-1">
                    @forelse($modul->tujuanPembelajarans ?? [] as $tp)
                        <li>
                            @if(!empty($tp->kode_tp)) <strong>[{{ $tp->kode_tp }}]</strong> @endif
                            {{ $tp->deskripsi_tp ?? $tp->deskripsi ?? $tp->tujuan }}
                        </li>
                    @empty
                        <li class="text-slate-400 italic">Tidak ada Tujuan Pembelajaran.</li>
                    @endforelse
                </ol>
            </div>

            <!-- Pemahaman Bermakna & Pertanyaan Pemantik -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">C. Pemahaman Bermakna</h4>
                    <p class="text-justify bg-slate-50 p-2.5 rounded border border-slate-200 text-xs">
                        {{ $modul->pemahaman_bermakna ?? '-' }}
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">D. Pertanyaan Pemantik</h4>
                    <p class="text-justify bg-slate-50 p-2.5 rounded border border-slate-200 text-xs">
                        {{ $modul->pertanyaan_pemantik ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Kegiatan Pembelajaran -->
            <div class="mb-4">
                <h4 class="font-bold text-xs uppercase text-slate-700 mb-2">E. Kegiatan Pembelajaran</h4>
                
                @if(isset($modul->langkahPembelajarans) && $modul->langkahPembelajarans->count() > 0)
                    <div class="space-y-3">
                        @foreach($modul->langkahPembelajarans as $langkah)
                            <div class="border border-slate-200 rounded p-3">
                                <h5 class="font-bold text-xs border-b border-slate-200 pb-1 mb-2">
                                    Pertemuan Ke-{{ $langkah->pertemuan_ke }} ({{ $langkah->alokasi_waktu ?? '-' }})
                                </h5>
                                <div class="space-y-1.5 text-xs">
                                    <div><strong>Kegiatan Pendahuluan:</strong> {{ $langkah->kegiatan_pendahuluan ?? '-' }}</div>
                                    <div><strong>Kegiatan Inti:</strong> {{ $langkah->kegiatan_inti ?? '-' }}</div>
                                    <div><strong>Kegiatan Penutup:</strong> {{ $langkah->kegiatan_penutup ?? '-' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="border border-slate-200 rounded p-3 text-xs space-y-1.5">
                        <div><strong>Kegiatan Pendahuluan:</strong> {{ $modul->kegiatan_pendahuluan ?? 'Apersepsi, motivasi, dan penyampaian tujuan pembelajaran.' }}</div>
                        <div><strong>Kegiatan Inti:</strong> {{ $modul->kegiatan_inti ?? 'Eksplorasi konsep, diskusi, dan sintesis materi.' }}</div>
                        <div><strong>Kegiatan Penutup:</strong> {{ $modul->kegiatan_penutup ?? 'Refleksi pembelajaran, kesimpulan, dan tindak lanjut.' }}</div>
                    </div>
                @endif
            </div>

            <!-- Asesmen -->
            <div class="mb-4">
                <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">F. Asesmen</h4>
                <ul class="list-disc list-inside pl-4 text-xs space-y-1">
                    <li><strong>Asesmen Diagnostik:</strong> {{ $modul->asesmen_diagnostik ?? 'Penilaian kemampuan awal sebelum pembelajaran.' }}</li>
                    <li><strong>Asesmen Formatif:</strong> {{ $modul->asesmen_formatif ?? 'Penilaian proses selama kegiatan pembelajaran.' }}</li>
                    <li><strong>Asesmen Sumatif:</strong> {{ $modul->asesmen_sumatif ?? 'Penilaian akhir ketercapaian tujuan pembelajaran.' }}</li>
                </ul>
            </div>

            <!-- Pengayaan & Remedial -->
            <div class="mb-4">
                <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">G. Pengayaan dan Remedial</h4>
                <div class="text-xs space-y-1 pl-4 border-l-2 border-slate-200">
                    <div><strong>Pengayaan:</strong> {{ $modul->pengayaan ?? 'Pemberian materi/tugas tambahan bagi peserta didik dengan capaian tinggi.' }}</div>
                    <div><strong>Remedial:</strong> {{ $modul->remedial ?? 'Bimbingan khusus bagi peserta didik yang membutuhkan pemahaman ekstra.' }}</div>
                </div>
            </div>

            <!-- Refleksi Peserta Didik dan Guru -->
            <div class="mb-4">
                <h4 class="font-bold text-xs uppercase text-slate-700 mb-1">H. Refleksi Peserta Didik dan Guru</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                        <strong>Refleksi Peserta Didik:</strong>
                        <p class="mt-1">{{ $modul->refleksi_siswa ?? 'Bagian mana yang paling kamu sukai dari pembelajaran hari ini?' }}</p>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                        <strong>Refleksi Guru:</strong>
                        <p class="mt-1">{{ $modul->refleksi_guru ?? 'Apakah seluruh peserta didik mencapai tujuan pembelajaran yang ditetapkan?' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN / PENGESAHAN -->
        <div class="mt-12 pt-6 border-t border-slate-200">
            <div class="flex justify-between text-center text-xs">
                <div>
                    <p class="mb-16">Mengetahui,<br>Kepala Sekolah</p>
                    <p class="font-bold underline uppercase">{{ $kepalaSekolah->nama ?? $kepalaSekolah->name ?? '.....................................' }}</p>
                    <p>NIP. {{ $sekolah->nip_kepsek ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-16">Guru Mata Pelajaran</p>
                    <p class="font-bold underline uppercase">{{ $modul->user->name ?? '(..........................................)' }}</p>
                    <p>NIP. {{ $modul->user->nip ?? '..........................................' }}</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>