@extends('layouts.superadmin')

@section('title', 'Master Bank Soal')
@section('header', 'Master Bank Soal')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tambah Kelas Baru</h1>
            <p class="text-xs text-slate-500">Tambahkan data kelas baru ke dalam sistem.</p>
        </div>
        <a href="{{ route('superadmin.kelas.index') }}" 
           class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
            &larr; Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <ul class="list-disc list-inside text-xs text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.kelas.store') }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-5">
        @csrf

        {{-- Nama Kelas --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Kelas <span class="text-red-500">*</span></label>
            <input type="text" name="nama_kelas" value="{{ old('nama_kelas') }}" required placeholder="Contoh: VII A / X IPA 1"
                   class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        {{-- Tingkat --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tingkat / Tingkatan</label>
            <input type="text" name="tingkat" value="{{ old('tingkat') }}" placeholder="Contoh: 7 / 10 / X"
                   class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        {{-- Tombol Submit --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('superadmin.kelas.index') }}" 
               class="px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">
                Batal
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                Simpan Kelas
            </button>
        </div>
    </form>
</div>
@endsection