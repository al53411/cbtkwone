<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta & Nomor Meja Ujian - {{ $namaKelasDipilih }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { background: white; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .card-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .page-box { 
            height: 245px !important; 
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
        Sedang menyiapkan dokumen PDF resmi (F4), harap tunggu...
    </div>

    <!-- Area Konten Kartu Ujian yang akan di-export -->
    <div id="area-pdf" class="w-full max-w-4xl bg-white p-3 space-y-2.5 shadow-sm rounded-xl">
        
        <!-- Header Dokumen Utama -->
        <div class="text-center border-b-2 border-slate-900 pb-1.5 mb-2">
            <h1 class="text-xs font-black uppercase tracking-wider text-slate-900">KARTU PESERTA UJIAN & NOMOR MEJA SEKOLAH</h1>
            <p class="text-[10px] font-semibold text-slate-600">Rombongan Belajar / Kelas: {{ $namaKelasDipilih }}</p>
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
                    <div class="page-box bg-white border-2 border-slate-900 rounded-lg p-2.5 shadow-none relative overflow-hidden">
                        <!-- Aksen Garis Atas Resmi -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-slate-900"></div>

                        <div>
                            <!-- Header Instansi & Logo Sejajar Rapi -->
                            <div class="flex items-center justify-between border-b border-slate-300 pb-1.5 mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-9 h-9 flex items-center justify-center shrink-0 bg-slate-50 border border-slate-200 rounded-md p-0.5">
                                        <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                                    </div>
                                    <div class="leading-tight">
                                        <h2 class="font-extrabold text-[8px] uppercase tracking-wider text-blue-800">Kartu Peserta Ujian CAT</h2>
                                        <h1 class="font-bold text-[9px] text-slate-900 uppercase">{{ $siswa->sekolah->nama_sekolah ?? 'Sistem Ujian Sekolah' }}</h1>
                                        <p class="text-[7px] text-slate-500 truncate max-w-[210px]">
                                            {{ $siswa->sekolah->alamat ?? $siswa->sekolah->alamat_sekolah ?? 'Alamat Resmi Satuan Pendidikan' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Biodata Siswa (Full Lebar karena Foto dipindah ke bawah) -->
                            <div class="grid grid-cols-4 gap-y-1 gap-x-1 text-[9px] mb-2">
                                <div class="text-slate-500 font-semibold">No. Peserta</div>
                                <div class="col-span-3 font-bold font-mono text-slate-900">: {{ $siswa->nisn }}</div>

                                <div class="text-slate-500 font-semibold">Nama Lengkap</div>
                                <div class="col-span-3 font-bold text-slate-900 truncate">: {{ $siswa->nama_siswa }}</div>

                                <div class="text-slate-500 font-semibold">Kelas / Rombel</div>
                                <div class="col-span-3 font-semibold text-slate-900">: {{ $siswa->kelas->nama_kelas ?? '-' }}</div>

                                <div class="text-slate-500 font-semibold">Password Ujian</div>
                                <div class="col-span-3 font-mono font-bold text-blue-700">: {{ $siswa->nisn }}*</div>

                                <div class="text-slate-500 font-semibold">No. Meja</div>
                                <div class="col-span-3 font-mono font-bold text-slate-800">: {{ str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>

                        <!-- Footer Kartu: Pas Foto di Bawah Sejajar Tanda Tangan -->
                        <div class="pt-1.5 border-t border-slate-300 flex justify-between items-end">
                            <!-- Kotak Pas Foto di Bawah Kiri -->
                            <div class="w-12 h-16 bg-slate-100 border border-slate-400 rounded flex flex-col items-center justify-center text-[6.5px] text-slate-400 shrink-0 font-medium text-center p-0.5 shadow-2xs">
                                @if(isset($siswa->foto) && $siswa->foto)
                                    <img src="{{ asset('storage/' . $siswa->foto) }}" class="w-full h-full object-cover rounded">
                                @else
                                    <span>FOTO</span>
                                    <span class="text-[6px] font-mono">3x4</span>
                                @endif
                            </div>

                            <!-- Tanda Tangan Kepala Sekolah di Bawah Kanan -->
                            <div class="text-right">
                                <div class="text-[7px] text-slate-700 mb-3">Kepala Sekolah,</div>
                                <div class="font-bold underline text-[7.5px] text-slate-900 leading-none mb-1">
                                    {{ $siswa->sekolah->nama_kepsek ?? ($siswa->sekolah->kepala_sekolah ?? 'SITI KALIMATUS SAKDIYAH, S.Pd') }}
                                </div>
                                <div class="text-[7px] font-mono text-slate-600">
                                    NIP. {{ $siswa->sekolah->nip_kepsek ?? ($siswa->sekolah->nip ?? '197809212008012012') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 2: NOMOR MEJA UJIAN -->
                    <div class="page-box bg-slate-50 border-4 border-slate-800 rounded-lg p-2.5 shadow-none text-center flex flex-col justify-between">
                        <div>
                            <div class="border-b-2 border-slate-300 pb-1 mb-1">
                                <h2 class="font-black text-[7.5px] uppercase tracking-widest text-slate-600">TANDA PENGENAL NOMOR MEJA UJIAN</h2>
                                <h1 class="font-extrabold text-[9px] text-slate-900 uppercase mt-0.5">{{ $siswa->sekolah->nama_sekolah ?? 'Sistem Ujian Sekolah' }}</h1>
                            </div>

                            <!-- Kotak Besar Nomor Meja -->
                            <div class="my-1.5">
                                <div class="text-xl font-black font-mono text-slate-900 tracking-wider bg-white py-1.5 px-2 border-2 border-slate-900 rounded-md shadow-inner inline-block w-full">
                                    {{ $nomorMejaPanjang }}
                                </div>
                            </div>

                            <!-- Detail Singkat Siswa di Meja -->
                            <div class="bg-white p-1.5 rounded-md border border-slate-300 text-left text-[8.5px] space-y-0.5">
                                <div class="flex justify-between">
                                    <span class="text-slate-500 font-medium">No. Peserta:</span>
                                    <span class="font-bold font-mono text-slate-900">{{ $siswa->nisn }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500 font-medium">Nama:</span>
                                    <span class="font-bold text-slate-900 truncate max-w-[160px]">{{ $siswa->nama_siswa }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500 font-medium">Kelas:</span>
                                    <span class="font-semibold text-slate-900">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Instruksi Meja -->
                        <div class="text-[7px] text-slate-500 font-semibold italic pt-1 border-t border-slate-300 bg-slate-100/50 py-0.5 rounded">
                            Harap ditempel di bagian kanan atas meja peserta ujian.
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-10 text-slate-500 text-xs font-semibold">
                Tidak ada data siswa untuk kelas ini.
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