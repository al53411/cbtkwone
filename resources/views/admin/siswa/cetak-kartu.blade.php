@extends('layouts.admin')

@section('title', 'Cetak Kartu & Nomor Meja Ujian')
@section('header', 'Cetak Kartu & Nomor Meja Ujian')

@section('content')
<div class="space-y-6">

    <!-- Filter Kelas & Tombol Aksi -->
    <div class="max-w-4xl mx-auto bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
        
        <!-- Form Pilih Kelas -->
        <form action="{{ route('admin.siswa.cetak-kartu-kelas') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
            <select name="kelas_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
        <div class="text-center border-b pb-4 mb-4">
            <h2 class="text-sm font-bold uppercase text-slate-800">Kartu Peserta Ujian & Nomor Meja Peserta</h2>
            <p class="text-xs text-slate-500">
                @if(isset($namaKelasDipilih))
                    Kelas: {{ $namaKelasDipilih }}
                @else
                    Semua Kelas
                @endif
            </p>
        </div>

        <!-- Container Kartu -->
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 print-grid">
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
            
                <!-- ================= KARTU 1: KARTU PESERTA UJIAN (UNTUK SISWA) ================= -->
                <div class="page-break bg-white border-2 border-slate-800 rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between" style="min-height: 290px; margin-bottom: 15px;">
                    <div>
                        <div class="flex items-start justify-between border-b border-slate-200 pb-2 mb-2">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-2 shrink-0 mt-0.5">
                                    CBT
                                </div>
                                <div class="leading-tight">
                                    <h2 class="font-bold text-[9px] uppercase tracking-wider text-slate-500">Kartu Peserta Ujian</h2>
                                    <h1 class="font-extrabold text-[11px] text-slate-900">{{ $siswa->sekolah->nama_sekolah ?? 'Sistem Ujian Sekolah' }}</h1>
                                    <p class="text-[9px] text-slate-500 leading-tight mt-0.5">
                                        {{ $siswa->sekolah->alamat ?? $siswa->sekolah->alamat_sekolah ?? '' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-[9px] font-bold bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-300">
                                Urut: {{ str_pad($noMejaPerKelas, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        <!-- Detail Siswa -->
                        <div class="grid grid-cols-4 gap-1 text-[11px] mb-2">
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

                    <!-- Tanda Tangan & Footer -->
                    <div>
                        <div class="flex justify-between items-end pt-1 pb-2 border-t border-slate-100 text-[10px]">
                            <div class="text-[9px] text-slate-400 italic">
                                *Gunakan NISN & Password<br>untuk login ujian.
                            </div>
                            <div class="text-center leading-tight">
                                <span>Kepala Sekolah</span>
                                <div class="h-7"></div>
                                <span class="font-bold underline text-[10px] text-slate-800">{{ $siswa->sekolah->nama_kepsek ?? ($siswa->sekolah->kepala_sekolah ?? 'SITI KALIMATUS SAKDIYAH, S.Pd') }}</span>
                                <div class="text-[8px] text-slate-600 font-mono mt-0.5">
                                    NIP. {{ $siswa->sekolah->nip_kepsek ?? ($siswa->sekolah->nip ?? '197809212008012012') }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 -mx-4 -mb-4 px-4 py-1.5 border-t border-slate-200 flex justify-between items-center text-[9px] text-slate-500 font-semibold">
                            <span>Pengawas: ...........................</span>
                            <span>Ruang: ............</span>
                        </div>
                    </div>
                </div>


                <!-- ================= KARTU 2: NOMOR MEJA UJIAN (UNTUK MEJA) ================= -->
                <div class="page-break bg-slate-50 border-4 border-dashed border-slate-700 rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between text-center" style="min-height: 290px; margin-bottom: 15px;">
                    <div>
                        <div class="border-b-2 border-slate-300 pb-2 mb-3">
                            <h2 class="font-bold text-[9px] uppercase tracking-widest text-slate-500">Nomor Meja Peserta Ujian</h2>
                            <h1 class="font-extrabold text-[11px] text-slate-800 uppercase mt-0.5">{{ $siswa->sekolah->nama_sekolah ?? 'Sistem Ujian Sekolah' }}</h1>
                        </div>

                        <!-- NOMOR MEJA PANJANG -->
                        <div class="my-1">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block mb-1">KODE NOMOR MEJA</span>
                            <div class="text-2xl font-black font-mono text-blue-700 tracking-tight bg-white py-2 px-3 border-2 border-slate-300 rounded-lg shadow-inner inline-block w-full">
                                {{ $nomorMejaPanjang }}
                            </div>
                        </div>

                        <!-- Identitas Singkat di Meja -->
                        <div class="mt-2.5 bg-white p-2.5 rounded-lg border border-slate-300 text-left text-xs shadow-sm">
                            <div class="flex justify-between border-b border-slate-100 pb-1 mb-1">
                                <span class="text-slate-500 font-medium text-[11px]">No. Peserta:</span>
                                <span class="font-bold font-mono text-slate-900 text-[11px]">{{ $siswa->nisn }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium text-[11px]">Nama:</span>
                                <span class="font-bold text-slate-900 truncate max-w-[170px] text-[11px]">{{ $siswa->nama_siswa }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-1 pt-1 border-t border-slate-100">
                                <span class="text-slate-500 font-medium text-[11px]">Kelas:</span>
                                <span class="font-semibold text-slate-900 text-[11px]">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer No Meja -->
                    <div class="text-[9px] text-slate-400 italic pt-1.5 border-t border-slate-200">
                        *Harap tempel / letakkan nomor meja ini sesuai urutan kelas.
                    </div>
                </div>

            @empty
                <div class="col-span-2 text-center py-10 text-slate-500 text-xs">
                    Tidak ada data siswa yang ditemukan. Silakan pilih kelas terlebih dahulu, lalu klik "Tampilkan Kelas".
                </div>
            @endforelse
        </div>

    </div>

</div>

@endsection