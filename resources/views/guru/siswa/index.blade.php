@extends('layouts.guru')

@section('title', $profilSekolah->nama_sekolah ?? 'SDN Kawu 1')

@section('content')
<div class="w-full bg-white text-slate-800 p-4 sm:p-6">

    <!-- Header & Total Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-5 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-gray-800">Daftar Siswa</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola dan lihat data siswa yang terdaftar</p>
        </div>
        <div class="flex items-center">
            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1.5 rounded-lg border border-blue-100 w-fit">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Total: <strong class="font-bold">{{ count($siswas) }}</strong> Siswa
            </span>
        </div>
    </div>

    <!-- Filter & Search Bar Section -->
    <div class="bg-white p-3.5 sm:p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('guru.siswa.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            
            <!-- Input Input Search -->
            <div class="sm:col-span-6 lg:col-span-7 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Cari nama siswa atau NISN..." 
                    class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <!-- Dropdown Filter Kelas -->
            <div class="sm:col-span-4 lg:col-span-3">
                <select name="kelas_id" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <option value="">-- Semua Kelas --</option>
                    @if(isset($listKelas))
                        @foreach($listKelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs sm:text-sm rounded-lg transition shadow-sm flex items-center justify-center gap-1">
                    <span>Cari</span>
                </button>
                @if(request()->has('search') || request()->has('kelas_id'))
                    <a href="{{ route('guru.siswa.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition text-xs" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Data Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">

        <!-- 1. TAMPILAN MOBILE (< sm) -->
        <div class="block sm:hidden divide-y divide-gray-100">
            @forelse ($siswas as $index => $siswa)
            <div class="p-3.5 hover:bg-gray-50/80 transition-colors">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <!-- Nomor Urut -->
                        <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100 mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        
                        <!-- Info Siswa -->
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 text-sm leading-snug truncate">
                                {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->nama ?? 'Tanpa Nama' }}
                            </h2>
                            <div class="flex items-center gap-1.5 mt-1 text-[11px] text-gray-500">
                                <span>NISN:</span>
                                <span class="font-mono font-medium text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded">
                                    {{ $siswa->nisn ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Badge Kelas -->
                    <span class="shrink-0 bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded border border-blue-100">
                        {{ $siswa->kelas->nama_kelas ?? '-' }}
                    </span>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500 text-xs">
                <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Tidak ditemukan data siswa yang sesuai.
            </div>
            @endforelse
        </div>

        <!-- 2. TAMPILAN DESKTOP/TABLET (>= sm) -->
        <div class="hidden sm:block overflow-x-auto w-full">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-center w-16">No</th>
                        <th scope="col" class="px-6 py-3.5">NISN</th>
                        <th scope="col" class="px-6 py-3.5">Nama Siswa</th>
                        <th scope="col" class="px-6 py-3.5 text-center">Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($siswas as $index => $siswa)
                    <tr class="bg-white hover:bg-gray-50/80 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 text-center">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-mono text-gray-600">{{ $siswa->nisn ?? '-' }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-800">
                            {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->nama ?? 'Tanpa Nama' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full border border-blue-200">
                                {{ $siswa->kelas->nama_kelas ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Tidak ditemukan data siswa yang sesuai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection