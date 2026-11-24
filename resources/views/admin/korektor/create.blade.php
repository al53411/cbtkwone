@extends('layouts.admin')
@section('title', 'Tambah Korektor')
@section('page_title', 'Tambah Korektor')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Tambah Korektor</h1>
    <p class="text-gray-500 text-sm mt-1">Data korektor akan otomatis dibuatkan akun login dengan <strong>NIP sebagai username & password</strong>.</p>
</div>

@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border p-6 max-w-2xl">
    <form action="{{ route('admin.korektor.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Contoh: Siti Rahayu, S.Pd" required>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                <input type="text" name="nip" value="{{ old('nip') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Nomor Induk Pegawai" required>
                <p class="text-xs text-blue-600 mt-1"><i class="fa-solid fa-circle-info"></i> NIP digunakan sebagai <strong>username & password</strong> login.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NUPTK <span class="text-gray-400">(opsional)</span></label>
                <input type="text" name="nuptk" value="{{ old('nuptk') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Nomor Unik PTK">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
            <input type="text" name="mata_pelajaran" value="{{ old('mata_pelajaran') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="Contoh: Bahasa Indonesia" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas yang dikoreksi <span class="text-red-500">*</span></label>
            <select name="kelas_id[]" multiple required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 h-36">
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" @selected(in_array($k->id, old('kelas_id', [])))>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Tahan <kbd class="bg-gray-100 px-1 rounded">Ctrl</kbd> / <kbd class="bg-gray-100 px-1 rounded">Cmd</kbd> untuk memilih lebih dari satu kelas.</p>
        </div>

        {{-- Info box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700 flex gap-2">
            <i class="fa-solid fa-key mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Akun login otomatis dibuat:</p>
                <ul class="list-disc pl-4 mt-1 space-y-0.5 text-xs">
                    <li>Username: <strong>NIP</strong> yang diinput</li>
                    <li>Password: <strong>NIP</strong> yang diinput (korektor wajib ganti saat pertama login)</li>
                    <li>Role: <strong>guru</strong></li>
                </ul>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                <i class="fa-solid fa-save mr-1"></i> Simpan & Buat Akun
            </button>
            <a href="{{ route('admin.korektor.index') }}"
                class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
