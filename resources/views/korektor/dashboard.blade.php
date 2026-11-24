@extends('layouts.korektor')

@section('title', 'Dashboard Korektor')
@section('page_title', 'Dashboard Korektor')

@php
    $hour = now()->format('H');
    $salam = $hour < 11 ? 'Pagi' : ($hour < 15 ? 'Siang' : ($hour < 19 ? 'Sore' : 'Malam'));
    $totalSemua = ($belumDikoreksi ?? 0) + ($sudahDikoreksi ?? 0);
    $persenSelesai = $totalSemua > 0 ? round((($sudahDikoreksi ?? 0) / $totalSemua) * 100) : 0;
@endphp

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Selamat {{ $salam }}, {{ Auth::user()->name ?? 'Korektor' }}!</h1>
            <p class="text-slate-500 text-sm mt-1">Ringkasan progres koreksi jawaban uraian/esai siswa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1.5 text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-xl">
                Mapel: {{ $mapelDiampu }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum Dikoreksi</h3>
                <span
                    class="px-2.5 py-1 text-xs font-semibold {{ $belumDikoreksi > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }} rounded-full">
                    {{ $belumDikoreksi > 0 ? 'Perlu Tindakan' : 'Selesai' }}
                </span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800">{{ $belumDikoreksi }} <span
                    class="text-sm font-semibold text-slate-500">Jawaban</span></p>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sudah Dikoreksi</h3>
                <span
                    class="px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">{{ $persenSelesai }}%
                    Selesai</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800">{{ $sudahDikoreksi }} <span
                    class="text-sm font-semibold text-slate-500">Jawaban</span></p>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kelas &amp; Peserta</h3>
                <span
                    class="px-2.5 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">{{ $kelasDiampu->count() }}
                    Kelas</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800">{{ $totalSiswaDiampu }} <span
                    class="text-sm font-semibold text-slate-500">Siswa</span></p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800">Antrean Koreksi Terbaru</h3>
            <a href="{{ route('korektor.koreksi.index') }}"
                class="px-4 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">
                Mulai Koreksi &rarr;
            </a>
        </div>

        <div class="space-y-3">
            @forelse($daftarTugasKoreksi as $tugas)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-800">
                            {{ $tugas->judul ?? $tugas->nama_ujian ?? 'Paket Ujian #' . $tugas->id }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Menunggu penilaian esai</p>
                    </div>
                    <a href="{{ route('korektor.koreksi.show', $tugas->id) }}"
                        class="px-3.5 py-2 text-xs font-bold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Buka
                    </a>
                </div>
            @empty
                <p class="text-sm text-slate-500 italic">Tidak ada antrean jawaban esai yang belum dikoreksi.</p>
            @endforelse
        </div>
    </div>
@endsection