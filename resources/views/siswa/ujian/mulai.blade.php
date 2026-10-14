@extends('layouts.siswa')

@section('title', 'Ujian CAT - ' . ($ujian->nama_ujian ?? 'Ujian'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-4 space-y-4">
    <!-- Header Informasi Ujian -->
    <div class="bg-white border border-slate-300 rounded-lg p-4 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-sm tracking-wider shrink-0">
                CAT
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900">{{ $ujian->nama_ujian }}</h1>
                <p class="text-xs text-slate-600 mt-0.5">
                    Mapel: <span class="font-semibold text-slate-800">{{ $ujian->mapel->nama_mapel ?? '-' }}</span> | 
                    Peserta: <span class="font-semibold text-slate-800">{{ $siswa->nama ?? ($siswa->nama_lengkap ?? ($siswa->name ?? 'Siswa')) }}</span>
                </p>
            </div>
        </div>

        <!-- Timer Sisa Waktu -->
        <div class="bg-slate-900 text-amber-400 px-4 py-2 rounded-lg flex items-center gap-3 border border-slate-800 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock text-amber-400"></i>
                <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Sisa Waktu</span>
            </div>
            <span id="timer" class="text-base font-black font-mono tracking-wider">00:00:00</span>
        </div>
    </div>

    @php
        $adaSoal = isset($soals) && count($soals) > 0;
    @endphp

    @if(!$adaSoal)
        <div class="bg-white p-12 rounded-lg border border-slate-300 text-center space-y-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-3xl"></i>
            <h2 class="text-sm font-bold text-slate-800">Soal Belum Tersedia</h2>
            <p class="text-xs text-slate-600">Belum ada butir soal yang diinputkan untuk mata pelajaran ini.</p>
            <a href="{{ route('siswa.dashboard') }}" class="inline-block px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold rounded transition">Kembali ke Dashboard</a>
        </div>
    @else
        <!-- Grid Layout Utama: Kiri Soal (75%), Kanan Navigasi (25%) -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-start">
            
            <!-- Panel Soal Utama -->
            <div class="lg:col-span-3 bg-white border border-slate-300 rounded-lg p-6 shadow-xs flex flex-col justify-between min-h-[480px]">
                <div>
                    <!-- Bar Nomor Soal & Ragu-ragu -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-2 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900 bg-slate-100 px-3 py-1.5 rounded border border-slate-300">
                                SOAL NO: <span id="nomor-soal-aktif" class="text-blue-700 font-black text-sm">1</span> / {{ count($soals) }}
                            </span>
                            <span id="tipe-soal-label" class="text-xs font-semibold px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded border border-slate-300 uppercase">
                                Pilihan Ganda
                            </span>
                        </div>

                        <!-- Checkbox Ragu-ragu -->
                        <label class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded cursor-pointer transition select-none">
                            <input type="checkbox" id="ragu-ragu-checkbox" onclick="toggleRaguRagu(indeksAktif)" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                            <span class="text-xs font-bold text-amber-800">Ragu-ragu</span>
                        </label>
                    </div>

                    <!-- Teks Pertanyaan Soal -->
                    <div class="text-sm text-slate-900 font-medium leading-relaxed mb-6">
                        <div id="teks-soal" class="prose max-w-none text-slate-900">Memuat soal...</div>
                    </div>

                    <!-- Pilihan Opsi Jawaban -->
                    <div class="space-y-2.5" id="opsi-jawaban-container">
                        <!-- Render via JS -->
                    </div>
                </div>

                <!-- Tombol Navigasi Bawah -->
                <div class="flex items-center justify-between pt-4 mt-6 border-t border-slate-200">
                    <button type="button" id="btn-sebelumnya" onclick="gantiSoal(indeksAktif - 1)" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded transition disabled:opacity-40 disabled:cursor-not-allowed">
                        &larr; Sebelumnya
                    </button>
                    <button type="button" id="btn-selanjutnya" onclick="gantiSoal(indeksAktif + 1)" class="px-5 py-2 text-xs font-semibold text-white bg-blue-700 hover:bg-blue-800 rounded transition shadow-xs">
                        Selanjutnya &rarr;
                    </button>
                </div>
            </div>

            <!-- Panel Navigasi Nomor Soal (Kanan) -->
            <div class="bg-white border border-slate-300 rounded-lg p-4 shadow-xs space-y-4">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2 text-center">
                    Daftar Soal
                </h2>

                <!-- Grid Kotak Nomor Soal -->
                <div class="grid grid-cols-5 gap-1.5 max-h-[320px] overflow-y-auto p-1" id="navigasi-nomor-container">
                    @foreach ($soals as $index =>$soal)
                        <button type="button" id="nav-btn-{{ $index }}" onclick="gantiSoal({{ $index }})" 
                            class="w-10 h-10 rounded text-xs font-bold flex items-center justify-center border border-slate-300 bg-slate-100 text-slate-800 hover:bg-slate-200 transition">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>

                <!-- Legenda / Keterangan Status -->
                <div class="pt-3 border-t border-slate-200 space-y-2 text-[11px] text-slate-700">
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 bg-blue-700 rounded border border-blue-800 shrink-0"></div>
                        <span>Sudah Dijawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 bg-amber-500 rounded border border-amber-600 shrink-0"></div>
                        <span>Ragu-ragu</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3.5 h-3.5 bg-slate-100 rounded border border-slate-300 shrink-0"></div>
                        <span>Belum Dijawab</span>
                    </div>
                </div>

                <!-- Tombol Selesai Ujian -->
                <div class="pt-3 border-t border-slate-200">
                    <button type="button" onclick="konfirmasiSelesai()" class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded transition shadow-xs flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i> Selesai Ujian
                    </button>
                </div>
            </div>

        </div>
    @endif
</div>

@if($adaSoal)
<script>
    const daftarSoal = @json($soals);
    let indeksAktif = 0;
    let jawabanSiswa = {};
    let raguSiswa = {};

    function muatSoal(index) {
        if (index < 0 || index >= daftarSoal.length) return;
        indeksAktif = index;
        const soal = daftarSoal[index];

        document.getElementById('nomor-soal-aktif').textContent = index + 1;
        document.getElementById('teks-soal').innerHTML = soal.soal || soal.pertanyaan || 'Tidak ada teks soal.';
        document.getElementById('tipe-soal-label').textContent = soal.tipe ? soal.tipe.toUpperCase() : 'Pilihan Ganda';

        document.getElementById('ragu-ragu-checkbox').checked = !!raguSiswa[index];

        let containerOpsi = document.getElementById('opsi-jawaban-container');
        containerOpsi.innerHTML = '';

        let opsiList = [
            { key: 'A', teks: soal.opsi_a || soal.pilihan_a || soal.option_a || soal.a },
            { key: 'B', teks: soal.opsi_b || soal.pilihan_b || soal.option_b || soal.b },
            { key: 'C', teks: soal.opsi_c || soal.pilihan_c || soal.option_c || soal.c },
            { key: 'D', teks: soal.opsi_d || soal.pilihan_d || soal.option_d || soal.d },
            { key: 'E', teks: soal.opsi_e || soal.pilihan_e || soal.option_e || soal.e }
        ];

        opsiList.forEach(opsi => {
            if (opsi.teks) {
                let isChecked = jawabanSiswa[index] === opsi.key ? 'checked' : '';
                let borderClass = isChecked ? 'border-blue-700 bg-blue-50/70' : 'border-slate-300 bg-white hover:bg-slate-50';

                let html = `
                    <label class="flex items-center gap-3 p-2.5 rounded border ${borderClass} cursor-pointer transition select-none">
                        <input type="radio" name="pilihan_jawaban" value="${opsi.key}" ${isChecked} onclick="pilihJawaban(${index}, '${opsi.key}')" class="text-blue-700 focus:ring-blue-600 w-4 h-4">
                        <span class="text-xs font-bold text-slate-900 w-6 h-6 flex items-center justify-center bg-slate-100 rounded border border-slate-300 shrink-0">${opsi.key}</span>
                        <span class="text-xs text-slate-900 flex-1 font-normal leading-normal">${opsi.teks}</span>
                    </label>
                `;
                containerOpsi.insertAdjacentHTML('beforeend', html);
            }
        });

        document.getElementById('btn-sebelumnya').disabled = (index === 0);
        document.getElementById('btn-selanjutnya').style.display = (index === daftarSoal.length - 1) ? 'none' : 'inline-flex';

        updateTombolNavigasi();
    }

    function pilihJawaban(indexSoal, pilihan) {
        jawabanSiswa[indexSoal] = pilihan;
        updateTombolNavigasi();
        muatSoal(indexSoal);
    }

    function toggleRaguRagu(indexSoal) {
        let isChecked = document.getElementById('ragu-ragu-checkbox').checked;
        raguSiswa[indexSoal] = isChecked;
        updateTombolNavigasi();
    }

    function gantiSoal(index) {
        muatSoal(index);
    }

    function updateTombolNavigasi() {
        daftarSoal.forEach((_, idx) => {
            let btn = document.getElementById(`nav-btn-${idx}`);
            if (btn) {
                let hasAnswer = jawabanSiswa[idx] !== undefined;
                let isRagu = raguSiswa[idx];

                if (isRagu) {
                    btn.className = "w-10 h-10 rounded text-xs font-bold flex items-center justify-center border border-amber-600 bg-amber-500 text-white transition shadow-2xs";
                } else if (hasAnswer) {
                    btn.className = "w-10 h-10 rounded text-xs font-bold flex items-center justify-center border border-blue-800 bg-blue-700 text-white transition shadow-2xs";
                } else {
                    btn.className = "w-10 h-10 rounded text-xs font-bold flex items-center justify-center border border-slate-300 bg-slate-100 text-slate-800 hover:bg-slate-200 transition";
                }

                if (idx === indeksAktif) {
                    btn.classList.add('ring-2', 'ring-offset-1', 'ring-slate-950');
                } else {
                    btn.classList.remove('ring-2', 'ring-offset-1', 'ring-slate-950');
                }
            }
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        muatSoal(0);
    });

    let durasiMenit = {{ $ujian->durasi ?? 60 }};
    let timerdetik = durasiMenit * 60;
    const timerElement = document.getElementById('timer');

    const countdown = setInterval(() => {
        let jam = Math.floor(timerdetik / 3600);
        let menit = Math.floor((timerdetik % 3600) / 60);
        let detik = timerdetik % 60;

        timerElement.textContent = 
            String(jam).padStart(2, '0') + ':' + 
            String(menit).padStart(2, '0') + ':' + 
            String(detik).padStart(2, '0');

        if (timerdetik <= 0) {
            clearInterval(countdown);
            alert('Waktu ujian telah habis!');
            window.location.href = "{{ route('siswa.dashboard') }}";
        } else {
            timerdetik--;
        }
    }, 1000);

    function konfirmasiSelesai() {
        let jumlahTerjawab = Object.keys(jawabanSiswa).length;
        let totalSoal = daftarSoal.length;
        
        if (confirm(`Anda telah menjawab ${jumlahTerjawab} dari ${totalSoal} soal. Apakah Anda yakin ingin mengakhiri dan mengirimkan ujian ini?`)) {
            alert('Ujian berhasil diserahkan!');
            window.location.href = "{{ route('siswa.dashboard') }}";
        }
    }
</script>
@endif
@endsection