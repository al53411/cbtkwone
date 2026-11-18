@extends('layouts.admin')

@section('title', 'Cetak Kartu & Nomor Meja Ujian')
@section('header', 'Cetak Kartu & Nomor Meja Ujian')

@section('content')
    <div class="max-w-5xl mx-auto space-y-5">

        <!-- Toolbar Filter Kelas, Judul Ujian & Tombol Aksi -->
        <div
            class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">

            <!-- Form Pilih Kelas & Judul Kegiatan -->
            <form action="{{ route('admin.siswa.cetak-kartu-kelas') }}" method="GET"
                class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full lg:w-auto">
                <div class="relative w-full sm:w-48">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                    </span>
                    <select name="kelas_id" required
                        class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($semuaKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ (isset($kelasPilih) && $kelasPilih == $k->id) || request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative w-full sm:w-56">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-heading text-xs"></i>
                    </span>
                    <input type="text" name="judul_ujian"
                        value="{{ request('judul_ujian', $judulUjian ?? 'Kartu Peserta Ujian CAT') }}"
                        placeholder="Judul Kegiatan Ujian"
                        class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>

                <button type="submit"
                    class="w-full sm:w-auto justify-center px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-xs inline-flex items-center gap-1.5 shrink-0 transition cursor-pointer">
                    <i class="fa-solid fa-filter"></i>
                    <span>Tampilkan</span>
                </button>
            </form>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2.5 w-full lg:w-auto">
                <a href="{{ route('admin.siswa.index') }}"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                @if(isset($siswas) && count($siswas) > 0)
                    <a href="{{ route('admin.siswa.print-preview', ['kelas_id' => request('kelas_id', $kelasPilih ?? null), 'judul_ujian' => request('judul_ujian', $judulUjian ?? 'Kartu Peserta Ujian CAT')]) }}"
                        target="_blank"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm inline-flex items-center gap-2 transition">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak / Simpan PDF ({{ count($siswas) }} Siswa)</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Area Preview Kartu Ujian -->
        <div class="cetak-area bg-white p-5 sm:p-8 rounded-2xl border border-slate-200 shadow-xs space-y-6">

            <!-- Header Dokumen -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b-2 border-slate-900 pb-3.5 gap-2">
                <div>
                    <h2 class="text-sm sm:text-base font-black uppercase tracking-wider text-slate-900">
                        {{ $judulUjian ?? 'Kartu Peserta Ujian & Nomor Meja' }}
                    </h2>
                    <p class="text-xs font-semibold text-slate-500 mt-0.5">
                        Rombongan Belajar:
                        <span
                            class="text-slate-800 font-bold">{{ $namaKelasDipilih ?? 'Pilih Kelas Terlebih Dahulu' }}</span>
                    </p>
                </div>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold self-start sm:self-auto">
                    <i class="fa-solid fa-scissors text-slate-400"></i>
                    Format 2 Kolom (Kartu + Nomor Meja)
                </span>
            </div>

            <!-- Container Kartu (Grid 2 Kolom: Kiri Kartu Peserta, Kanan Nomor Meja) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                        // Data Dinamis Sekolah & Kepala Sekolah
                        $sekolah = $siswa->sekolah ?? Auth::user()->sekolah;
                        $namaSekolah = $sekolah->nama_sekolah ?? 'NAMA SEKOLAH BELUM DIATUR';
                        $alamatSekolah = $sekolah->alamat ?? ($sekolah->alamat_sekolah ?? 'Alamat sekolah belum diisi');
                        $namaKepsek = $sekolah->nama_kepsek
                            ?? $sekolah->kepala_sekolah
                            ?? ($kepsekUser->name ?? '........................................');
                        $nipKepsek = $sekolah->nip_kepsek
                            ?? $sekolah->nip
                            ?? ($kepsekUser->nip ?? '-');

                        $inisialKelas = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $siswa->kelas->nama_kelas ?? 'UMUM'));
                        $nomorMejaPanjang = 'MJ-' . $inisialKelas . '-' . str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT);
                    @endphp

                    <!-- ================= KARTU 1: KARTU PESERTA UJIAN ================= -->
                    <div
                        class="bg-white border-2 border-slate-900 rounded-xl p-3.5 relative overflow-hidden flex flex-col justify-between min-h-[255px]">
                        <!-- Aksen Garis Atas Resmi -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-slate-900"></div>

                        <div class="pt-1">
                            <!-- Header Instansi & Logo Dinamis -->
                            <div class="flex items-center gap-2.5 border-b border-slate-300 pb-2 mb-2.5">
                                <div
                                    class="w-10 h-10 flex items-center justify-center shrink-0 bg-slate-50 border border-slate-200 rounded-lg p-1">
                                    <img src="{{ !empty($sekolah->logo) ? asset('storage/' . $sekolah->logo) : asset('logo.png') }}"
                                        alt="Logo" class="w-full h-full object-contain">
                                </div>
                                <div class="leading-tight min-w-0">
                                    <span
                                        class="block font-extrabold text-[9px] uppercase tracking-wider text-blue-800 truncate">
                                        {{ $judulUjian ?? 'Kartu Peserta Ujian CAT' }}
                                    </span>
                                    <h1 class="font-black text-[11px] text-slate-900 uppercase truncate">
                                        {{ $namaSekolah }}
                                    </h1>
                                    <p class="text-[8.5px] text-slate-500 truncate">
                                        {{ $alamatSekolah }}
                                    </p>
                                </div>
                            </div>

                            <!-- Biodata Siswa -->
                            <div class="grid grid-cols-12 gap-y-1 text-[10px] mb-2">
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
                                    {{ str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>

                        <!-- Footer Kartu: Pas Foto & Tanda Tangan Kepala Sekolah Dinamis -->
                        <div class="pt-2 border-t border-slate-300 flex justify-between items-end gap-2">
                            <!-- Kotak Pas Foto 3x4 -->
                            <div
                                class="w-11 h-14 bg-slate-100 border border-slate-400 rounded-md flex flex-col items-center justify-center text-[7px] text-slate-400 shrink-0 font-bold text-center overflow-hidden">
                                @if(!empty($siswa->foto))
                                    <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto" class="w-full h-full object-cover">
                                @else
                                    <span>FOTO</span>
                                    <span class="font-mono">3x4</span>
                                @endif
                            </div>

                            <!-- Tanda Tangan Kepala Sekolah -->
                            <div class="text-right leading-tight">
                                <div class="text-[8px] text-slate-600 mb-5">Kepala Sekolah,</div>
                                <div class="font-bold underline text-[9px] text-slate-900">
                                    {{ $namaKepsek }}
                                </div>
                                <div class="text-[8px] font-mono text-slate-600 mt-0.5">
                                    NIP. {{ $nipKepsek }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= KARTU 2: NOMOR MEJA UJIAN ================= -->
                    <div
                        class="bg-slate-50 border-2 border-dashed border-slate-800 rounded-xl p-3.5 text-center flex flex-col justify-between min-h-[255px]">
                        <div>
                            <div class="border-b border-slate-300 pb-2 mb-2.5">
                                <span class="block font-black text-[8.5px] uppercase tracking-widest text-slate-500">
                                    Tanda Pengenal Nomor Meja Ujian
                                </span>
                                <h1 class="font-black text-[11px] text-slate-900 uppercase mt-0.5 truncate">
                                    {{ $namaSekolah }}
                                </h1>
                            </div>

                            <!-- Kotak Besar Nomor Meja -->
                            <div class="my-2.5">
                                <div
                                    class="text-2xl font-black font-mono text-slate-900 tracking-wider bg-white py-2 px-3 border-2 border-slate-900 rounded-lg shadow-2xs w-full">
                                    {{ $nomorMejaPanjang }}
                                </div>
                            </div>

                            <!-- Detail Singkat Siswa di Meja -->
                            <div class="bg-white p-2.5 rounded-lg border border-slate-300 text-left text-[10px] space-y-1">
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

                @empty
                    <div
                        class="col-span-1 md:col-span-2 py-12 px-4 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 space-y-2">
                        <div
                            class="w-11 h-11 rounded-full bg-slate-200/70 text-slate-400 flex items-center justify-center mx-auto">
                            <i class="fa-solid fa-id-card text-lg"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-700">Belum Ada Kartu Ujian yang Ditampilkan</p>
                        <p class="text-[11px] text-slate-500">
                            Silakan pilih <b>Kelas / Rombel</b> pada dropdown di atas, lalu klik tombol <b>Tampilkan</b>.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>

    </div>
@endsection