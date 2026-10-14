@extends('layouts.siswa')

@section('page_title', 'Dashboard Siswa - CBT')
@section('header')

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <!-- Sambutan & Profil Singkat Siswa (Menggunakan Background Solid Biru Tua Profesional) -->
    <div class="bg-slate-900 border border-slate-800 p-6 md:p-8 rounded-2xl text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-1.5">
            <span class="inline-flex items-center px-3 py-1 bg-blue-600/30 border border-blue-500/30 text-blue-300 rounded-lg text-xs font-semibold tracking-wide">
                <i class="fa-solid fa-graduation-cap mr-1.5"></i> Siswa Peserta CBT
            </span>
            @php
                $namaSiswa = $siswa->nama ?? ($siswa->nama_lengkap ?? ($siswa->name ?? 'Siswa'));
            @endphp
            <h1 class="text-2xl font-bold tracking-tight text-white">Selamat Datang, {{ $namaSiswa }}!</h1>
            <p class="text-xs text-slate-300 flex flex-wrap items-center gap-2 pt-1">
                <span>NISN: <b class="text-white">{{ $siswa->nisn ?? '-' }}</b></span>
                <span class="text-slate-600">•</span>
                <span>Kelas: <b class="text-white">{{ $siswa->kelas->nama_kelas ?? 'Belum ditentukan' }}</b></span>
            </p>
        </div>
        <div>
            <form action="{{ route('siswa.logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-power-off"></i> Keluar Sistem
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Notifikasi Error -->
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-3 shadow-2xs">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Alert Notifikasi Sukses -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-3 shadow-2xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Form Input Token Ujian -->
    <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-5">
        <div>
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Mulai Mengerjakan Ujian</h2>
            <p class="text-xs text-slate-600 mt-0.5">Masukkan token ujian unik yang diberikan oleh pengawas atau proktor ruang ujian Anda.</p>
        </div>

        <form action="{{ route('siswa.konfirmasi') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Token Ujian</label>
                <div class="relative max-w-md">
                    <!-- Icon Kunci dengan posisi z-index dan center yang aman -->
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 z-10">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <!-- Input dengan pl-11 agar teks tidak menimpa ikon kunci -->
                    <input type="text" name="token" required value="{{ old('token') }}" placeholder="CONTOH: A7X9M2" 
                        class="w-full text-sm font-mono uppercase tracking-widest rounded-xl border border-slate-300 bg-slate-50 pl-11 pr-4 py-3 text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition @error('token') border-rose-500 bg-rose-50/30 @enderror"
                        maxlength="10" autocomplete="off">
                </div>
                @error('token')
                    <span class="text-xs text-rose-600 mt-1.5 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white font-bold text-xs rounded-xl transition shadow-xs inline-flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Ujian Sekarang
            </button>
        </form>
    </div>

    <!-- Informasi Daftar Ujian Aktif Hari Ini -->
    <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Jadwal Ujian Aktif</h2>
            <span class="text-[11px] text-slate-500 font-medium">Real-time status</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100 text-slate-700 uppercase tracking-wider font-bold border-b border-slate-300">
                    <tr>
                        <th class="py-3 px-4">Nama Ujian</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4 text-center">Durasi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse ($ujianAktif as $ujian)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $ujian->nama_ujian }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-md font-semibold">
                                    {{ $ujian->mapel->nama_mapel ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-slate-800 font-semibold">{{ $ujian->durasi ?? '-' }} Menit</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 border border-blue-200 rounded-md font-extrabold uppercase tracking-wide">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 font-normal italic">
                                Belum ada jadwal ujian yang aktif saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection