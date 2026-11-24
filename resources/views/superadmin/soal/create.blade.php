@extends('layouts.superadmin')

@section('title', 'Tambah Bank Soal')
@section('header', 'Master Bank Soal')

@section('content')
    <div class="w-full">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Tambah Soal Baru</h1>
                <p class="text-xs text-slate-500">Pilih tipe soal (Pilihan Ganda, Menjodohkan, atau Esai) lalu lengkapi
                    detail butir soal.</p>
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

        <form action="{{ route('superadmin.soal.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-5">
            @csrf

            {{-- Baris 1: Tipe Soal, Kode Paket, & Bobot --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Soal <span
                            class="text-red-500">*</span></label>
                    <select name="tipe_soal" id="tipe_soal" onchange="switchTipeSoal()" required
                        class="w-full text-xs font-semibold rounded-xl border-slate-300 bg-white p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="pg" {{ old('tipe_soal', 'pg') === 'pg' ? 'selected' : '' }}>Pilihan Ganda (PG)</option>
                        <option value="menjodohkan" {{ old('tipe_soal') === 'menjodohkan' ? 'selected' : '' }}>Menjodohkan
                            (Pasangan Kiri - Kanan)</option>
                        <option value="esai" {{ old('tipe_soal') === 'esai' ? 'selected' : '' }}>Esai / Uraian</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Paket Soal (Opsional)</label>
                    <input type="text" name="kode_soal" value="{{ old('kode_soal') }}" placeholder="Contoh: MTK-KLS6-UTS"
                        class="w-full text-xs rounded-xl border-slate-300 bg-white p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Bobot Skor Butir Soal</label>
                    <input type="number" name="bobot_soal" value="{{ old('bobot_soal', 1) }}" min="0.5" max="100" step="0.5"
                        class="w-full text-xs rounded-xl border-slate-300 bg-white p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            {{-- Baris 2: Mata Pelajaran & Kelas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Mata Pelajaran <span
                            class="text-red-500">*</span></label>
                    <select name="mapel_id" required
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach (($mapels ?? []) as $mapel)
                            <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel ?? $mapel->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kelas <span
                            class="text-red-500">*</span></label>
                    <select name="kelas_id" required
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach (($kelass ?? []) as $kelas)
                            <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas ?? $kelas->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Pertanyaan / Instruksi Soal --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" id="label-pertanyaan">
                    Pertanyaan / Soal <span class="text-red-500">*</span>
                </label>
                <textarea name="soal" rows="4" required placeholder="Tuliskan pertanyaan atau instruksi soal di sini..."
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-3 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('soal') }}</textarea>
            </div>

            {{-- Upload Gambar Pendukung (Opsional untuk semua tipe soal) --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Gambar Pendukung Soal (Opsional)</label>
                <input type="file" name="gambar" accept="image/*"
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl bg-slate-50 p-2">
            </div>

            {{-- ========================================================
            SECTION 1: KHUSUS PILIHAN GANDA (PG)
            ======================================================== --}}
            <div id="section-pg" class="space-y-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Pilihan Jawaban <span
                            class="text-red-500">*</span></label>
                    <select name="jumlah_opsi" id="jumlah_opsi" onchange="toggleOpsi()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="3" {{ old('jumlah_opsi') == 3 ? 'selected' : '' }}>3 Pilihan (A, B, C)</option>
                        <option value="4" {{ old('jumlah_opsi') == 4 ? 'selected' : '' }}>4 Pilihan (A, B, C, D)</option>
                        <option value="5" {{ old('jumlah_opsi', 5) == 5 ? 'selected' : '' }}>5 Pilihan (A, B, C, D, E)
                        </option>
                    </select>
                </div>

                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pilihan Jawaban</h2>
                    @foreach (['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D', 'e' => 'Pilihan E'] as $key => $label)
                        <div class="option-item option-{{ $key }} flex items-center gap-3">
                            <span class="text-xs font-bold uppercase w-16 text-slate-500">{{ $label }}</span>
                            <input type="text" name="option_{{ $key }}" id="input_option_{{ $key }}"
                                value="{{ old('option_' . $key) }}" placeholder="Isi pilihan {{ strtoupper($key) }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    @endforeach
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kunci Jawaban PG <span
                            class="text-red-500">*</span></label>
                    <select name="kunci_jawaban" id="kunci_jawaban"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Pilih Kunci Jawaban --</option>
                        <option value="A" {{ old('kunci_jawaban') == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('kunci_jawaban') == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" id="opt_c" {{ old('kunci_jawaban') == 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" id="opt_d" {{ old('kunci_jawaban') == 'D' ? 'selected' : '' }}>D</option>
                        <option value="E" id="opt_e" {{ old('kunci_jawaban') == 'E' ? 'selected' : '' }}>E</option>
                    </select>
                </div>
            </div>

            {{-- ========================================================
            SECTION 2: KHUSUS MENJODOHKAN (PASANGAN KIRI - KANAN)
            ======================================================== --}}
            <div id="section-menjodohkan" class="hidden space-y-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Pasangan Pernyataan Kiri
                            &harr; Jawaban Kanan</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Masukkan pasangan yang benar secara sejajar. Saat ujian
                            berlangsung, pilihan kanan akan diacak otomatis oleh sistem.</p>
                    </div>
                    <button type="button" onclick="tambahPasangan()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                        <i class="fa-solid fa-plus"></i> Tambah Pasangan
                    </button>
                </div>

                <div id="pasangan-container" class="space-y-2.5">
                    @php
                        $oldKiri = old('pasangan_kiri', ['', '', '']);
                        $oldKanan = old('pasangan_kanan', ['', '', '']);
                    @endphp
                    @foreach ($oldKiri as $idx => $valKiri)
                        <div
                            class="pasangan-row grid grid-cols-1 md:grid-cols-12 gap-2 items-center p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="md:col-span-5">
                                <input type="text" name="pasangan_kiri[]" value="{{ $valKiri }}"
                                    placeholder="Premis / Soal Kiri #{{ $idx + 1 }}"
                                    class="input-kiri w-full text-xs rounded-lg border-slate-300 bg-white p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div class="md:col-span-1 text-center text-indigo-600 font-bold text-xs">
                                <i class="fa-solid fa-arrows-left-right"></i>
                            </div>
                            <div class="md:col-span-5">
                                <input type="text" name="pasangan_kanan[]" value="{{ $oldKanan[$idx] ?? '' }}"
                                    placeholder="Pasangan Jawaban Kanan #{{ $idx + 1 }}"
                                    class="input-kanan w-full text-xs rounded-lg border-emerald-300 bg-emerald-50/40 p-2.5 border focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div class="md:col-span-1 text-right">
                                <button type="button" onclick="hapusPasangan(this)"
                                    class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Pasangan">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ========================================================
            SECTION 3: KHUSUS ESAI / URAIAN
            ======================================================== --}}
            <div id="section-esai" class="hidden space-y-3 pt-3 border-t border-slate-100">
                <h2 class="text-xs font-bold text-amber-700 uppercase tracking-wider">Pedoman / Kunci Jawaban Esai</h2>
                <p class="text-[11px] text-slate-500">Tuliskan jawaban acuan atau poin-poin penilaian sebagai pedoman
                    guru/admin saat mengoreksi jawaban esai siswa.</p>
                <textarea name="kunci_esai" id="kunci_esai" rows="4"
                    placeholder="Tuliskan kunci/pedoman jawaban esai di sini (opsional)..."
                    class="w-full text-xs rounded-xl border-amber-200 bg-amber-50/30 p-3 border focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('kunci_esai') }}</textarea>
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
        function switchTipeSoal() {
            const tipe = document.getElementById('tipe_soal').value;
            const secPg = document.getElementById('section-pg');
            const secMenjodohkan = document.getElementById('section-menjodohkan');
            const secEsai = document.getElementById('section-esai');
            const kunciPg = document.getElementById('kunci_jawaban');
            const labelPertanyaan = document.getElementById('label-pertanyaan');

            secPg.classList.add('hidden');
            secMenjodohkan.classList.add('hidden');
            secEsai.classList.add('hidden');
            kunciPg.removeAttribute('required');

            if (tipe === 'menjodohkan') {
                secMenjodohkan.classList.remove('hidden');
                labelPertanyaan.innerHTML = 'Instruksi / Pengantar Soal Menjodohkan <span class="text-red-500">*</span>';
            } else if (tipe === 'esai') {
                secEsai.classList.remove('hidden');
                labelPertanyaan.innerHTML = 'Pertanyaan Soal Esai / Uraian <span class="text-red-500">*</span>';
            } else {
                secPg.classList.remove('hidden');
                kunciPg.setAttribute('required', 'required');
                labelPertanyaan.innerHTML = 'Pertanyaan Pilihan Ganda <span class="text-red-500">*</span>';
                toggleOpsi();
            }
        }

        function toggleOpsi() {
            let jumlah = document.getElementById('jumlah_opsi').value;
            let optionC = document.querySelector('.option-c');
            let optionD = document.querySelector('.option-d');
            let optionE = document.querySelector('.option-e');

            let optC = document.getElementById('opt_c');
            let optD = document.getElementById('opt_d');
            let optE = document.getElementById('opt_e');

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

        function tambahPasangan() {
            const container = document.getElementById('pasangan-container');
            const nomor = container.querySelectorAll('.pasangan-row').length + 1;
            const row = document.createElement('div');
            row.className = 'pasangan-row grid grid-cols-1 md:grid-cols-12 gap-2 items-center p-3 bg-slate-50 rounded-xl border border-slate-200';
            row.innerHTML = `
            <div class="md:col-span-5">
                <input type="text" name="pasangan_kiri[]" placeholder="Premis / Soal Kiri #${nomor}"
                    class="input-kiri w-full text-xs rounded-lg border-slate-300 bg-white p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div class="md:col-span-1 text-center text-indigo-600 font-bold text-xs">
                <i class="fa-solid fa-arrows-left-right"></i>
            </div>
            <div class="md:col-span-5">
                <input type="text" name="pasangan_kanan[]" placeholder="Pasangan Jawaban Kanan #${nomor}"
                    class="input-kanan w-full text-xs rounded-lg border-emerald-300 bg-emerald-50/40 p-2.5 border focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="md:col-span-1 text-right">
                <button type="button" onclick="hapusPasangan(this)"
                    class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Pasangan">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
            container.appendChild(row);
        }

        function hapusPasangan(btn) {
            const container = document.getElementById('pasangan-container');
            if (container.querySelectorAll('.pasangan-row').length <= 2) {
                alert('Soal menjodohkan minimal harus memiliki 2 pasangan.');
                return;
            }
            btn.closest('.pasangan-row').remove();
        }

        document.addEventListener("DOMContentLoaded", function () {
            switchTipeSoal();
        });
    </script>
@endsection