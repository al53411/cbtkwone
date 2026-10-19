@extends('layouts.superadmin')

@section('title', 'Impor Soal')
@section('header', 'Impor Soal')

@section('content')
<div class="w-full">
    <div>
        <h2 class="text-lg font-bold text-slate-800">Import Soal Ujian</h2>
        <p class="text-xs text-slate-500">Unggah file Excel (.xlsx) atau CSV sesuai dengan format template yang disediakan.</p>
    </div>

    <!-- Tombol Download Template -->
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-file-excel text-blue-600 text-2xl"></i>
            <div>
                <p class="text-xs font-semibold text-blue-900">Format Template Import</p>
                <p class="text-[11px] text-blue-700">Unduh template agar sesuai dengan format database.</p>
            </div>
        </div>
        <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-lg font-semibold transition">
            <i class="fa-solid fa-download"></i> Unduh Template
        </a>
    </div>

    <!-- Form Upload -->
    <form action="{{ route('superadmin.soal.process-import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih File Excel / CSV</label>
            <input type="file" name="file" accept=".xlsx, .csv" required
                class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-900 border border-slate-200 rounded-lg cursor-pointer">
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <a href="{{ route('superadmin.soal.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg">Batal</a>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                <i class="fa-solid fa-upload mr-1"></i> Proses Import
            </button>
        </div>
    </form>
</div>
@endsection