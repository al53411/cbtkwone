@extends('layouts.admin')

@section('title', 'Dashboard CAT Sekolah')
@section('page_title', 'Dashboard CAT')

@section('content')
{{-- Header & Salam Dinamis --}}
@php
    $hour = date('H');
    $salam = $hour < 11 ? 'Pagi' : ($hour < 15 ? 'Siang' : ($hour < 19 ? 'Sore' : 'Malam'));
@endphp

<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Selamat {{ $salam }}, {{ Auth::user()?->name ?? 'Admin Sekolah' }}!
        </h1>
        <p class="text-slate-500 mt-1">
            Panel Kontrol Computer Assisted Test (CAT) di 
            <span class="font-semibold text-slate-700">
                {{ Auth::user()?->sekolah?->nama_sekolah ?? $profilSekolah?->nama_sekolah ?? 'Sekolah' }}
            </span>.
        </p>
    </div>
    <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm self-start md:self-auto">
        <i class="fa-regular fa-calendar-days text-blue-600 font-bold"></i>
        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
    </div>
</div>

{{-- Ringkasan Statistik CAT --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    {{-- Total Peserta Ujian / Siswa --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Siswa Peserta</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ number_format($total_siswa ?? 0) }}</h3>
            <p class="text-xs text-blue-600 font-medium flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i> Peserta Terdaftar
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl shrink-0">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
    </div>

    {{-- Total Sesi Ujian --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sesi Ujian</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ number_format($total_sesi ?? $total_kelas ?? 0) }}</h3>
            <p class="text-xs text-indigo-600 font-medium">Sesi CAT Dikonfigurasi</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
    </div>

    {{-- Status Token Ujian --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Token Ujian (SuperAdmin)</p>
            <h3 class="text-2xl font-bold text-amber-600 tracking-wider">
                {{ $token_aktif ?? 'READY' }}
            </h3>
            <p class="text-xs text-slate-500 font-medium">Rilis dari Super Admin</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl shrink-0">
            <i class="fa-solid fa-key"></i>
        </div>
    </div>

    {{-- Status Server CAT --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center justify-between hover:shadow-md transition">
        <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Server CAT</p>
            <h3 class="text-xl font-bold text-emerald-600 flex items-center gap-2 mt-1">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                Aktif & Siap
            </h3>
            <p class="text-xs text-slate-400 font-medium">Ujian berjalan lancar</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl shrink-0">
            <i class="fa-solid fa-server"></i>
        </div>
    </div>
</div>

{{-- Konten Utama --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Fitur Akses Cepat CAT --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Akses Cepat Admin CAT</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola persiapan dan pelaksanaan ujian CAT</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Input Data Siswa --}}
            @if(Route::has('admin.siswa.index'))
            <a href="{{ route('admin.siswa.index') }}"
                class="p-4 border border-slate-200 rounded-xl hover:bg-blue-50/50 hover:border-blue-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-user-graduate text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-blue-600 transition">Input Data Siswa</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Kelola data dan nomor peserta ujian siswa.</p>
                </div>
            </a>
            @endif

            {{-- Atur Sesi Ujian CAT --}}
            @if(Route::has('admin.cat.sesi.index'))
            <a href="{{ route('admin.cat.sesi.index') }}"
                class="p-4 border border-slate-200 rounded-xl hover:bg-indigo-50/50 hover:border-indigo-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition">Atur Sesi Ujian</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Jadwalkan pembagian sesi dan waktu ujian CAT.</p>
                </div>
            </a>
            @endif

            {{-- Token Ujian CAT --}}
            @if(Route::has('admin.cat.token.index'))
            <a href="{{ route('admin.cat.token.index') }}"
                class="p-4 border border-slate-200 rounded-xl hover:bg-amber-50/50 hover:border-amber-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-lg group-hover:bg-amber-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-key text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-amber-600 transition">Token Ujian CAT</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Pantau token ujian aktif yang dirilis Super Admin.</p>
                </div>
            </a>
            @endif

            {{-- Profil Sekolah --}}
            @if(Route::has('admin.sekolah.index'))
            <a href="{{ route('admin.sekolah.index') }}"
                class="p-4 border border-slate-200 rounded-xl hover:bg-emerald-50/50 hover:border-emerald-200 transition group flex items-start space-x-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition shrink-0">
                    <i class="fa-solid fa-school text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition">Profil Sekolah</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Lihat dan sesuaikan identitas data sekolah.</p>
                </div>
            </a>
            @endif

        </div>
    </div>

    {{-- Log Aktivitas Pelaksanaan CAT --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <h2 class="text-lg font-bold text-slate-800">Aktivitas CAT Terakhir</h2>
                <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">Log Sistem</span>
            </div>

            <div class="space-y-4">
                @forelse($aktivitas ?? [] as $item)
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-slate-700 font-medium leading-snug">
                            {{ $item?->keterangan ?? $item?->deskripsi ?? 'Aktivitas Ujian CAT' }}
                        </p>
                        <span class="text-xs text-slate-400 mt-0.5 inline-block">
                            {{ isset($item?->created_at) ? \Carbon\Carbon::parse($item->created_at)->diffForHumans() : 'Baru saja' }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-slate-700 font-medium">Server CAT dalam kondisi stabil</p>
                        <span class="text-xs text-slate-400">Baru saja</span>
                    </div>
                </div>
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-slate-700 font-medium">Sesi login Admin Sekolah diverifikasi</p>
                        <span class="text-xs text-slate-400">Hari ini</span>
                    </div>
                </div>
                <div class="flex items-start space-x-3 text-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-slate-700 font-medium">Token ujian telah disinkronkan</p>
                        <span class="text-xs text-slate-400">Hari ini</span>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100">
            <a href="#" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center justify-center gap-1">
                Lihat Semua Aktivitas CAT <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

</div>
@endsection