@extends('layouts.admin')

@section('title', 'Cetak Kartu & Nomor Meja Ujian')
@section('header', 'Cetak Kartu & Nomor Meja Ujian')

@section('content')
<div class="space-y-6">

    <!-- Filter Kelas & Tombol Aksi -->
    <div class="w-full max-w-4xl mx-auto mb-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Form Pilih Kelas -->
        <form action="{{ route('admin.siswa.cetak-kartu-kelas') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
            <select name="kelas_id" class="px-4 py-2 bg-slate-100 border border-slate-800 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($semuaKelas ?? [] as $k)
                    <option value="{{ $k->id }}" {{ isset($kelasPilih) && $kelasPilih == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-bold shadow flex items-center gap-1.5 shrink-0">
                <i class="fa-solid fa-filter"></i> Tampilkan Kelas
            </button>
        </form>

        <!-- Tombol Aksi -->
        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <a href="javascript:history.back()" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold shadow">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
            <!-- Tombol Cetak / Simpan PDF (Mengarah ke rute print-preview yang bersih tanpa layout admin) -->
            <a href="{{ route('admin.siswa.print-preview', ['kelas_id' => request('kelas_id')]) }}" target="_blank" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </a>
        </div>
    </div>

    <!-- Area Kartu Ujian (Preview di Halaman Admin) -->
    <div class="cetak-area space-y-6 bg-white p-6 rounded-xl">
        
        <!-- Judul Header Dokumen -->
        <div class="text-center border-b-2 border-slate-900 pb-3 mb-4">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-900">KARTU PESERTA UJIAN & NOMOR MEJA SEKOLAH</h2>
            <p class="text-[10px] font-semibold text-slate-600 mt-0.5">
                @if(isset($namaKelasDipilih))
                    Rombongan Belajar / Kelas: {{ $namaKelasDipilih }}
                @else
                    Semua Kelas
                @endif
            </p>
        </div>

        <!-- Container Kartu (Grid 2 Kolom) -->
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    $inisialKelas = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $siswa->kelas->nama_kelas ?? 'UMUM'));
                    $nomorMejaPanjang = 'MJ-' . $inisialKelas . '-' . str_pad($noMejaPerKelas, 3, '0', STR_PAD_LEFT);
                @endphp
            
                <!-- ================= KARTU 1: KARTU PESERTA UJIAN ================= -->
                <div class="bg-white border-2 border-slate-900 rounded-lg p-2.5 shadow-none relative overflow-hidden flex flex-col justify-between" style="height: 245px;">
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

                        <!-- Biodata Siswa -->
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

                    <!-- Footer Kartu: Pas Foto di Bawah Kiri & Tanda Tangan di Bawah Kanan -->
                    <div class="pt-1.5 border-t border-slate-300 flex justify-between items-end">
                        <!-- Kotak Pas Foto -->
                        <div class="w-10 h-13 bg-slate-100 border border-slate-400 rounded flex flex-col items-center justify-center text-[6.5px] text-slate-400 shrink-0 font-medium text-center p-0.5 shadow-2xs">
                            @if(isset($siswa->foto) && $siswa->foto)
                                <img src="{{ asset('storage/' . $siswa->foto) }}" class="w-full h-full object-cover rounded">
                            @else
                                <span>FOTO</span>
                                <span class="text-[6px] font-mono">3x4</span>
                            @endif
                        </div>

                        <!-- Tanda Tangan Kepala Sekolah -->
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


                <!-- ================= KARTU 2: NOMOR MEJA UJIAN ================= -->
                <div class="bg-slate-50 border-4 border-slate-800 rounded-lg p-2.5 shadow-none text-center flex flex-col justify-between" style="height: 245px;">
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

            @empty
                <div class="col-span-2 text-center py-10 text-slate-500 text-xs font-semibold">
                    Tidak ada data siswa yang ditemukan. Silakan pilih kelas terlebih dahulu, lalu klik "Tampilkan Kelas".
                </div>
            @endforelse
        </div>

    </div>

</div>
@endsection