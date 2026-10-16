<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Kartu & Nomor Meja Ujian</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { background: white; font-family: sans-serif; }
        .card-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .page-box { 
            height: 230px !important; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-start min-h-screen p-4">

    <!-- Indikator Proses -->
    <div id="loading-indicator" class="bg-blue-600 text-white px-6 py-3 rounded-xl shadow-lg mb-4 text-xs font-bold flex items-center gap-2">
        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Sedang menyiapkan file PDF (F4), harap tunggu...
    </div>

    <!-- Area Konten Kartu Ujian yang akan di-export -->
    <div id="area-pdf" class="w-full max-w-4xl bg-white p-3 space-y-3 shadow-sm rounded-xl">
        
        <!-- Header Judul Dokumen -->
        <div class="text-center border-b pb-1.5 mb-2">
            <h1 class="text-xs font-bold uppercase tracking-wider text-slate-800">Kartu Peserta Ujian & Nomor Meja Peserta</h1>
            <p class="text-[10px] text-slate-500">Kelas: {{ $namaKelasDipilih }}</p>
        </div>

        @php
            $currentKelasId = null;
            $noMejaPerKelas = 0;
            $chunks = $siswas->chunk(2);
        @endphp

        @forelse($chunks as $chunk)
            <div class="card-row">
                @foreach($chunk as $siswa)
                    @php
                        if ($currentKelasId !== $siswa->kelas_id) {
                            $currentKelasId = $siswa->kelas_id;
                            $noMejaPerKelas = 1; 
                        } else {
                            $noMejaPerKelas++;
                        }

                        $inisialKelas = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $siswa->kelas->nama_kelas ?? 'UMUM'));
                        $nomorMejaPanjang = 'MJ-' . $inisialKelas . '-' . str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT);
                    @endphp

                    <!-- KARTU 1: KARTU PESERTA UJIAN -->
                    <div class="page-box bg-white border-2 border-slate-800 rounded-lg p-2.5 shadow-none">
                        <div>
                            <div class="flex items-start justify-between border-b border-slate-200 pb-1 mb-1">
                                <div class="flex items-start">
                                    <div class="w-6 h-6 bg-blue-600 rounded-md flex items-center justify-center text-white font-bold text-[10px] mr-1.5 shrink-0">
                                        CBT
                                    </div>
                                    <div class="leading-tight">
                                        <h2 class="font-bold text-[7px] uppercase tracking-wider text-slate-500">Kartu Peserta Ujian</h2>
                                        <h1 class="font-extrabold text-[9px] text-slate-900">{{ $siswa->sekolah->nama_sekolah ?? 'Sistem Ujian Sekolah' }}</h1>
                                        <p class="text-[7px] text-slate-500 leading-tight">
                                            {{ $siswa->sekolah->alamat ?? $siswa->sekolah->alamat_sekolah ?? '' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="text-[7px] font-bold bg-slate-100 text-slate-700 px-1 py-0.5 rounded border border-slate-300">
                                    Urut: {{ str_pad($noMejaPerKelas, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-4 gap-0.5 text-[9px] mb-1">
                                <div class="col-span-1 text-slate-500 font-medium">No. Peserta</div>
                                <div class="col-span-3 font-bold font-mono text-slate-900">: {{ $siswa->nisn }}</div>

                                <div class="col-span-1 text-slate-500 font-medium">Nama Siswa</div>
                                <div class="col-span-3 font-bold text-slate-900 truncate">: {{ $siswa->nama_siswa }}</div>

                                <div class="col-span-1 text-slate-500 font-medium">Kelas</div>
                                <div class="col-span-3 font-semibold text-slate-900">: {{ $siswa->kelas->nama_kelas ?? '-' }}</div>

                                <div class="col-span-1 text-slate-500 font-medium">Password</div>
                                <div class="col-span-3 font-mono font-bold text-rose-600">: {{ $siswa->nisn }}*</div>
                            </div>
                        </div>

                        <div>
                            <!-- Bagian Tanda Tangan Dirapikan -->
                            <div class="flex justify-between items-end pt-1 pb-1.5 border-t border-slate-100 text-[8px]">
                                <div class="text-[7px] text-slate-400 italic">
                                    *Gunakan NISN & Password<br>untuk login ujian.
                                </div>
                                <div class="text-right pr-2">
                                    <div class="text-[7px] text-slate-600 mb-5">Kepala Sekolah</div>
                                    <div class="font-bold underline text-[8px] text-slate-800 leading-none mb-1">{{ $siswa->sekolah->nama_kepsek ?? ($siswa->sekolah->kepala_sekolah ?? 'SITI KALIMATUS SAKDIYAH, S.Pd') }}</div>
                                    <div class="text-[10px] text-slate-600">
                                        NIP. {{ $siswa->sekolah->nip_kepsek ?? ($siswa->sekolah->nip ?? '197809212008012012') }}
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 -mx-2.5 -mb-2.5 px-2.5 py-0.5 border-t border-slate-200 flex justify-between items-center text-[7px] text-slate-500 font-semibold">
                                <span>Pengawas: ...........................</span>
                                <span>Ruang: ............</span>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 2: NOMOR MEJA UJIAN -->
                    <div class="page-box bg-slate-50 border-4 border-dashed border-slate-700 rounded-lg p-2.5 shadow-none text-center">
                        <div>
                            <div class="border-b-2 border-slate-300 pb-1 mb-1">
                                <h2 class="font-bold text-[7px] uppercase tracking-widest text-slate-500">Nomor Meja Peserta Ujian</h2>
                                <h1 class="font-extrabold text-[9px] text-slate-800 uppercase mt-0.5">{{ $siswa->sekolah->nama_sekolah ?? 'Sistem Ujian Sekolah' }}</h1>
                            </div>

                            <div class="my-0.5">
                                <span class="text-[7px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">KODE NOMOR MEJA</span>
                                <div class="text-lg font-black font-mono text-blue-700 tracking-tight bg-white py-1 px-2 border-2 border-slate-300 rounded-md inline-block w-full">
                                    {{ $nomorMejaPanjang }}
                                </div>
                            </div>

                            <div class="mt-1 bg-white p-1.5 rounded-md border border-slate-300 text-left text-[9px]">
                                <div class="flex justify-between border-b border-slate-100 pb-0.5 mb-0.5">
                                    <span class="text-slate-500">No. Peserta:</span>
                                    <span class="font-bold font-mono text-slate-900">{{ $siswa->nisn }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Nama:</span>
                                    <span class="font-bold text-slate-900 truncate max-w-[150px]">{{ $siswa->nama_siswa }}</span>
                                </div>
                                <div class="flex justify-between items-center mt-0.5 pt-0.5 border-t border-slate-100">
                                    <span class="text-slate-500">Kelas:</span>
                                    <span class="font-semibold text-slate-900">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-[7px] text-slate-400 italic pt-1 border-t border-slate-200">
                            *Harap tempel / letakkan nomor meja ini sesuai urutan kelas.
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-10 text-slate-500 text-xs">
                Tidak ada data siswa.
            </div>
        @endforelse

    </div>

    <!-- Script Otomatis Download PDF (Format F4) -->
    <script>
        window.onload = function() {
            const element = document.getElementById('area-pdf');
            const indicator = document.getElementById('loading-indicator');
            
            const opt = {
                margin:       4,
                filename:     'kartu-dan-nomor-meja-ujian-kelas-{{ $namaKelasDipilih }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: [215, 330], orientation: 'portrait' }
            };

            html2pdf().from(element).set(opt).save().then(() => {
                indicator.style.display = 'none';
                setTimeout(() => { window.close(); }, 1000);
            }).catch(err => {
                console.error('Gagal generate PDF:', err);
                indicator.innerText = 'Gagal mengunduh PDF. Silakan coba lagi.';
                indicator.className = 'bg-rose-600 text-white px-6 py-3 rounded-xl shadow-lg mb-4 text-xs font-bold';
            });
        };
    </script>
</body>
</html>