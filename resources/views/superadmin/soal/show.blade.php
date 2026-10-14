@extends('layouts.superadmin')

@section('title', 'Detail Soal')
@section('header', 'Detail Soal')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Detail Soal</h1>
            <p class="text-xs text-slate-500">Informasi lengkap pertanyaan dan pilihan jawaban.</p>
        </div>
        <a href="{{ route('superadmin.soal.index') }}" 
           class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-6">
        <div class="flex gap-4">
            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 font-semibold text-xs rounded-lg">Mapel: {{ $soal->mapel->nama_mapel ?? '-' }}</span>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 font-semibold text-xs rounded-lg">Kelas: {{ $soal->kelas->nama_kelas ?? '-' }}</span>
        </div>

        <div>
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pertanyaan</h2>
            <div class="p-4 bg-slate-50 rounded-xl text-slate-800 text-sm border border-slate-100">
                {!! nl2br(e($soal->soal)) !!}
            </div>
        </div>

        <div class="space-y-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pilihan Jawaban</h2>
            <div class="space-y-2">
                @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                    @if($soal->{'option_' . $opt})
                        <div class="p-3 rounded-xl border {{ $soal->kunci_jawaban == strtoupper($opt) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-100 text-slate-700' }} flex items-center justify-between text-xs">
                            <span class="font-bold uppercase w-8">{{ $opt }}.</span>
                            <span class="flex-1">{{ $soal->{'option_' . $opt} }}</span>
                            @if($soal->kunci_jawaban == strtoupper($opt))
                                <span class="px-2 py-0.5 bg-emerald-600 text-white text-[10px] font-bold rounded-md">Kunci Jawaban</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection