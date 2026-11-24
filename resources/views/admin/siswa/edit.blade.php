@extends('layouts.admin')

@section('title', 'Edit Data Siswa')
@section('page_title', 'Siswa')

@section('content')
<div class="w-full bg-white rounded-xl shadow-sm border p-4 sm:p-6 md:p-8 max-w-5xl mx-auto">
    <!-- Header Form -->
    <div class="mb-5 sm:mb-8 border-b pb-3 sm:pb-4">
        <h1 class="text-lg sm:text-2xl font-bold text-gray-900">Edit Data Siswa</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">
            Perbarui informasi dan data administrasi siswa yang terdaftar.
        </p>
    </div>

    <form action="{{ route('admin.siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5 sm:space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: DATA UTAMA SISWA -->
        <div>
            <div class="flex items-center space-x-2 mb-3 sm:mb-4">
                <span class="w-2 sm:w-2.5 h-4 sm:h-6 bg-blue-600 rounded-full inline-block"></span>
                <h2 class="text-sm sm:text-lg font-bold text-gray-800">Informasi Siswa</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-6 bg-gray-50/50 p-3.5 sm:p-6 rounded-xl border">
                
                <!-- NISN -->
                <div>
                    <label for="nisn" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                        NISN <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nisn" id="nisn" value="{{ old('nisn', $siswa->nisn) }}"
                        placeholder="Masukkan NISN siswa"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-xs sm:text-sm @error('nisn') border-red-500 @enderror"
                        required>
                    @error('nisn')
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Nama Siswa (Diselaraskan ke nama_siswa) -->
                <div>
                    <label for="nama_siswa" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_siswa" id="nama_siswa"
                        value="{{ old('nama_siswa', $siswa->nama_siswa) }}" placeholder="Masukkan nama lengkap siswa"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-xs sm:text-sm @error('nama_siswa') border-red-500 @enderror"
                        required>
                    @error('nama_siswa')
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Kelas -->
                <div>
                    <label for="kelas_id" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <select name="kelas_id" id="kelas_id"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-xs sm:text-sm @error('kelas_id') border-red-500 @enderror"
                        required>
                        <option value="" disabled>-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                        <option value="{{ $k->id }}"
                            {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="jenis_kelamin"
                        class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="jenis_kelamin"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-xs sm:text-sm @error('jenis_kelamin') border-red-500 @enderror"
                        required>
                        <option value="" disabled>-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                            Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                            Perempuan (P)</option>
                    </select>
                    @error('jenis_kelamin')
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Alamat Lengkap -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                        Alamat Lengkap
                    </label>
                    <textarea name="alamat" id="alamat" rows="2" placeholder="Masukkan alamat domisili siswa"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-xs sm:text-sm @error('alamat') border-red-500 @enderror">{{ old('alamat', $siswa->alamat) }}</textarea>
                    @error('alamat')
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Foto Siswa (Pas Foto 3x4) -->
                <div class="md:col-span-2">
                    <label for="foto" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                        Pas Foto Siswa (Tampil di Kartu Ujian)
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-20 border-2 {{ !empty($siswa->foto) ? 'border-blue-500' : 'border-dashed border-gray-300' }} rounded-lg flex flex-col items-center justify-center bg-white text-gray-400 overflow-hidden shrink-0 shadow-xs" id="preview-foto-box">
                            @if(!empty($siswa->foto))
                                <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto Siswa" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-user text-xl"></i>
                                <span class="text-[9px] mt-1 font-bold">3x4</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg,image/webp"
                                onchange="previewFoto(this)"
                                class="w-full px-3 py-2 sm:px-4 sm:py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-xs sm:text-sm @error('foto') border-red-500 @enderror">
                            <p class="text-[11px] text-gray-500 mt-1">
                                Format: JPG, PNG, atau WEBP. Maksimal 2MB. 
                                @if(!empty($siswa->foto))
                                    <span class="text-blue-600 font-semibold">(Biarkan kosong jika tidak ingin mengubah foto)</span>
                                @else
                                    <span>Disarankan pas foto resmi rasio 3x4.</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @error('foto')
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- BUTTON ACTION -->
        <div class="pt-3 sm:pt-6 border-t flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <a href="{{ route('admin.siswa.index') }}"
                class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-300 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-100 transition active:bg-gray-200">
                Batal
            </a>
            <button type="submit"
                class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition flex items-center justify-center gap-2 active:bg-blue-800">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    function previewFoto(input) {
        const box = document.getElementById('preview-foto-box');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                box.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                box.classList.remove('border-dashed');
                box.classList.add('border-blue-500');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection