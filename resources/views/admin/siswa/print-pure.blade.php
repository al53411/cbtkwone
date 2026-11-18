<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta & Nomor Meja Ujian - {{ $namaKelasDipilih ?? 'Semua Kelas' }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @page {
            size: 215mm 330mm;
            /* Ukuran Kertas F4 */
            margin: 5mm;
        }

        body {
            background: #f1f5f9;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #area-pdf {
            width: 205mm;
            background: #ffffff;
            margin: 0 auto;
            padding: 4mm;
            box-sizing: border-box;
        }

        /* 1 Baris = 1 Siswa (Kiri: Kartu Peserta, Kanan: Nomor Meja) */
        .card-pair-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .page-box {
            height: 255px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .page-break-f4 {
            page-break-after: always;
            break-after: page;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            #no-print-bar {
                display: none !important;
            }

            #area-pdf {
                box-shadow: none !important;
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body class="flex flex-col items-center justify-start min-h-screen p-4">

    <!-- Bar Status & Tombol Manual (Tersembunyi saat Print) -->
    <div id="no-print-bar"
        class="w-full max-w-[205mm] mb-4 flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
        <div id="loading-indicator"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span>Menyiapkan file PDF F4 otomatis...</span>
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <button type="button" onclick="window.print()"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold cursor-pointer">
                Cetak Langsung (Ctrl+P)
            </button>
            <button type="button" onclick="window.close()"
                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Area Konten Kartu Ujian yang akan di-export ke PDF F4 -->
    <div id="area-pdf" class="shadow-sm rounded-xl">

        <!-- Header Dokumen Utama -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-2 mb-3">
            <div>
                <h1 class="text-xs font-black uppercase tracking-wider text-slate-900">
                    {{ $judulUjian ?? 'KARTU PESERTA UJIAN & NOMOR MEJA SEKOLAH' }}
                </h1>
                <p class="text-[10px] font-semibold text-slate-600">
                    Rombongan Belajar / Kelas: <span
                        class="text-slate-900 font-bold">{{ $namaKelasDipilih ?? 'Semua Kelas' }}</span>
                </p>
            </div>
            <span class="text-[9px] font-bold text-slate-500 uppercase">
                Total: {{ count($siswas) }} Peserta
            </span>
        </div>

        @php
            $currentKelasId = null;
            $noMejaPerKelas = 0;
        @endphp

        @forelse($siswas as $index => $siswa)
            @php
                if ($currentKelasId !== $siswa->kelas_id) {
                    $currentKelasId = $siswa->kelas_id;
                    $noMejaPerKelas = 1;
                } else {
                    $noMejaPerKelas++;
                }

                // Data Dinamis Sekolah & Kepala Sekolah (Fallback: Relasi Siswa -> User Login -> User Kepsek -> Kosong)
                $sekolah = $siswa->sekolah ?? Auth::user()->sekolah;
                $namaSekolah = $sekolah->nama_sekolah ?? 'NAMA SEKOLAH BELUM DIATUR';
                $alamatSekolah = $sekolah->alamat ?? ($sekolah->alamat_sekolah ?? 'Alamat resmi satuan pendidikan');
                $namaKepsek = $sekolah->nama_kepsek
                    ?? $sekolah->kepala_sekolah
                    ?? ($kepsekUser->name ?? '........................................');
                $nipKepsek = $sekolah->nip_kepsek
                    ?? $sekolah->nip
                    ?? ($kepsekUser->nip ?? '-');
                $logoSekolah = !empty($sekolah->logo) ? asset('storage/' . $sekolah->logo) : asset('logo.png');

                $inisialKelas = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $siswa->kelas->nama_kelas ?? 'UMUM'));
                $nomorMejaPanjang = 'MJ-' . $inisialKelas . '-' . str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT);

                // Setiap 4 siswa (8 kartu) pas 1 halaman F4 (tinggi 330mm)
                $isAkhirHalamanF4 = (($index + 1) % 4 === 0) && !$loop->last;
            @endphp

            <div class="card-pair-row {{ $isAkhirHalamanF4 ? 'page-break-f4' : '' }}">
                <!-- ================= KARTU 1: KARTU PESERTA UJIAN ================= -->
                <div class="page-box bg-white border-2 border-slate-900 rounded-xl p-3.5 relative overflow-hidden">
                    <!-- Aksen Garis Atas Resmi -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-slate-900"></div>

                    <div class="pt-1">
                        <!-- Header Instansi & Logo Dinamis -->
                        <div class="flex items-center gap-2.5 border-b border-slate-300 pb-2 mb-2.5">
                            <div
                                class="w-10 h-10 flex items-center justify-center shrink-0 bg-slate-50 border border-slate-200 rounded-lg p-1">
                                <img src="{{ $logoSekolah }}" alt="Logo" class="w-full h-full object-contain">
                            </div>
                            <div class="leading-tight min-w-0">
                                <span
                                    class="block font-extrabold text-[9px] uppercase tracking-wider text-blue-800 truncate">
                                    {{ $judulUjian ?? 'Kartu Peserta Ujian CAT' }}
                                </span>
                                <h1 class="font-black text-[10.5px] text-slate-900 uppercase truncate">
                                    {{ $namaSekolah }}
                                </h1>
                                <p class="text-[8px] text-slate-500 truncate">
                                    {{ $alamatSekolah }}
                                </p>
                            </div>
                        </div>

                        <!-- Biodata Siswa -->
                        <div class="grid grid-cols-12 gap-y-1 text-[9.5px] mb-2">
                            <div class="col-span-4 text-slate-500 font-semibold">No. Peserta</div>
                            <div class="col-span-8 font-bold font-mono text-slate-900">: {{ $siswa->nisn }}</div>

                            <div class="col-span-4 text-slate-500 font-semibold">Nama Lengkap</div>
                            <div class="col-span-8 font-bold text-slate-900 truncate">: {{ $siswa->nama_siswa }}</div>

                            <div class="col-span-4 text-slate-500 font-semibold">Kelas / Rombel</div>
                            <div class="col-span-8 font-semibold text-slate-900">: {{ $siswa->kelas->nama_kelas ?? '-' }}
                            </div>

                            <div class="col-span-4 text-slate-500 font-semibold">Password Ujian</div>
                            <div class="col-span-8 font-mono font-bold text-blue-700">: {{ $siswa->nisn }}*</div>

                            <div class="col-span-4 text-slate-500 font-semibold">No. Meja</div>
                            <div class="col-span-8 font-mono font-bold text-slate-800">:
                                {{ str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kartu: Pas Foto 3x4 & Tanda Tangan Kepala Sekolah Dinamis -->
                    <div class="pt-2 border-t border-slate-300 flex justify-between items-end gap-2">
                        <div
                            class="w-11 h-14 bg-slate-100 border border-slate-400 rounded-md flex flex-col items-center justify-center text-[7px] text-slate-400 shrink-0 font-bold text-center overflow-hidden">
                            @if(!empty($siswa->foto))
                                <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto" class="w-full h-full object-cover">
                            @else
                                <span>FOTO</span>
                                <span class="font-mono">3x4</span>
                            @endif
                        </div>

                        <div class="text-right leading-tight">
                            <div class="text-[8px] text-slate-600 mb-5">Kepala Sekolah,</div>
                            <div class="font-bold underline text-[9.5px] text-slate-900">
                                {{ $namaKepsek }}
                            </div>
                            <div class="text-[9.5px] font-mono text-slate-600 mt-0.5">
                                NIP. {{ $nipKepsek }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= KARTU 2: NOMOR MEJA UJIAN ================= -->
                <div class="page-box bg-slate-50 border-2 border-dashed border-slate-800 rounded-xl p-3.5 text-center">
                    <div>
                        <div class="border-b border-slate-300 pb-2 mb-2.5">
                            <span class="block font-black text-[8px] uppercase tracking-widest text-slate-500">
                                Tanda Pengenal Nomor Meja Ujian
                            </span>
                            <h1 class="font-black text-[10.5px] text-slate-900 uppercase mt-0.5 truncate">
                                {{ $namaSekolah }}
                            </h1>
                        </div>

                        <!-- Kotak Besar Nomor Meja -->
                        <div class="my-2.5">
                            <div
                                class="text-2xl font-black font-mono text-slate-900 tracking-wider bg-white py-2 px-3 border-2 border-slate-900 rounded-lg w-full">
                                {{ $nomorMejaPanjang }}
                            </div>
                        </div>

                        <!-- Detail Singkat Siswa di Meja -->
                        <div class="bg-white p-2.5 rounded-lg border border-slate-300 text-left text-[9.5px] space-y-1">
                            <div class="flex justify-between gap-2">
                                <span class="text-slate-500 font-medium shrink-0">No. Peserta:</span>
                                <span class="font-bold font-mono text-slate-900">{{ $siswa->nisn }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-slate-500 font-medium shrink-0">Nama Siswa:</span>
                                <span class="font-bold text-slate-900 truncate">{{ $siswa->nama_siswa }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-slate-500 font-medium shrink-0">Kelas:</span>
                                <span class="font-semibold text-slate-900">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Instruksi Meja -->
                    <div class="text-[8px] text-slate-600 font-semibold italic pt-1.5 border-t border-slate-300">
                        Harap ditempel di bagian kanan atas meja peserta ujian.
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-500 text-xs font-semibold">
                Tidak ada data siswa untuk kelas ini.
            </div>
        @endforelse

    </div>

    <!-- Script Otomatis Download PDF (Format F4 215mm x 330mm) -->
    <script>
        window.addEventListener('load', function () {
            const element = document.getElementById('area-pdf');
            const indicator = document.getElementById('loading-indicator');

            // Beri jeda 500ms agar Tailwind CDN selesai me-render seluruh class sebelum di-capture
            setTimeout(() => {
                const opt = {
                    margin: [5, 5, 5, 5],
                    filename: 'kartu-dan-nomor-meja-ujian-{{ \Illuminate\Support\Str::slug($namaKelasDipilih ?? "semua-kelas") }}.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false },
                    jsPDF: { unit: 'mm', format: [215, 330], orientation: 'portrait' },
                    pagebreak: { mode: ['css', 'legacy'], after: '.page-break-f4' }
                };

                html2pdf().from(element).set(opt).save().then(() => {
                    indicator.innerHTML = '<span>✓ PDF berhasil diunduh! Tab akan tertutup otomatis...</span>';
                    indicator.className = 'bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2';
                    setTimeout(() => { window.close(); }, 1500);
                }).catch(err => {
                    console.error('Gagal generate PDF:', err);
                    indicator.innerText = 'Gagal mengunduh otomatis. Gunakan tombol Cetak Langsung (Ctrl+P).';
                    indicator.className = 'bg-rose-600 text-white px-4 py-2 rounded-lg text-xs font-bold';
                });
            }, 500);
        });
    </script>
</body>

</html>