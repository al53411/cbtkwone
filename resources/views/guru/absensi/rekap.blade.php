@extends('layouts.guru')

@section('title', $profilSekolah->nama_sekolah ?? 'SDN Kawu 1')

@section('content')
@php
    // Array nama bulan dalam Bahasa Indonesia
    $namaBulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $bulanAktif = sprintf('%02d', (int)$bulan);
@endphp

<div class="w-full bg-white text-slate-800 p-4 sm:p-6">

    <!-- Kop Cetak (Hanya Muncul Saat Print/Cetak) -->
    <div class="hidden print:block text-center mb-6">
        <h2 class="text-xl font-bold uppercase tracking-wide text-black">{{ $profilSekolah->nama_sekolah ?? 'SD NEGERI KAWU 1' }}</h2>
        <h3 class="text-lg font-semibold uppercase mt-1 text-black">REKAPITULASI ABSENSI SISWA</h3>
        <p class="text-sm text-slate-700 mt-1">
            Bulan: {{ $namaBulan[$bulanAktif] ?? '' }} {{ $tahun }}
        </p>
        <div class="border-b-2 border-black mt-3"></div>
    </div>

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Rekapitulasi Absensi Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Ringkasan dan rekap kehadiran siswa per bulan.</p>
        </div>
        <!-- Tombol Action -->
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.absensi.index') }}" 
            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-sm">
            <span>⬅️</span> Kembali ke Input Absensi
            </a>
            
            <!-- PERBAIKAN: Memanggil route cetak khusus ke rekap_cetak.blade.php -->
            <a href="{{ route('guru.absensi.cetak', ['kelas_id' => $kelasId, 'bulan' => $bulan, 'tahun' => $tahun]) }}" 
            target="_blank"
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-sm">
            <span>🖨️</span> Cetak Rekap
            </a>
        </div>
    </div>

    <!-- Filter Kelas, Bulan, & Tahun -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200/80 print:hidden">
        <form action="{{ route('guru.absensi.rekap') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($daftarKelas as $kelas)
                    <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->nama_kelas ?? $kelas->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bulan</label>
                <select name="bulan"
                    class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                    @foreach($namaBulan as $key => $nama)
                        <option value="{{ $key }}" {{ $bulanAktif == $key ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun</label>
                <select name="tahun"
                    class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-3 lg:col-span-1">
                <button type="submit"
                    class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <span>🔍</span> Filter Data
                </button>
            </div>
        </form>
    </div>

    @if(isset($siswaList) && $siswaList->isNotEmpty())

    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200/80 print:shadow-none print:border-none print:p-0">
        
        <!-- TAMPILAN MOBILE (Card View) -->
        <div class="block md:hidden space-y-3.5 print:hidden">
            @foreach($siswaList as $index => $siswa)
            @php
                $stat = $rekapData[$siswa->id] ?? ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0];
                $namaSiswa = $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->nama ?? '-';
            @endphp
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs font-bold text-slate-400">#{{ $index + 1 }}</span>
                    <div>
                        <p class="font-bold text-sm text-slate-800">{{ $namaSiswa }}</p>
                        <p class="text-[11px] text-slate-500">NISN: {{ $siswa->nisn ?? '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-1.5 text-center pt-1">
                    <div class="bg-emerald-100 text-emerald-800 py-1.5 rounded-lg text-xs font-semibold">
                        <p class="text-[10px] text-emerald-600">Hadir</p>
                        <p class="text-sm font-bold">{{ $stat['hadir'] }}</p>
                    </div>
                    <div class="bg-blue-100 text-blue-800 py-1.5 rounded-lg text-xs font-semibold">
                        <p class="text-[10px] text-blue-600">Izin</p>
                        <p class="text-sm font-bold">{{ $stat['izin'] }}</p>
                    </div>
                    <div class="bg-amber-100 text-amber-800 py-1.5 rounded-lg text-xs font-semibold">
                        <p class="text-[10px] text-amber-600">Sakit</p>
                        <p class="text-sm font-bold">{{ $stat['sakit'] }}</p>
                    </div>
                    <div class="bg-rose-100 text-rose-800 py-1.5 rounded-lg text-xs font-semibold">
                        <p class="text-[10px] text-rose-600">Alpa</p>
                        <p class="text-sm font-bold">{{ $stat['alpa'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- TAMPILAN DESKTOP & PRINT (Table View) -->
        <div class="hidden md:block overflow-x-auto print:block">
            <table class="w-full text-sm text-left text-slate-600 border-collapse">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center border print:border-slate-400 print:text-black">No</th>
                        <th class="px-4 py-3 w-36 text-center border print:border-slate-400 print:text-black">NISN</th>
                        <th class="px-4 py-3 border print:border-slate-400 print:text-black">Nama Siswa</th>
                        <th class="px-4 py-3 text-center w-24 border print:border-slate-400 print:text-black">Hadir (H)</th>
                        <th class="px-4 py-3 text-center w-24 border print:border-slate-400 print:text-black">Izin (I)</th>
                        <th class="px-4 py-3 text-center w-24 border print:border-slate-400 print:text-black">Sakit (S)</th>
                        <th class="px-4 py-3 text-center w-24 border print:border-slate-400 print:text-black">Alpa (A)</th>
                        <th class="px-4 py-3 text-center w-28 border print:border-slate-400 print:text-black">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 print:divide-slate-300">
                    @foreach($siswaList as $index => $siswa)
                    @php
                        $stat = $rekapData[$siswa->id] ?? ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0];
                        $namaSiswa = $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->nama ?? '-';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-4 py-3 text-center font-semibold text-slate-400 border print:border-slate-300 print:text-black">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-center font-mono text-xs text-slate-500 border print:border-slate-300 print:text-black">{{ $siswa->nisn ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold text-slate-800 border print:border-slate-300 print:text-black">{{ $namaSiswa }}</td>
                        <td class="px-4 py-3 text-center border print:border-slate-300">
                            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold print:bg-transparent print:p-0 print:text-black">
                                {{ $stat['hadir'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center border print:border-slate-300">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold print:bg-transparent print:p-0 print:text-black">
                                {{ $stat['izin'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center border print:border-slate-300">
                            <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold print:bg-transparent print:p-0 print:text-black">
                                {{ $stat['sakit'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center border print:border-slate-300">
                            <span class="inline-block px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold print:bg-transparent print:p-0 print:text-black">
                                {{ $stat['alpa'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-slate-800 border print:border-slate-300 print:text-black">
                            {{ $stat['total'] }} Hari
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center space-y-2 print:hidden">
        <span class="text-3xl">🔍</span>
        <p class="font-bold text-slate-700">Data Tidak Ditemukan</p>
        <p class="text-xs text-slate-500">Silakan pilih kelas dan periode bulan/tahun yang sesuai pada filter di atas.</p>
    </div>
    @endif

</div>
@endsection