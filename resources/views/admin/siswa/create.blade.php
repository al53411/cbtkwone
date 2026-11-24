@php $isAjax = request()->ajax(); @endphp

@if(!$isAjax)
@section('title', 'Tambah Data Siswa')
@section('page_title', 'Siswa')

@section('content')
<div class="flat-card p-4 sm:p-6">
    <div class="mb-5 border-b border-slate-200 pb-3.5">
        <h1 class="text-lg sm:text-xl font-bold text-slate-900">Tambah Siswa Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Isi formulir di bawah ini dengan data siswa yang valid.</p>
    </div>
    @endif

    <div id="ajaxErrorBox" class="hidden mb-4 p-3 bg-rose-50 border border-rose-300 text-rose-800 rounded-md text-xs">
    </div>

    <form id="formSiswaModal" action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data"
        data-no-loader="true" class="space-y-4">
        @csrf

        <div class="flex items-center gap-2 pb-1 border-b border-slate-200">
            <span class="w-1.5 h-4 bg-blue-600 rounded-xs inline-block"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Informasi Siswa</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50/70 p-4 rounded-md border border-slate-200">

            <!-- NISN -->
            <div>
                <label for="nisn" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    NISN <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nisn" id="nisn" value="{{ old('nisn') }}" placeholder="Masukkan NISN siswa"
                    class="flat-input w-full px-3 py-2 font-mono @error('nisn') border-rose-500 @enderror" required>
                @error('nisn')
                    <p class="text-xs text-rose-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Nama Siswa -->
            <div>
                <label for="nama_siswa" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_siswa" id="nama_siswa" value="{{ old('nama_siswa') }}"
                    placeholder="Masukkan nama lengkap siswa"
                    class="flat-input w-full px-3 py-2 @error('nama_siswa') border-rose-500 @enderror" required>
                @error('nama_siswa')
                    <p class="text-xs text-rose-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Kelas -->
            <div>
                <label for="kelas_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    Kelas <span class="text-rose-500">*</span>
                </label>
                <select name="kelas_id" id="kelas_id"
                    class="flat-input w-full px-3 py-2 @error('kelas_id') border-rose-500 @enderror" required>
                    <option value="" disabled selected>-- Pilih Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
                @error('kelas_id')
                    <p class="text-xs text-rose-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Jenis Kelamin -->
            <div>
                <label for="jenis_kelamin"
                    class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <select name="jenis_kelamin" id="jenis_kelamin"
                    class="flat-input w-full px-3 py-2 @error('jenis_kelamin') border-rose-500 @enderror" required>
                    <option value="" disabled selected>-- Pilih Jenis Kelamin --</option>
                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
                @error('jenis_kelamin')
                    <p class="text-xs text-rose-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Alamat Lengkap -->
            <div class="md:col-span-2">
                <label for="alamat" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    Alamat Lengkap
                </label>
                <textarea name="alamat" id="alamat" rows="2" placeholder="Masukkan alamat domisili siswa"
                    class="flat-input w-full px-3 py-2 @error('alamat') border-rose-500 @enderror">{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="text-xs text-rose-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Foto Siswa (Pas Foto 3x4) -->
            <div class="md:col-span-2">
                <label for="foto" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    Pas Foto Siswa <span class="text-slate-400 font-normal normal-case">(Tampil di Kartu Ujian)</span>
                </label>
                <div class="flex items-center gap-3.5">
                    <div class="w-14 h-18 border border-dashed border-slate-300 rounded-md flex flex-col items-center justify-center bg-white text-slate-400 overflow-hidden shrink-0"
                        id="preview-foto-box">
                        <i class="fa-solid fa-user text-lg"></i>
                        <span class="text-[9px] mt-0.5 font-bold">3x4</span>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg,image/webp"
                            onchange="previewFotoSiswa(this)"
                            class="flat-input w-full px-3 py-1.5 text-xs @error('foto') border-rose-500 @enderror">
                        <p class="text-[11px] text-slate-500 mt-1">
                            Format: JPG, PNG, atau WEBP (Maks. 2MB). Disarankan rasio 3x4.
                        </p>
                    </div>
                </div>
                @error('foto')
                    <p class="text-xs text-rose-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

        </div>

        <!-- BUTTON ACTION -->
        <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            @if($isAjax)
                <button type="button" onclick="closeSiswaModal()"
                    class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-md transition cursor-pointer">
                    Batal
                </button>
            @else
                <a href="{{ route('admin.siswa.index') }}"
                    class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-md transition">
                    Batal
                </a>
            @endif

            <button type="submit" id="btnSubmitSiswa"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Data</span>
            </button>
        </div>
    </form>

    <script>
        function previewFotoSiswa(input) {
            const box = document.getElementById('preview-foto-box');
            if (box && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    box.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    box.classList.remove('border-dashed');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    @if(!$isAjax)
        </div>
        @endsection
    @endif