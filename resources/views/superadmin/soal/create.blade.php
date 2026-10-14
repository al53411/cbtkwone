@extends('layouts.superadmin')

@section('title', 'Master Bank Soal')
@section('header', 'Master Bank Soal')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tambah Soal Baru</h1>
            <p class="text-xs text-slate-500">Isi formulir di bawah ini untuk menambahkan soal ke bank soal.</p>
        </div>
        <a href="{{ route('superadmin.soal.index') }}" 
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

    <form action="{{ route('superadmin.soal.store') }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-5">
        @csrf

        {{-- Filter Mata Pelajaran & Kelas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                <select name="mapel_id" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach (($mapels ?? []) as $mapel)
                        <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama_mapel ?? $mapel->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                <select name="kelas_id" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach (($kelass ?? []) as $kelas)
                        <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas ?? $kelas->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Pertanyaan --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pertanyaan / Soal <span class="text-red-500">*</span></label>
            <textarea name="soal" rows="4" required placeholder="Tuliskan pertanyaan soal di sini..." 
                      class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-3 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('soal') }}</textarea>
        </div>

        {{-- Pilih Jumlah Pilihan Opsi Aktif --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Pilihan Jawaban <span class="text-red-500">*</span></label>
            <select name="jumlah_opsi" id="jumlah_opsi" onchange="toggleOpsi()" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="3" {{ old('jumlah_opsi') == 3 ? 'selected' : '' }}>3 Pilihan (A, B, C)</option>
                <option value="4" {{ old('jumlah_opsi') == 4 ? 'selected' : '' }}>4 Pilihan (A, B, C, D)</option>
                <option value="5" {{ old('jumlah_opsi', 5) == 5 ? 'selected' : '' }}>5 Pilihan (A, B, C, D, E)</option>
            </select>
        </div>

        {{-- Pilihan Jawaban (PG Dinamis) --}}
        <div class="space-y-3 pt-2">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pilihan Jawaban</h2>

            @foreach (['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D', 'e' => 'Pilihan E'] as $key => $label)
                <div class="option-item option-{{ $key }} flex items-center gap-3">
                    <span class="text-xs font-bold uppercase w-16 text-slate-500">{{ $label }}</span>
                    <input type="text" name="option_{{ $key }}" id="input_option_{{ $key }}" value="{{ old('option_' . $key) }}" placeholder="Isi pilihan {{ strtoupper($key) }}"
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            @endforeach
        </div>

        {{-- Kunci Jawaban Dinamis --}}
        <div class="pt-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kunci Jawaban <span class="text-red-500">*</span></label>
            <select name="kunci_jawaban" id="kunci_jawaban" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">-- Pilih Kunci Jawaban --</option>
                <option value="A" {{ old('kunci_jawaban') == 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ old('kunci_jawaban') == 'B' ? 'selected' : '' }}>B</option>
                <option value="C" id="opt_c" {{ old('kunci_jawaban') == 'C' ? 'selected' : '' }}>C</option>
                <option value="D" id="opt_d" {{ old('kunci_jawaban') == 'D' ? 'selected' : '' }}>D</option>
                <option value="E" id="opt_e" {{ old('kunci_jawaban') == 'E' ? 'selected' : '' }}>E</option>
            </select>
        </div>

        {{-- Tombol Submit --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('superadmin.soal.index') }}" 
               class="px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">
                Batal
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                Simpan Soal
            </button>
        </div>
    </form>
</div>

<script>
function toggleOpsi() {
    let jumlah = document.getElementById('jumlah_opsi').value;
    let optionC = document.querySelector('.option-c');
    let optionD = document.querySelector('.option-d');
    let optionE = document.querySelector('.option-e');
    
    let optC = document.getElementById('opt_c');
    let optD = document.getElementById('opt_d');
    let optE = document.getElementById('opt_e');

    let inputC = document.getElementById('input_option_c');
    let inputD = document.getElementById('input_option_d');
    let inputE = document.getElementById('input_option_e');

    if (jumlah == '3') {
        optionC.style.display = 'flex';
        optionD.style.display = 'none';
        optionE.style.display = 'none';
        
        optC.style.display = 'block';
        optD.style.display = 'none';
        optE.style.display = 'none';

        inputD.value = '';
        inputE.value = '';
    } else if (jumlah == '4') {
        optionC.style.display = 'flex';
        optionD.style.display = 'flex';
        optionE.style.display = 'none';
        
        optC.style.display = 'block';
        optD.style.display = 'block';
        optE.style.display = 'none';

        inputE.value = '';
    } else {
        optionC.style.display = 'flex';
        optionD.style.display = 'flex';
        optionE.style.display = 'flex';
        
        optC.style.display = 'block';
        optD.style.display = 'block';
        optE.style.display = 'block';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    toggleOpsi();
});
</script>
@endsection