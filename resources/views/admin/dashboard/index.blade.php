@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
{{-- Header & Salam Dinamis --}}
@php
    $hour = date('H');
    $salam = $hour < 11 ? 'Pagi' : ($hour < 15 ? 'Siang' : ($hour < 19 ? 'Sore' : 'Malam'));
@endphp

<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
            Selamat {{ $salam }}, {{ Auth::user()?->name ?? 'Admin' }}!
        </h1>
        <p class="text-gray-500 mt-1">
            Kelola seluruh data dan aktivitas aktif di
            {{ Auth::user()?->sekolah?->nama_sekolah ?? $profilSekolah?->nama_sekolah ?? 'Sekolah' }}.
        </p>
    </div>
    <div class="flex items-center gap-2 text-xs font-medium text-gray-600 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-lg border border-gray-200/80 shadow-sm self-start md:self-auto">
        <i class="fa-regular font-bold fa-calendar-days text-emerald-600"></i>
        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
    </div>
</div>

{{-- Ringkasan Statistik --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    {{-- Total Siswa --}}
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Siswa</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ number_format($total_siswa ?? 0) }}</h3>
            <p class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i> Siswa terdaftar
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl shrink-0">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
    </div>

    {{-- Total Guru --}}
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Guru</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ number_format($total_guru ?? 0) }}</h3>
            <p class="text-xs text-slate-500 font-medium">Tenaga pendidik</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl shrink-0">
            <i class="fa-solid fa-chalkboard-teacher"></i>
        </div>
    </div>

    {{-- Total Kelas --}}
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Kelas</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ number_format($total_kelas ?? 0) }}</h3>
            <p class="text-xs text-slate-500 font-medium">Rombongan belajar</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl shrink-0">
            <i class="fa-solid fa-school"></i>
        </div>
    </div>

    {{-- Status Sistem --}}
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status Sistem</p>
            <h3 class="text-xl font-bold text-emerald-600 flex items-center gap-2 mt-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                Normal
            </h3>
            <p class="text-xs text-gray-400 font-medium">Modul berjalan lancar</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl shrink-0">
            <i class="fa-solid fa-server"></i>
        </div>
    </div>
</div>

{{-- Konten Utama --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Fitur Akses Cepat --}}
    <div class="lg:col-span-2 bg-white/90 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Akses Cepat Fitur</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pintas menuju pengelolaan data utama</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Kelola Siswa --}}
            @if(Route::has('admin.siswa.index'))
            <a href="{{ route('admin.siswa.index') }}"
                class="p-4 border border-gray-200/80 rounded-xl hover:bg-blue-50/50 hover:border-blue-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm group-hover:text-blue-600 transition">Kelola Siswa</h4>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">Tambah, edit, atau mutasi data siswa aktif.</p>
                </div>
            </a>
            @endif

            {{-- Kelola Guru --}}
            @if(Route::has('admin.guru.index'))
            <a href="{{ route('admin.guru.index') }}"
                class="p-4 border border-gray-200/80 rounded-xl hover:bg-indigo-50/50 hover:border-indigo-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-id-card text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm group-hover:text-indigo-600 transition">Kelola Guru</h4>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">Atur data pendidik, jabatan, dan golongan.</p>
                </div>
            </a>
            @endif

            {{-- Kelola Kelas --}}
            @if(Route::has('admin.kelas.index'))
            <a href="{{ route('admin.kelas.index') }}"
                class="p-4 border border-gray-200/80 rounded-xl hover:bg-emerald-50/50 hover:border-emerald-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-door-open text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm group-hover:text-emerald-600 transition">Data Rombel & Kelas</h4>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">Atur pembagian kelas dan wali kelas.</p>
                </div>
            </a>
            @endif

            {{-- Pengaturan Profil Sekolah --}}
            @if(Route::has('admin.sekolah.index'))
            <a href="{{ route('admin.sekolah.index') }}"
                class="p-4 border border-gray-200/80 rounded-xl hover:bg-amber-50/50 hover:border-amber-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-lg group-hover:bg-amber-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-sliders text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm group-hover:text-amber-600 transition">Profil Sekolah</h4>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">Perbarui identitas dan informasi lembaga.</p>
                </div>
            </a>
            @endif

        </div>
    </div>

    {{-- Feed Aktivitas Sistem Dinamis --}}
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                <h2 class="text-lg font-bold text-gray-800">Aktivitas Terakhir</h2>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium">Log Sistem</span>
            </div>

            <div class="space-y-4">
                @forelse($aktivitas ?? [] as $item)
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium leading-snug">
                            {{ $item?->keterangan ?? $item?->deskripsi ?? 'Aktivitas Sistem' }}
                        </p>
                        <span class="text-xs text-gray-400 mt-0.5 inline-block">
                            {{ isset($item?->created_at) ? \Carbon\Carbon::parse($item->created_at)->diffForHumans() : 'Baru saja' }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-gray-700 font-medium">Sistem siap digunakan</p>
                        <span class="text-xs text-gray-400">Baru saja</span>
                    </div>
                </div>
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-gray-700 font-medium">Sesi login admin diverifikasi</p>
                        <span class="text-xs text-gray-400">Hari ini</span>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100">
            <a href="#" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center justify-center gap-1">
                Lihat Semua Log <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

</div>
@endsection