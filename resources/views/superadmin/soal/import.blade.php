@extends('layouts.superadmin')

@section('title', 'Impor Soal')
@section('header', 'Impor Soal')

@section('content')
    <div class="w-full space-y-6">
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Import Soal Ujian (Multi-Tipe)</h2>
                <p class="text-xs text-slate-500">Unggah file Excel (.xlsx, .xls) atau CSV untuk mengimpor soal Pilihan
                    Ganda, Menjodohkan, dan Esai sekaligus.</p>
            </div>
            <a href="{{ route('superadmin.soal.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition self-start sm:self-auto">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Bank Soal
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl">
                <ul class="list-disc list-inside text-xs text-rose-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Download Template 2 Sheet -->
        <div
            class="p-4 bg-blue-50 border border-blue-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-file-excel text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-blue-900">Template Excel 2 Sheet (Data + Petunjuk Pengisian)</p>
                    <p class="text-[11px] text-blue-700 mt-0.5">Berisi sheet <span
                            class="font-semibold">Template_Soal</span> (contoh PG, Menjodohkan, & Esai) dan sheet <span
                            class="font-semibold">Petunjuk_Pengisian</span>.</p>
                </div>
            </div>
            <a href="{{ route('superadmin.soal.download-template') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-4 py-2.5 rounded-xl font-semibold transition inline-flex items-center justify-center gap-2 shadow-sm whitespace-nowrap shrink-0">
                <i class="fa-solid fa-download"></i> Unduh Template (.xlsx)
            </a>
        </div>

        <!-- Panduan Ringkas Format Kolom -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl space-y-1">
                <span class="inline-block px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[10px] uppercase">1.
                    Pilihan Ganda (pg)</span>
                <p class="text-[11px] text-slate-600 leading-relaxed">Isi kolom <code
                        class="text-indigo-600 font-semibold">tipe_soal = pg</code>, lengkapi <code
                        class="font-semibold">opsi_a</code> s/d <code class="font-semibold">opsi_e</code>, dan isi <code
                        class="font-semibold">kunci_jawaban</code> (<span class="font-semibold">A–E</span>).</p>
            </div>
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl space-y-1">
                <span
                    class="inline-block px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[10px] uppercase">2.
                    Menjodohkan (menjodohkan)</span>
                <p class="text-[11px] text-slate-600 leading-relaxed">Isi <code
                        class="text-indigo-600 font-semibold">tipe_soal = menjodohkan</code>, lalu tulis pasangan pada <code
                        class="font-semibold">opsi_a..e</code> dengan pemisah <code
                        class="font-bold text-slate-800">|</code> (contoh: <span class="italic">Segitiga | 3 Sisi</span>).
                </p>
            </div>
            <div class="p-3.5 bg-white border border-slate-200 rounded-xl space-y-1">
                <span class="inline-block px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-bold text-[10px] uppercase">3.
                    Esai / Uraian (esai)</span>
                <p class="text-[11px] text-slate-600 leading-relaxed">Isi <code
                        class="text-indigo-600 font-semibold">tipe_soal = esai</code>, kosongkan <code
                        class="font-semibold">opsi_a..e</code>, dan isi <code class="font-semibold">kunci_jawaban</code>
                    dengan pedoman skor serta kolom <code class="font-semibold">bobot</code>.</p>
            </div>
        </div>

        <!-- Form Upload -->
        <form action="{{ route('superadmin.soal.process-import') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-6 border border-slate-200 rounded-2xl shadow-xs space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kode Paket Soal Default (Opsional)</label>
                <input type="text" name="kode_soal" value="{{ old('kode_soal') }}"
                    placeholder="Contoh: MTK-KLS6-UTS (kosongkan jika sudah diisi di kolom Excel)"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <p class="text-[11px] text-slate-400 mt-1">Digunakan jika kolom <code class="font-semibold">kode_soal</code>
                    di dalam file Excel dibiarkan kosong.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih File Excel / CSV <span
                        class="text-rose-500">*</span></label>
                <input type="file" name="file" accept=".xlsx, .xls, .csv" required
                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-900 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 p-1.5">
                <p class="text-[11px] text-slate-400 mt-1">Format yang didukung: .xlsx, .xls, .csv (Maksimal ukuran: 5MB).
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <a href="{{ route('superadmin.soal.index') }}"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-upload"></i> Proses Import
                </button>
            </div>
        </form>
    </div>
@endsection