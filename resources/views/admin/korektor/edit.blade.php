@extends('layouts.admin')
@section('title', 'Edit Korektor')
@section('page_title', 'Edit Korektor')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Edit Korektor</h1>
    <p class="text-gray-500 text-sm mt-1">Perbarui data korektor dan kelas yang diampu.</p>
</div>

@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border p-6 max-w-2xl">
    <form action="{{ route('admin.korektor.update', $korektor->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Info akun login --}}
        @if($korektor->user)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-600 flex gap-2">
            <i class="fa-solid fa-circle-user mt-0.5 shrink-0 text-gray-400"></i>
            <div>
                <p class="font-semibold text-gray-700">Akun Login:</p>
                <p class="text-xs mt-0.5">Username (NIP): <strong>{{ $korektor->user->nip }}</strong> &nbsp;|&nbsp; Role: <strong>{{ $korektor->user->role }}</strong></p>
            </div>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $korektor->nama_lengkap) }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                <input type="text" name="nip" value="{{ old('nip', $korektor->nip) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    required>
                <p class="text-xs text-gray-400 mt-1">NIP digunakan sebagai username login.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NUPTK <span class="text-gray-400">(opsional)</span></label>
                <input type="text" name="nuptk" value="{{ old('nuptk', $korektor->nuptk) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
            <input type="text" name="mata_pelajaran" value="{{ old('mata_pelajaran', $korektor->mata_pelajaran) }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas yang dikoreksi <span class="text-red-500">*</span></label>
            <select name="kelas_id[]" multiple required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 h-36">
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" @selected(in_array($k->id, old('kelas_id', $selected)))>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Tahan <kbd class="bg-gray-100 px-1 rounded">Ctrl</kbd> / <kbd class="bg-gray-100 px-1 rounded">Cmd</kbd> untuk memilih lebih dari satu kelas.</p>
        </div>

        {{-- Reset Password --}}
        @if($korektor->user)
        <div class="border border-amber-200 bg-amber-50 rounded-lg p-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="reset_password" value="1" class="w-4 h-4 text-amber-500 rounded">
                <span class="text-sm font-medium text-amber-800">
                    <i class="fa-solid fa-rotate-left mr-1"></i>
                    Reset password ke NIP ({{ $korektor->nip }})
                </span>
            </label>
            <p class="text-xs text-amber-600 mt-1 pl-6">Centang jika korektor lupa password dan perlu direset.</p>
        </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                <i class="fa-solid fa-save mr-1"></i> Perbarui
            </button>
            <a href="{{ route('admin.korektor.index') }}"
                class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
