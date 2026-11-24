@extends('layouts.admin')

@section('title', 'Cetak Nomor Meja & Kartu Peserta Ujian')
@section('header', 'Cetak Nomor Meja & Kartu Peserta Ujian')

@section('content')
    <!-- CSS Tampilan FLAT Resmi untuk Layar & Hasil Cetak (A4 & F4) -->
    <style>
        /* Desain Flat: Tanpa Shadow, Garis Hitam Tegas, Sudut Siku Presisi */
        .card-flat {
            border: 1.5px solid #000000 !important;
            border-radius: 0px !important;
            box-shadow: none !important;
            background-color: #ffffff !important;
        }

        .border-flat {
            border: 1px solid #000000 !important;
            border-radius: 0px !important;
        }

        .cut-line-flat {
            position: relative;
            border-top: 1px dashed #64748b;
            margin: 12px 0;
            text-align: center;
        }

        .cut-line-flat::before {
            content: "✂  POTONG DI SINI";
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 10px;
            font-size: 8px;
            font-weight: 800;
            color: #475569;
            letter-spacing: 0.15em;
        }

        /* Standar Cetak Kertas Resmi */
        @media print {
            @page {
                size: portrait;
                margin: 8mm 6mm;
            }

            html,
            body {
                height: auto !important;
                overflow: visible !important;
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            aside,
            header,
            #sidebar,
            #sidebar-overlay,
            .no-print {
                display: none !important;
            }

            .flex.h-screen,
            main,
            #area-cetak-wrapper,
            #area-cetak {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
                position: static !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: #ffffff !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .kartu-baris-cetak {
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-bottom: 4mm !important;
            }

            .kartu-grid {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 4mm !important;
            }

            .cut-line-flat {
                border-top-color: #000000 !important;
                margin: 3mm 0 !important;
            }

            .cut-line-flat::before {
                background: #ffffff !important;
                color: #000000 !important;
            }
        }
    </style>

    @php
        $judulUjianAktif = request('judul_ujian', $judulUjian ?? 'TES KEMAMPUAN AKADEMIK SD/MI');
        $taAktif = \App\Models\TahunAjaran::getAktif(auth()->user()->sekolah_id ?? null);
        $tahunPelajaranDipilih = $taAktif ? ($taAktif->tahun . ' (' . $taAktif->semester . ')') : ($tahunPelajaran ?? '2026/2027');
    @endphp

    <div class="max-w-5xl mx-auto space-y-4">

        <!-- Toolbar Filter & Aksi (No Print) -->
        <div
            class="no-print bg-white p-4 rounded-xl shadow-xs border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <form action="{{ route('admin.siswa.cetak-kartu-kelas') }}" method="GET"
                class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full lg:w-auto">
                <div class="relative w-full sm:w-48">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                    </span>
                    <select name="kelas_id" required
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($semuaKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ (isset($kelasPilih) && $kelasPilih == $k->id) || request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-heading text-xs"></i>
                    </span>
                    <input type="text" name="judul_ujian"
                        value="{{ request('judul_ujian', $judulUjianAktif) }}"
                        placeholder="Judul Kegiatan Ujian"
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit"
                    class="w-full sm:w-auto justify-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shrink-0 transition cursor-pointer">
                    <i class="fa-solid fa-filter"></i>
                    <span>Tampilkan</span>
                </button>
            </form>

            <div class="flex items-center justify-end gap-2 w-full lg:w-auto">
                <a href="{{ route('admin.siswa.index') }}"
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                @if(isset($siswas) && count($siswas) > 0)
                    <button type="button" onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-xs inline-flex items-center gap-1.5 transition cursor-pointer">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Langsung ({{ count($siswas) }} Siswa)</span>
                    </button>

                    <a href="{{ route('admin.siswa.print-preview', ['kelas_id' => request('kelas_id', $kelasPilih ?? null), 'judul_ujian' => request('judul_ujian', $judulUjianAktif)]) }}"
                        target="_blank"
                        class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Preview PDF</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Area Lembar Cetak / Preview Kartu (Flat Paper Container) -->
        <div id="area-cetak-wrapper" class="bg-white p-4 sm:p-6 rounded-none sm:rounded-xl border border-slate-300 space-y-4">

            <!-- Header Dokumen Atas -->
            <div class="text-center pb-2.5 border-b-2 border-black">
                <h2 class="text-xs sm:text-sm font-black uppercase tracking-wider text-black">
                    KARTU PESERTA &amp; NOMOR MEJA {{ strtoupper($judulUjianAktif) }}
                </h2>
                <p class="text-[11px] font-bold uppercase text-black mt-0.5">
                    {{ strtoupper(Auth::user()->sekolah->nama_sekolah ?? ($namaKelasDipilih ?? 'SD NEGERI KAWU 1')) }}
                </p>
                <p class="text-[10px] font-semibold uppercase text-slate-700 mt-0.5">
                    TAHUN PELAJARAN {{ $tahunPelajaranDipilih }}
                    {{ isset($namaKelasDipilih) && $namaKelasDipilih !== 'Semua Kelas' ? ' • KELAS ' . strtoupper($namaKelasDipilih) : '' }}
                    @if(isset($siswas) && count($siswas) > 0)
                        • TOTAL {{ count($siswas) }} PESERTA
                    @endif
                </p>
            </div>

            @php
                $currentKelasId = null;
                $noMejaPerKelas = 0;
            @endphp

            <div id="area-cetak" class="space-y-4">
                @forelse($siswas ?? [] as $index => $siswa)
                    @php
                        if ($currentKelasId !== $siswa->kelas_id) {
                            $currentKelasId = $siswa->kelas_id;
                            $noMejaPerKelas = 1;
                        } else {
                            $noMejaPerKelas++;
                        }

                        $sekolah = $siswa->sekolah ?? Auth::user()->sekolah;
                        $namaSekolah = $sekolah->nama_sekolah ?? 'SD NEGERI KAWU 1';
                        $kotaTanggal = $sekolah->kabupaten_kota ?? ($sekolah->kota ?? ($sekolah->kabupaten ?? 'Ngawi'));
                        $tanggalCetak = $tanggalKartu ?? now()->translatedFormat('d F Y');
                        $namaKepsek = $sekolah->nama_kepsek
                            ?? $sekolah->kepala_sekolah
                            ?? ($kepsekUser->name ?? '........................................');
                        $nipKepsek = $sekolah->nip_kepsek
                            ?? $sekolah->nip
                            ?? ($kepsekUser->nip ?? '-');
                        $ttlSiswa = $siswa->tempat_tanggal_lahir
                            ?? trim(($siswa->tempat_lahir ?? '') . (!empty($siswa->tempat_lahir) && !empty($siswa->tanggal_lahir) ? ', ' : '') . ($siswa->tanggal_lahir ?? ''))
                            ?: '-';

                        $inisialKelas = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $siswa->kelas->nama_kelas ?? 'UMUM'));
                        $nomorMejaSingkat = str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT);
                        $nomorMejaLengkap = 'MJ-' . $inisialKelas . '-' . $nomorMejaSingkat;

                        // Logo Lembaga: Jika upload pakai logo lembaga, jika tidak upload gunakan logo bawaan (asset/logo.png)
                        $logoUrl = (!empty($sekolah->logo) && file_exists(public_path('storage/' . $sekolah->logo)))
                            ? asset('storage/' . $sekolah->logo)
                            : asset('logo.png');

                        $qrPayload = urlencode("NO_MEJA:{$nomorMejaLengkap}|NISN:{$siswa->nisn}|NAMA:{$siswa->nama_siswa}");
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={$qrPayload}";
                    @endphp

                    <!-- 1 Baris Cetak Flat: Kiri Nomor Meja, Kanan Kartu Peserta -->
                    <div class="kartu-baris-cetak">
                        <div class="kartu-grid grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">

                            <!-- ================= KIRI: NOMOR MEJA UJIAN (FLAT) ================= -->
                            <div class="card-flat p-2.5 sm:p-3 flex flex-col justify-between">
                                <div>
                                    <!-- Kop Kartu Flat -->
                                    <div class="flex items-center border-b-[1.5px] border-black pb-1.5 mb-2 gap-2">
                                        <div class="w-8 h-8 shrink-0 flex items-center justify-center overflow-hidden">
                                            <img src="{{ $logoUrl }}" alt="Logo"
                                                style="width: 30px; height: 30px; max-width: 30px; max-height: 30px; object-fit: contain;">
                                        </div>
                                        <div class="text-center flex-1 min-w-0 leading-tight">
                                            <div class="text-[9.5px] font-black uppercase tracking-wider text-black">
                                                TANDA PENGENAL NOMOR MEJA
                                            </div>
                                            <div class="text-[9px] font-extrabold uppercase text-black truncate mt-0.5">
                                                {{ $judulUjianAktif }}
                                            </div>
                                            <div class="text-[8px] font-bold uppercase text-slate-800 truncate">
                                                {{ $namaSekolah }} • TAHUN {{ date('Y') }}
                                            </div>
                                        </div>
                                        <div class="w-8 shrink-0"></div>
                                    </div>

                                    <!-- Box Nomor Meja Flat -->
                                    <div class="border-[1.5px] border-black bg-slate-50 py-1.5 px-2.5 mb-2 flex items-center justify-between">
                                        <div class="text-left leading-none">
                                            <span class="block text-[7.5px] font-extrabold uppercase tracking-widest text-slate-700">
                                                NOMOR MEJA
                                            </span>
                                            <span class="text-[8.5px] font-bold text-black mt-0.5 block">
                                                RUANG {{ $siswa->ruang ?? '01' }} / SESI {{ $siswa->sesi ?? '01' }}
                                            </span>
                                        </div>
                                        <div class="text-xl sm:text-2xl font-black font-mono tracking-wider text-black">
                                            {{ $nomorMejaLengkap }}
                                        </div>
                                    </div>

                                    <!-- Biodata Singkat Flat -->
                                    <div class="grid grid-cols-12 gap-y-0.5 text-[9.5px] leading-snug px-0.5 mb-2 text-black">
                                        <div class="col-span-4 font-semibold text-slate-800">No. Peserta / NISN</div>
                                        <div class="col-span-8 font-bold font-mono text-black">: {{ $siswa->nisn }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Nama Peserta</div>
                                        <div class="col-span-8 font-bold uppercase text-black truncate">: {{ $siswa->nama_siswa }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Kelas / Rombel</div>
                                        <div class="col-span-8 font-semibold text-black truncate">: {{ $siswa->kelas->nama_kelas ?? '-' }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Asal Sekolah</div>
                                        <div class="col-span-8 font-semibold uppercase text-black truncate">: {{ $namaSekolah }}</div>
                                    </div>
                                </div>

                                <!-- Footer Flat: Petunjuk Meja + QR Code -->
                                <div class="pt-1.5 border-t border-black flex items-center justify-between gap-2 mt-auto">
                                    <div class="text-[8px] text-slate-700 leading-tight">
                                        <span class="font-bold text-black">Petunjuk:</span><br>
                                        Tempelkan tanda pengenal ini pada sudut kanan atas meja peserta ujian.
                                    </div>
                                    <div class="w-10 h-10 border border-black p-0.5 bg-white shrink-0 flex items-center justify-center">
                                        <img src="{{ $qrUrl }}" alt="QR"
                                            style="width: 36px; height: 36px; max-width: 36px; max-height: 36px;">
                                    </div>
                                </div>
                            </div>

                            <!-- ================= KANAN: KARTU PESERTA UJIAN (FLAT) ================= -->
                            <div class="card-flat p-2.5 sm:p-3 flex flex-col justify-between">
                                <div>
                                    <!-- Kop Kartu Peserta Flat -->
                                    <div class="flex items-center border-b-[1.5px] border-black pb-1.5 mb-2 gap-2">
                                        <div class="w-8 h-8 shrink-0 flex items-center justify-center overflow-hidden">
                                            <img src="{{ $logoUrl }}" alt="Logo"
                                                style="width: 30px; height: 30px; max-width: 30px; max-height: 30px; object-fit: contain;">
                                        </div>
                                        <div class="text-center flex-1 min-w-0 leading-tight">
                                            <div class="text-[9.5px] font-black uppercase tracking-wider text-black">
                                                KARTU TANDA PESERTA UJIAN
                                            </div>
                                            <div class="text-[9px] font-extrabold uppercase text-black truncate mt-0.5">
                                                {{ $judulUjianAktif }}
                                            </div>
                                            <div class="text-[8px] font-bold uppercase text-slate-800 truncate">
                                                {{ $namaSekolah }} • TP {{ $tahunPelajaranDipilih }}
                                            </div>
                                        </div>
                                        <span class="border border-black bg-white px-1.5 py-0.5 text-[7px] font-black uppercase shrink-0">
                                            PESERTA
                                        </span>
                                    </div>

                                    <!-- Biodata Lengkap Peserta Flat -->
                                    <div class="grid grid-cols-12 gap-y-0.5 text-[9.5px] leading-snug px-0.5 mb-2 text-black">
                                        <div class="col-span-4 font-semibold text-slate-800">No. Peserta / NISN</div>
                                        <div class="col-span-8 font-bold font-mono text-black">: {{ $siswa->nisn }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Nama Lengkap</div>
                                        <div class="col-span-8 font-bold uppercase text-black truncate">: {{ $siswa->nama_siswa }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Tmp, Tgl Lahir</div>
                                        <div class="col-span-8 font-semibold text-black truncate">: {{ $ttlSiswa }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Kelas / Rombel</div>
                                        <div class="col-span-8 font-semibold text-black truncate">: {{ $siswa->kelas->nama_kelas ?? '-' }}</div>

                                        <div class="col-span-4 font-semibold text-slate-800">Sandi / PIN Ujian</div>
                                        <div class="col-span-8 font-bold font-mono text-black">: {{ $siswa->nisn }}*</div>
                                    </div>
                                </div>

                                <!-- Footer Flat: Pas Foto Siswa (Kiri) + Tanda Tangan Kepsek (Tengah) + QR (Kanan) -->
                                <div class="pt-1.5 border-t border-black flex items-end justify-between gap-2 mt-auto">
                                    <!-- Kotak Pas Foto Siswa 3x4 (Flat) -->
                                    <div class="w-[50px] h-[64px] border border-black bg-white flex flex-col items-center justify-center overflow-hidden shrink-0">
                                        @if(!empty($siswa->foto) && file_exists(public_path('storage/' . $siswa->foto)))
                                            <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <span class="text-[6.5px] font-bold text-slate-700 text-center leading-tight">
                                                PAS FOTO<br>3x4
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Tanda Tangan Kepala Sekolah (Flat) -->
                                    <div class="relative text-left leading-tight flex-1 px-1">
                                        <div class="text-[7.5px] text-slate-800">
                                            {{ $kotaTanggal }}, {{ $tanggalCetak }}
                                        </div>
                                        <div class="text-[7.5px] font-semibold text-black mb-4">
                                            Kepala Sekolah,
                                        </div>

                                        <!-- Stempel Flat Halus -->
                                        <div class="absolute left-1 top-2.5 w-9 h-9 rounded-full border border-dashed border-blue-800/35 flex items-center justify-center pointer-events-none -rotate-12">
                                            <span class="text-[5px] font-extrabold text-blue-900/40 uppercase">STEMPEL</span>
                                        </div>

                                        <div class="text-[8px] font-bold underline text-black truncate max-w-[130px]">
                                            {{ $namaKepsek }}
                                        </div>
                                        <div class="text-[7px] font-mono text-slate-800">
                                            NIP. {{ $nipKepsek }}
                                        </div>
                                    </div>

                                    <!-- QR Code Verifikasi Siswa (Flat) -->
                                    <div class="w-10 h-10 border border-black p-0.5 bg-white shrink-0 flex items-center justify-center">
                                        <img src="{{ $qrUrl }}" alt="QR"
                                            style="width: 36px; height: 36px; max-width: 36px; max-height: 36px;">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Garis Pembatas Gunting Flat Antar Baris Siswa -->
                        @if(!$loop->last)
                            <div class="cut-line-flat my-2.5"></div>
                        @endif
                    </div>
                @empty
                    <div class="py-12 px-4 text-center bg-slate-50 border border-dashed border-slate-300 space-y-1.5">
                        <i class="fa-solid fa-id-card text-3xl text-slate-400 mb-2"></i>
                        <p class="text-xs font-bold text-slate-800">Belum Ada Kartu Ujian yang Ditampilkan</p>
                        <p class="text-[11px] text-slate-600">
                            Silakan pilih <b>Kelas / Rombel</b> pada toolbar di atas lalu klik tombol <b>Tampilkan</b>.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
@endsection
