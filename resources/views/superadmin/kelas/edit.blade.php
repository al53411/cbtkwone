@extends('layouts.superadmin')

@section('title', 'Edit Kelas')
@section('header', 'Edit Kelas')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-slate-800">Edit Data Kelas</h1>
        <p class="text-xs text-slate-500">Perbarui informasi tingkat dan nama kelas.</p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <form action="{{ route('superadmin.kelas.update', $kelas->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Nama Kelas --}}
            <div>
                <label for="nama_kelas" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Kelas</label>
                <input type="text" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required
                       class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-3 border focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('nama_kelas') border-red-500 @enderror"
                       placeholder="Contoh: 1 A, 7 B, XII IPA 1">
                @error('nama_kelas')
                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tingkat --}}
            <div>
                <label for="tingkat" class="block text-xs font-semibold text-slate-700 mb-1.5">Tingkat (Opsional)</label>
                <input type="text" id="tingkat" name="tingkat" value="{{ old('tingkat', $kelas->tingkat) }}"
                       class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-3 border focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('tingkat') border-red-500 @enderror"
                       placeholder="Contoh: 1, 7, 10">
                @error('tingkat')
                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('superadmin.kelas.index') }}" 
                   class="px-4 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-200 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection