<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian CAT - {{ $ujian->nama_ujian ?? 'Ujian Seleksi' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

@php
    $adaSoal = isset($soals) && count($soals) > 0;
    $acakJawabanAktif = method_exists($ujian, 'isJawabanAcak')
        ? $ujian->isJawabanAcak()
        : (bool) ($ujian->acak_jawaban ?? false);

    // Hitung sisa waktu akurat jika controller mengirim $sisaDetik atau $pengerjaan
    $sisaWaktuDetik = null;
    if (isset($sisaDetik) && is_numeric($sisaDetik)) {
        $sisaWaktuDetik = max(0, (int) $sisaDetik);
    } elseif (isset($pengerjaan) && !empty($pengerjaan->waktu_mulai)) {
        $akhirPengerjaan = \Carbon\Carbon::parse($pengerjaan->waktu_mulai)->addMinutes((int) ($ujian->durasi ?? 90));
        if (!empty($ujian->waktu_selesai)) {
            $batasSesi = \Carbon\Carbon::parse($ujian->waktu_selesai);
            if ($batasSesi->lt($akhirPengerjaan)) {
                $akhirPengerjaan = $batasSesi;
            }
        }
        $sisaWaktuDetik = max(0, (int) now()->diffInSeconds($akhirPengerjaan, false));
    } else {
        $sisaWaktuDetik = max(60, ((int) ($ujian->durasi ?? 90)) * 60);
    }
@endphp

<body class="bg-slate-100 min-h-screen flex flex-col antialiased">

    <!-- TOP HEADER / NAVBAR (Gaya Resmi CAT BKN) -->
    <header class="bg-white border-b border-slate-300 px-6 py-3 shadow-xs sticky top-0 z-40">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">

            <!-- Kiri: Logo & Judul Ujian -->
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <div
                        class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center font-black text-white text-lg shadow-sm overflow-hidden border border-blue-600">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-slate-900 text-sm tracking-wider">MAHESAPP</span>
                            <span class="text-[10px] text-slate-500 font-semibold border-l border-slate-300 pl-1.5">CBT
                                CAT System</span>
                        </div>
                        <p class="text-xs font-bold text-blue-900 tracking-tight uppercase">
                            {{ $ujian->nama_ujian ?? 'Ujian Sekolah' }}
                            &bull; <span
                                class="text-slate-700 font-semibold">{{ $ujian->mapel->nama_mapel ?? '' }}</span>
                            @if (isset($ujian->kelas->nama_kelas))
                                &bull; <span class="text-emerald-700 font-semibold">Kelas
                                    {{ $ujian->kelas->nama_kelas }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tengah/Kanan: Identitas Peserta & Timer -->
            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                <div class="text-right hidden sm:block">
                    <h2 class="font-semibold text-gray-900 text-sm leading-snug truncate">
                        {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->name ?? '-' }}
                    </h2>
                    <div class="text-xs text-gray-600 mt-0.5 flex items-center justify-end gap-1.5">
                        <span>NISN:</span>
                        <span
                            class="font-mono font-medium text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">
                            {{ $siswa->nisn ?? ($siswa->nis ?? '-') }}
                        </span>
                    </div>
                </div>

                <div
                    class="bg-white border-2 border-slate-300 px-4 py-1.5 rounded-lg flex items-center gap-3 shadow-inner">
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block leading-none">Sisa
                            Waktu</span>
                        <span id="timer"
                            class="text-xl font-black font-mono text-slate-900 tracking-wider">--:--:--</span>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="max-w-7xl w-full mx-auto p-4 md:p-6 flex-1">
        @if (!$adaSoal)
            <div class="bg-white p-12 rounded-lg border border-slate-300 text-center space-y-3 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-3xl"></i>
                <h2 class="text-sm font-bold text-slate-800">Soal Belum Tersedia</h2>
                <p class="text-xs text-slate-600">Belum ada butir soal yang diinputkan untuk ujian ini.</p>
                <a href="{{ route('siswa.dashboard') }}"
                    class="inline-block px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold rounded transition">Kembali
                    ke Dashboard</a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- KOLOM KIRI: PANEL SOAL & JAWABAN (Span 8) -->
                <div
                    class="lg:col-span-8 bg-white border border-slate-300 rounded-lg shadow-sm flex flex-col justify-between min-h-[580px] p-6">
                    <div>
                        <!-- Header Kategori / Nomor Soal & Badge Tipe -->
                        <div class="border-b border-slate-200 pb-3 mb-5 flex flex-wrap items-center justify-between gap-2">
                            <h2 id="header-kategori-soal" class="text-base font-extrabold text-slate-900 uppercase">
                                SOAL NO. <span id="nomor-soal-teks">1</span> -
                                {{ strtoupper($ujian->mapel->nama_mapel ?? 'TES UJIAN KOMPETENSI') }}
                            </h2>
                            <div class="flex items-center gap-2">
                                <span id="status-sync-jawaban" class="text-[11px] font-semibold text-slate-400"></span>
                                <span id="badge-tipe-soal"
                                    class="px-2.5 py-1 rounded text-[11px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                    PILIHAN GANDA
                                </span>
                            </div>
                        </div>

                        <!-- Teks Pertanyaan Soal -->
                        <div class="text-sm md:text-base text-slate-900 font-medium leading-relaxed mb-5">
                            <div id="teks-soal" class="prose max-w-none text-slate-900">Memuat soal...</div>
                        </div>

                        <!-- Wadah Gambar Soal (Jika Ada) -->
                        <div id="gambar-soal-container" class="mb-6 hidden">
                            <img id="gambar-soal-el" src="" alt="Gambar Soal"
                                class="max-h-72 rounded-lg border border-slate-200 object-contain shadow-xs">
                        </div>

                        <!-- Wadah Jawaban Dinamis (PG / Menjodohkan / Esai) -->
                        <div class="space-y-3 mb-6" id="opsi-jawaban-container">
                            <!-- Render via JavaScript -->
                        </div>
                    </div>

                    <!-- Bar Tombol Aksi Bawah -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-200 mt-6">
                        <button type="button" id="btn-sebelumnya" onclick="gantiSoal(indeksAktif - 1)"
                            class="px-5 py-2.5 text-xs md:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded shadow-2xs transition disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                            SEBELUMNYA
                        </button>

                        <button type="button" id="btn-ragu" onclick="toggleRaguRagu(indeksAktif)"
                            class="px-6 py-2.5 text-xs md:text-sm font-bold text-slate-900 bg-amber-400 hover:bg-amber-500 rounded shadow-2xs transition cursor-pointer">
                            RAGU-RAGU
                        </button>

                        <button type="button" id="btn-selanjutnya" onclick="gantiSoal(indeksAktif + 1)"
                            class="px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 rounded shadow-2xs transition cursor-pointer">
                            SELANJUTNYA
                        </button>
                    </div>
                </div>

                <!-- KOLOM KANAN: DAFTAR SOAL & LEGENDA STATUS (Span 4) -->
                <div class="lg:col-span-4 space-y-4">

                    <div class="bg-white border border-slate-300 rounded-lg p-4 shadow-sm space-y-2.5">
                        <p class="text-xs font-bold text-slate-800 border-b border-slate-200 pb-1.5">Status Soal:</p>

                        <div class="flex items-center gap-3 text-xs text-slate-700">
                            <div class="w-5 h-5 bg-emerald-600 rounded shrink-0 shadow-2xs"></div>
                            <span class="font-medium">Sudah Dijawab</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-700">
                            <div class="w-5 h-5 bg-white border border-slate-300 rounded shrink-0 shadow-2xs"></div>
                            <span class="font-medium">Belum Dijawab</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-700">
                            <div class="w-5 h-5 bg-amber-400 rounded shrink-0 shadow-2xs"></div>
                            <span class="font-medium">Ragu-ragu</span>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-300 rounded-lg p-4 shadow-sm space-y-3">
                        <h3
                            class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">
                            Daftar Soal
                        </h3>

                        <div class="grid grid-cols-5 gap-2 max-h-[360px] overflow-y-auto p-1.5 border border-slate-200 rounded bg-slate-50"
                            id="navigasi-nomor-container">
                            @foreach ($soals as $index => $soal)
                                <button type="button" id="nav-btn-{{ $index }}" onclick="gantiSoal({{ $index }})"
                                    class="w-full aspect-square rounded text-xs font-bold flex items-center justify-center border border-slate-300 bg-white text-slate-800 hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <div class="pt-2">
                            <button type="button" id="btn-selesai-ujian" onclick="konfirmasiSelesai()"
                                class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-check-circle"></i> SELESAI UJIAN
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        @endif
    </main>

    <!-- MODAL JEDA 15 DETIK SAAT PINDAH TIPE SOAL -->
    <div id="modal-transisi-tipe"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/75 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl border border-slate-300 overflow-hidden">
            <div class="bg-blue-800 px-5 py-3.5 text-white flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-hourglass-half"></i> PERPINDAHAN MODEL SOAL
                </span>
                <span id="modal-badge-tipe" class="px-2.5 py-0.5 rounded bg-white/20 text-[11px] font-black uppercase">
                    ESAI
                </span>
            </div>
            <div class="p-6 text-center space-y-4">
                <div
                    class="mx-auto w-20 h-20 rounded-full bg-blue-50 border-4 border-blue-700 flex flex-col items-center justify-center">
                    <span id="modal-countdown" class="text-2xl font-black text-blue-800 leading-none">15</span>
                    <span class="text-[10px] font-bold text-blue-600 uppercase mt-0.5">detik</span>
                </div>
                <div class="space-y-1.5">
                    <h4 id="modal-judul-perintah" class="text-sm font-extrabold text-slate-900">
                        Instruksi Model Soal Berikutnya
                    </h4>
                    <p id="modal-isi-perintah"
                        class="text-xs text-slate-700 leading-relaxed bg-amber-50 border border-amber-200 rounded-lg p-3.5 text-left">
                        Silakan baca petunjuk pengerjaan terlebih dahulu.
                    </p>
                </div>
                <p class="text-[11px] text-slate-400">
                    Soal akan terbuka otomatis setelah hitungan mundur selesai.
                </p>
            </div>
        </div>
    </div>

    <footer class="py-3 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
        &copy; {{ date('Y') }} Copyright: MahesAPP. All rights reserved.
    </footer>

    @if ($adaSoal)
        <script>
            const daftarSoal = @json($soals);
            const jawabanServer = @json($jawabanTersimpan ?? []);
            const SISWA_ID = {{ (int) ($siswa->id ?? 1) }};
            const UJIAN_ID = "{{ $ujian->id }}";
            const ACAK_JAWABAN_AKTIF = @json($acakJawabanAktif);

            const KEY_JAWABAN = `cbt_jawaban_${SISWA_ID}_${UJIAN_ID}`;
            const KEY_RAGU = `cbt_ragu_${SISWA_ID}_${UJIAN_ID}`;

            let indeksAktif = 0;
            let sedangJedaTipe = false;
            let intervalJedaTipe = null;
            let debounceEsaiTimer = null;
            let sedangSubmitSelesai = false;
            window._aktifKiriMenjodohkan = null;

            let jawabanLocal = JSON.parse(localStorage.getItem(KEY_JAWABAN)) || {};
            let raguSiswa = JSON.parse(localStorage.getItem(KEY_RAGU)) || {};

            let jawabanSiswa = { ...jawabanLocal };
            if (jawabanServer && typeof jawabanServer === 'object') {
                daftarSoal.forEach((soal, idx) => {
                    if (jawabanSiswa[idx] === undefined && jawabanServer[soal.id] !== undefined && jawabanServer[soal.id] !== null) {
                        jawabanSiswa[idx] = jawabanServer[soal.id];
                    }
                });
            }

            const PETUNJUK_TIPE = {
                pg: {
                    label: 'PILIHAN GANDA',
                    badgeClass: 'px-2.5 py-1 rounded text-[11px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-200',
                    judul: 'Perhatian! Anda beralih ke Soal Pilihan Ganda',
                    perintah: 'Pilih satu jawaban yang paling tepat. Jawaban akan tersimpan otomatis.'
                },
                menjodohkan: {
                    label: 'MENJODOHKAN',
                    badgeClass: 'px-2.5 py-1 rounded text-[11px] font-extrabold uppercase bg-indigo-100 text-indigo-800 border border-indigo-200',
                    judul: 'Perhatian! Anda beralih ke Soal Menjodohkan',
                    perintah: 'Pasangkan setiap pernyataan di sisi kiri dengan pilihan jawaban yang sesuai di sisi kanan. Lengkapi seluruh pasangan sebelum berpindah soal.'
                },
                esai: {
                    label: 'ESAI / URAIAN',
                    badgeClass: 'px-2.5 py-1 rounded text-[11px] font-extrabold uppercase bg-amber-100 text-amber-900 border border-amber-300',
                    judul: 'Perhatian! Anda beralih ke Soal Esai / Uraian',
                    perintah: 'Tuliskan jawaban uraian Anda dengan jelas, runtut, dan lengkap pada kotak jawaban esai yang tersedia.'
                }
            };

            function escapeHtmlText(str) {
                const div = document.createElement('div');
                div.textContent = str ?? '';
                return div.innerHTML;
            }

            function setStatusSync(text, isError = false) {
                const el = document.getElementById('status-sync-jawaban');
                if (!el) return;
                el.textContent = text;
                el.className = isError
                    ? 'text-[11px] font-semibold text-amber-600'
                    : 'text-[11px] font-semibold text-emerald-600';
            }

            function normalisasiTipe(soal) {
                let raw = (soal.tipe_soal || soal.tipe || 'pg').toString().toLowerCase().trim();
                if (raw === 'essay' || raw === 'uraian') return 'esai';
                if (raw === 'jodohkan' || raw === 'match') return 'menjodohkan';
                return ['pg', 'menjodohkan', 'esai'].includes(raw) ? raw : 'pg';
            }

            function parsePasangan(soal) {
                let data = soal.pasangan_menjodohkan;
                if (!data) return [];
                if (typeof data === 'string') {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        return [];
                    }
                }
                return Array.isArray(data) ? data : [];
            }

            function acakDeterministik(arr, seed) {
                let copy = [...arr];
                let s = seed % 2147483647;
                if (s <= 0) s += 2147483646;
                for (let i = copy.length - 1; i > 0; i--) {
                    s = (s * 16807) % 2147483647;
                    let j = s % (i + 1);
                    [copy[i], copy[j]] = [copy[j], copy[i]];
                }
                return copy;
            }

            function sudahTerjawab(idx) {
                let soal = daftarSoal[idx];
                if (!soal) return false;
                let tipe = normalisasiTipe(soal);
                let ans = jawabanSiswa[idx];

                if (tipe === 'menjodohkan') {
                    let pairs = parsePasangan(soal);
                    if (pairs.length === 0 || !ans || typeof ans !== 'object') return false;
                    let terisi = pairs.filter(p => ans[p.kiri] && ans[p.kiri].toString().trim() !== '').length;
                    return terisi === pairs.length;
                }

                if (tipe === 'esai') {
                    return typeof ans === 'string' && ans.trim().length > 0;
                }

                return ans !== undefined && ans !== null && ans.toString().trim() !== '';
            }

            function simpanKeLocalStorage() {
                localStorage.setItem(KEY_JAWABAN, JSON.stringify(jawabanSiswa));
                localStorage.setItem(KEY_RAGU, JSON.stringify(raguSiswa));
            }

            function kirimKeServer(indexSoal, payloadJawaban) {
                let soalId = daftarSoal[indexSoal].id;
                setStatusSync('Menyimpan...');

                fetch("{{ route('siswa.ujian.simpan-jawaban') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ujian_id: UJIAN_ID,
                        soal_id: soalId,
                        jawaban: payloadJawaban
                    })
                })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(() => setStatusSync('Tersimpan'))
                    .catch(() => setStatusSync('Tersimpan lokal (offline)', true));
            }

            function muatSoal(index) {
                if (index < 0 || index >= daftarSoal.length) return;
                indeksAktif = index;
                const soal = daftarSoal[index];
                const tipe = normalisasiTipe(soal);
                const infoTipe = PETUNJUK_TIPE[tipe] || PETUNJUK_TIPE.pg;

                document.getElementById('nomor-soal-teks').textContent = index + 1;
                document.getElementById('teks-soal').innerHTML = soal.soal || soal.pertanyaan || 'Tidak ada teks soal.';

                const badgeEl = document.getElementById('badge-tipe-soal');
                badgeEl.textContent = infoTipe.label;
                badgeEl.className = infoTipe.badgeClass;

                let containerGambar = document.getElementById('gambar-soal-container');
                let elGambar = document.getElementById('gambar-soal-el');
                if (soal.gambar) {
                    elGambar.src = soal.gambar.startsWith('http')
                        ? soal.gambar
                        : "{{ asset('storage') }}/" + soal.gambar;
                    containerGambar.classList.remove('hidden');
                } else {
                    elGambar.src = '';
                    containerGambar.classList.add('hidden');
                }

                let btnRagu = document.getElementById('btn-ragu');
                if (raguSiswa[index]) {
                    btnRagu.textContent = "BATAL RAGU";
                    btnRagu.className = "px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-slate-700 hover:bg-slate-800 rounded shadow-2xs transition cursor-pointer";
                } else {
                    btnRagu.textContent = "RAGU-RAGU";
                    btnRagu.className = "px-6 py-2.5 text-xs md:text-sm font-bold text-slate-900 bg-amber-400 hover:bg-amber-500 rounded shadow-2xs transition cursor-pointer";
                }

                let containerOpsi = document.getElementById('opsi-jawaban-container');
                containerOpsi.innerHTML = '';

                // 1. RENDER TIPE MENJODOHKAN (INTERAKTIF TARIK TALI / GARIS SVG)
                if (tipe === 'menjodohkan') {
                    let pairs = parsePasangan(soal);
                    let kananAcak = acakDeterministik(pairs.map(p => p.kanan), SISWA_ID * 100 + (soal.id || index));
                    let tersimpan = (jawabanSiswa[index] && typeof jawabanSiswa[index] === 'object') ? jawabanSiswa[index] : {};

                    let warnaTali = ['#059669', '#2563eb', '#7c3aed', '#db2777', '#d97706', '#0891b2'];
                    let kiriKeWarna = {};
                    let kananKeInfo = {};
                    pairs.forEach((p, idxPair) => {
                        let warna = warnaTali[idxPair % warnaTali.length];
                        kiriKeWarna[p.kiri] = warna;
                        if (tersimpan[p.kiri]) {
                            kananKeInfo[tersimpan[p.kiri]] = {
                                no: idxPair + 1,
                                kiri: p.kiri,
                                warna: warna
                            };
                        }
                    });

                    let kiriHtml = pairs.map((p, i) => {
                        let terpilihKanan = tersimpan[p.kiri] || '';
                        let sudahTerhubung = terpilihKanan !== '';
                        let warna = kiriKeWarna[p.kiri];
                        let isSelectedKiri = (window._aktifKiriMenjodohkan === p.kiri);

                        return `
                                <button type="button"
                                    data-jodoh-kiri="${escapeHtmlText(p.kiri)}"
                                    onclick="klikSisiKiriMenjodohkan(${index}, decodeURIComponent('${encodeURIComponent(p.kiri)}'))"
                                    style="${sudahTerhubung ? `border-color: ${warna}; background-color: ${warna}12;` : ''}"
                                    class="w-full text-left p-3 rounded-xl border-2 transition flex items-center justify-between gap-2.5 cursor-pointer select-none shadow-2xs
                                    ${isSelectedKiri ? 'ring-2 ring-blue-600 border-blue-600 bg-blue-50' : (sudahTerhubung ? '' : 'border-slate-300 bg-white hover:border-indigo-400')}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span style="background-color: ${sudahTerhubung ? warna : '#4338ca'};"
                                            class="w-6 h-6 rounded-full text-white text-xs font-extrabold flex items-center justify-center shrink-0">
                                            ${i + 1}
                                        </span>
                                        <span class="text-xs md:text-sm font-semibold text-slate-800 leading-snug break-words">
                                            ${escapeHtmlText(p.kiri)}
                                        </span>
                                    </div>
                                    <span class="w-3.5 h-3.5 rounded-full border-2 shrink-0 flex items-center justify-center"
                                        style="border-color: ${sudahTerhubung ? warna : '#94a3b8'}; background-color: ${sudahTerhubung ? warna : '#ffffff'};">
                                    </span>
                                </button>
                            `;
                    }).join('');

                    let kananHtml = kananAcak.map((kanan) => {
                        let info = kananKeInfo[kanan] || null;
                        let terhubung = info !== null;

                        return `
                                <button type="button"
                                    data-jodoh-kanan="${escapeHtmlText(kanan)}"
                                    onclick="klikSisiKananMenjodohkan(${index}, decodeURIComponent('${encodeURIComponent(kanan)}'))"
                                    style="${terhubung ? `border-color: ${info.warna}; background-color: ${info.warna}12;` : ''}"
                                    class="w-full text-left p-3 rounded-xl border-2 transition flex items-center justify-between gap-2.5 cursor-pointer select-none shadow-2xs
                                    ${terhubung ? '' : 'border-slate-300 bg-white hover:border-emerald-400'}">
                                    <span class="w-3.5 h-3.5 rounded-full border-2 shrink-0"
                                        style="border-color: ${terhubung ? info.warna : '#94a3b8'}; background-color: ${terhubung ? info.warna : '#ffffff'};">
                                    </span>
                                    <span class="text-xs md:text-sm font-medium text-slate-800 leading-snug flex-1 break-words">
                                        ${escapeHtmlText(kanan)}
                                    </span>
                                    ${terhubung
                                ? `<span style="background-color: ${info.warna};" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold text-white shrink-0">#${info.no}</span>`
                                : `<span class="text-[10px] font-semibold text-slate-400 shrink-0">Pilih</span>`
                            }
                                </button>
                            `;
                    }).join('');

                    containerOpsi.innerHTML = `
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 rounded-lg bg-indigo-50 border border-indigo-200 text-xs text-indigo-900">
                                    <span>
                                        <i class="fa-solid fa-bezier-curve mr-1 text-indigo-600"></i>
                                        Klik item <strong>Kiri</strong> lalu klik pasangan di <strong>Kanan</strong> untuk menarik garis penghubung.
                                    </span>
                                    <button type="button" onclick="resetMenjodohkan(${index})"
                                        class="text-xs font-bold text-rose-600 hover:underline cursor-pointer shrink-0">
                                        <i class="fa-solid fa-rotate-left"></i> Reset Tali
                                    </button>
                                </div>

                                <div id="arena-tarik-tali-${index}" class="relative grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-8 items-stretch py-1">
                                    <svg id="svg-tali-${index}" class="hidden md:block pointer-events-none absolute inset-0 w-full h-full z-10"></svg>

                                    <div class="md:col-span-5 space-y-2.5 relative z-20">
                                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 px-1">Pernyataan (Kiri)</div>
                                        ${kiriHtml}
                                    </div>

                                    <div class="hidden md:block md:col-span-2"></div>

                                    <div class="md:col-span-5 space-y-2.5 relative z-20">
                                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 px-1">Pasangan Jawaban (Kanan)</div>
                                        ${kananHtml}
                                    </div>
                                </div>
                            </div>
                        `;

                    setTimeout(() => gambarGarisMenjodohkan(index), 30);
                }
                // 2. RENDER TIPE ESAI
                else if (tipe === 'esai') {
                    let teksEsai = (typeof jawabanSiswa[index] === 'string') ? jawabanSiswa[index] : '';
                    containerOpsi.innerHTML = `
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700">Ketik Jawaban Uraian Anda:</label>
                                <textarea rows="6" oninput="pilihEsai(${index}, this.value)"
                                    placeholder="Tuliskan jawaban lengkap Anda di sini..."
                                    class="w-full text-xs md:text-sm rounded-lg border-slate-300 bg-slate-50 p-3.5 border text-slate-900 leading-relaxed focus:bg-white focus:ring-2 focus:ring-blue-700 focus:outline-none">${escapeHtmlText(teksEsai)}</textarea>
                                <p class="text-[11px] text-slate-400">Jawaban esai tersimpan otomatis saat Anda mengetik.</p>
                            </div>
                        `;
                }
                // 3. RENDER TIPE PILIHAN GANDA (PG)
                else {
                    let opsiList = [
                        { key: 'A', teks: soal.opsi_a || soal.pilihan_a || soal.option_a || soal.a },
                        { key: 'B', teks: soal.opsi_b || soal.pilihan_b || soal.option_b || soal.b },
                        { key: 'C', teks: soal.opsi_c || soal.pilihan_c || soal.option_c || soal.c },
                        { key: 'D', teks: soal.opsi_d || soal.pilihan_d || soal.option_d || soal.d },
                        { key: 'E', teks: soal.opsi_e || soal.pilihan_e || soal.option_e || soal.e }
                    ].filter(o => o.teks !== null && o.teks !== undefined && String(o.teks).trim() !== '');

                    if (ACAK_JAWABAN_AKTIF && opsiList.length > 1) {
                        opsiList = acakDeterministik(opsiList, SISWA_ID * 313 + (soal.id || index));
                    }

                    const hurufTampil = ['A', 'B', 'C', 'D', 'E'];

                    opsiList.forEach((opsi, posIdx) => {
                        let isChecked = jawabanSiswa[index] === opsi.key ? 'checked' : '';
                        let borderClass = isChecked
                            ? 'border-blue-700 bg-blue-50/70 ring-1 ring-blue-700 font-semibold'
                            : 'border-slate-300 bg-white hover:bg-slate-50';
                        let labelHuruf = ACAK_JAWABAN_AKTIF ? (hurufTampil[posIdx] || opsi.key) : opsi.key;

                        let html = `
                                <label class="flex items-center gap-3.5 p-3 rounded-lg border ${borderClass} cursor-pointer transition select-none shadow-2xs">
                                    <input type="radio" name="pilihan_jawaban" value="${escapeHtmlText(opsi.key)}" ${isChecked} onclick="pilihJawaban(${index}, decodeURIComponent('${encodeURIComponent(opsi.key)}'))" class="text-blue-700 focus:ring-blue-600 w-4 h-4 cursor-pointer">
                                    <span class="text-xs font-bold text-slate-900 w-7 h-7 flex items-center justify-center bg-slate-100 rounded border border-slate-300 shrink-0">${escapeHtmlText(labelHuruf)}.</span>
                                    <span class="text-xs md:text-sm text-slate-900 flex-1 leading-relaxed">${escapeHtmlText(opsi.teks)}</span>
                                </label>
                            `;
                        containerOpsi.insertAdjacentHTML('beforeend', html);
                    });
                }

                document.getElementById('btn-sebelumnya').disabled = (index === 0);
                document.getElementById('btn-selanjutnya').style.display = (index === daftarSoal.length - 1) ? 'none' : 'inline-flex';

                updateTombolNavigasi();
            }

            function pilihJawaban(indexSoal, pilihan) {
                jawabanSiswa[indexSoal] = pilihan;
                simpanKeLocalStorage();
                kirimKeServer(indexSoal, pilihan);
                muatSoal(indexSoal);
            }

            function klikSisiKiriMenjodohkan(indexSoal, teksKiri) {
                if (!jawabanSiswa[indexSoal] || typeof jawabanSiswa[indexSoal] !== 'object') {
                    jawabanSiswa[indexSoal] = {};
                }

                if (window._aktifKiriMenjodohkan === null && jawabanSiswa[indexSoal][teksKiri]) {
                    delete jawabanSiswa[indexSoal][teksKiri];
                    simpanKeLocalStorage();
                    kirimKeServer(indexSoal, jawabanSiswa[indexSoal]);
                    muatSoal(indexSoal);
                    return;
                }

                window._aktifKiriMenjodohkan = (window._aktifKiriMenjodohkan === teksKiri) ? null : teksKiri;
                muatSoal(indexSoal);
            }

            function klikSisiKananMenjodohkan(indexSoal, teksKanan) {
                if (!jawabanSiswa[indexSoal] || typeof jawabanSiswa[indexSoal] !== 'object') {
                    jawabanSiswa[indexSoal] = {};
                }

                let kiriTerpilih = window._aktifKiriMenjodohkan;

                if (!kiriTerpilih) {
                    let pairs = parsePasangan(daftarSoal[indexSoal]);
                    let kosong = pairs.find(p => !jawabanSiswa[indexSoal][p.kiri]);
                    if (!kosong) return;
                    kiriTerpilih = kosong.kiri;
                }

                Object.keys(jawabanSiswa[indexSoal]).forEach(k => {
                    if (jawabanSiswa[indexSoal][k] === teksKanan) {
                        delete jawabanSiswa[indexSoal][k];
                    }
                });

                jawabanSiswa[indexSoal][kiriTerpilih] = teksKanan;
                window._aktifKiriMenjodohkan = null;

                simpanKeLocalStorage();
                kirimKeServer(indexSoal, jawabanSiswa[indexSoal]);
                muatSoal(indexSoal);
            }

            function gambarGarisMenjodohkan(indexSoal) {
                const arena = document.getElementById(`arena-tarik-tali-${indexSoal}`);
                const svg = document.getElementById(`svg-tali-${indexSoal}`);
                if (!arena || !svg) return;

                const tersimpan = (jawabanSiswa[indexSoal] && typeof jawabanSiswa[indexSoal] === 'object') ? jawabanSiswa[indexSoal] : {};
                const pairs = parsePasangan(daftarSoal[indexSoal]);
                const warnaTali = ['#059669', '#2563eb', '#7c3aed', '#db2777', '#d97706', '#0891b2'];

                const arenaRect = arena.getBoundingClientRect();
                if (arenaRect.width === 0 || arenaRect.height === 0) return;

                let pathsHtml = '';

                pairs.forEach((p, idxPair) => {
                    const kananVal = tersimpan[p.kiri];
                    if (!kananVal) return;

                    const elKiri = Array.from(arena.querySelectorAll('[data-jodoh-kiri]'))
                        .find(el => el.getAttribute('data-jodoh-kiri') === p.kiri);
                    const elKanan = Array.from(arena.querySelectorAll('[data-jodoh-kanan]'))
                        .find(el => el.getAttribute('data-jodoh-kanan') === kananVal);

                    if (!elKiri || !elKanan) return;

                    const rKiri = elKiri.getBoundingClientRect();
                    const rKanan = elKanan.getBoundingClientRect();

                    const x1 = rKiri.right - arenaRect.left;
                    const y1 = (rKiri.top + rKiri.bottom) / 2 - arenaRect.top;
                    const x2 = rKanan.left - arenaRect.left;
                    const y2 = (rKanan.top + rKanan.bottom) / 2 - arenaRect.top;

                    const dx = Math.max(40, Math.abs(x2 - x1) * 0.45);
                    const d = `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`;
                    const warna = warnaTali[idxPair % warnaTali.length];

                    pathsHtml += `
                            <path d="${d}" fill="none" stroke="${warna}" stroke-width="3" stroke-linecap="round" opacity="0.9" />
                            <circle cx="${x1}" cy="${y1}" r="4.5" fill="${warna}" />
                            <circle cx="${x2}" cy="${y2}" r="4.5" fill="${warna}" />
                        `;
                });

                svg.setAttribute('viewBox', `0 0 ${arenaRect.width} ${arenaRect.height}`);
                svg.innerHTML = pathsHtml;
            }

            window.addEventListener('resize', () => {
                if (daftarSoal[indeksAktif] && normalisasiTipe(daftarSoal[indeksAktif]) === 'menjodohkan') {
                    gambarGarisMenjodohkan(indeksAktif);
                }
            });

            function resetMenjodohkan(indexSoal) {
                delete jawabanSiswa[indexSoal];
                window._aktifKiriMenjodohkan = null;
                simpanKeLocalStorage();
                kirimKeServer(indexSoal, {});
                muatSoal(indexSoal);
            }

            function pilihEsai(indexSoal, nilaiTeks) {
                jawabanSiswa[indexSoal] = nilaiTeks;
                simpanKeLocalStorage();
                updateTombolNavigasi();

                clearTimeout(debounceEsaiTimer);
                debounceEsaiTimer = setTimeout(() => {
                    kirimKeServer(indexSoal, nilaiTeks);
                }, 800);
            }

            function toggleRaguRagu(indexSoal) {
                raguSiswa[indexSoal] = !raguSiswa[indexSoal];
                simpanKeLocalStorage();
                muatSoal(indexSoal);
            }

            function gantiSoal(targetIndex) {
                if (sedangJedaTipe) return;
                if (targetIndex < 0 || targetIndex >= daftarSoal.length || targetIndex === indeksAktif) return;

                window._aktifKiriMenjodohkan = null;

                const tipeAsal = normalisasiTipe(daftarSoal[indeksAktif]);
                const tipeTujuan = normalisasiTipe(daftarSoal[targetIndex]);

                if (tipeAsal !== tipeTujuan) {
                    mulaiJedaTransisiTipe(tipeTujuan, () => {
                        muatSoal(targetIndex);
                    });
                } else {
                    muatSoal(targetIndex);
                }
            }

            function mulaiJedaTransisiTipe(tipeTujuan, callbackSelesai) {
                sedangJedaTipe = true;
                let sisaDetik = 15;
                const info = PETUNJUK_TIPE[tipeTujuan] || PETUNJUK_TIPE.pg;

                const modal = document.getElementById('modal-transisi-tipe');
                const badge = document.getElementById('modal-badge-tipe');
                const judul = document.getElementById('modal-judul-perintah');
                const perintah = document.getElementById('modal-isi-perintah');
                const hitungan = document.getElementById('modal-countdown');

                badge.textContent = info.label;
                judul.textContent = info.judul;
                perintah.textContent = info.perintah;
                hitungan.textContent = sisaDetik;

                modal.classList.remove('hidden');
                modal.classList.add('flex');

                clearInterval(intervalJedaTipe);
                intervalJedaTipe = setInterval(() => {
                    sisaDetik--;
                    hitungan.textContent = Math.max(0, sisaDetik);

                    if (sisaDetik <= 0) {
                        clearInterval(intervalJedaTipe);
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        sedangJedaTipe = false;
                        callbackSelesai();
                    }
                }, 1000);
            }

            function updateTombolNavigasi() {
                daftarSoal.forEach((_, idx) => {
                    let btn = document.getElementById(`nav-btn-${idx}`);
                    if (btn) {
                        let hasAnswer = sudahTerjawab(idx);
                        let isRagu = raguSiswa[idx];

                        let baseClass = "w-full aspect-square rounded text-xs font-bold flex items-center justify-center border transition shadow-2xs cursor-pointer ";
                        if (isRagu) {
                            btn.className = baseClass + "border-amber-500 bg-amber-400 text-slate-900";
                        } else if (hasAnswer) {
                            btn.className = baseClass + "border-emerald-700 bg-emerald-600 text-white";
                        } else {
                            btn.className = baseClass + "border-slate-300 bg-white text-slate-800 hover:bg-slate-100";
                        }

                        if (idx === indeksAktif) {
                            btn.classList.add('ring-2', 'ring-offset-1', 'ring-slate-950', 'font-black');
                        } else {
                            btn.classList.remove('ring-2', 'ring-offset-1', 'ring-slate-950', 'font-black');
                        }
                    }
                });
            }

            // Timer Mundur Ujian
            let timerdetik = {{ (int) $sisaWaktuDetik }};
            const timerElement = document.getElementById('timer');

            function renderTimer() {
                if (!timerElement) return;
                let jam = Math.floor(timerdetik / 3600);
                let menit = Math.floor((timerdetik % 3600) / 60);
                let detik = timerdetik % 60;

                timerElement.textContent =
                    String(jam).padStart(2, '0') + ':' +
                    String(menit).padStart(2, '0') + ':' +
                    String(detik).padStart(2, '0');

                if (timerdetik <= 300) {
                    timerElement.classList.remove('text-slate-900');
                    timerElement.classList.add('text-rose-600');
                }
            }

            document.addEventListener("DOMContentLoaded", () => {
                renderTimer();
                muatSoal(0);
            });

            const countdown = setInterval(() => {
                if (timerdetik <= 0) {
                    renderTimer();
                    clearInterval(countdown);
                    alert('Waktu ujian telah habis! Sistem akan mengirimkan jawaban Anda secara otomatis.');
                    submitUjianOtomatis();
                } else {
                    timerdetik--;
                    renderTimer();
                }
            }, 1000);

            function konfirmasiSelesai() {
                if (sedangSubmitSelesai) return;

                let totalSoal = daftarSoal.length;
                let belumDijawab = 0;
                let jumlahRagu = 0;

                daftarSoal.forEach((_, idx) => {
                    if (!sudahTerjawab(idx)) {
                        belumDijawab++;
                    }
                    if (raguSiswa[idx]) {
                        jumlahRagu++;
                    }
                });

                if (belumDijawab > 0 || jumlahRagu > 0) {
                    let pesan = "Ujian belum dapat diserahkan!\n\n";
                    if (belumDijawab > 0) {
                        pesan += `- Masih ada ${belumDijawab} soal yang BELUM DIJAWAB / BELUM LENGKAP.\n`;
                    }
                    if (jumlahRagu > 0) {
                        pesan += `- Masih ada ${jumlahRagu} soal berstatus RAGU-RAGU.\n`;
                    }
                    pesan += "\nSilakan lengkapi semua jawaban dan hilangkan status Ragu-Ragu terlebih dahulu.";

                    alert(pesan);
                    return;
                }

                if (confirm(`Semua ${totalSoal} soal telah terjawab dengan lengkap. Apakah Anda yakin ingin mengakhiri dan mengirimkan ujian ini?`)) {
                    kirimSinyalSelesai();
                }
            }

            function hapusLocalStorage() {
                localStorage.removeItem(KEY_JAWABAN);
                localStorage.removeItem(KEY_RAGU);
            }

            function submitUjianOtomatis() {
                if (sedangSubmitSelesai) return;
                sedangSubmitSelesai = true;
                hapusLocalStorage();

                fetch("{{ route('siswa.ujian.selesai') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ujian_id: UJIAN_ID
                    })
                })
                    .finally(() => {
                        window.location.href = "{{ route('siswa.dashboard') }}";
                    });
            }

            function kirimSinyalSelesai() {
                if (sedangSubmitSelesai) return;
                sedangSubmitSelesai = true;

                const btnSelesai = document.getElementById('btn-selesai-ujian');
                if (btnSelesai) {
                    btnSelesai.disabled = true;
                    btnSelesai.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> MENGIRIM UJIAN...';
                }

                fetch("{{ route('siswa.ujian.selesai') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ujian_id: UJIAN_ID
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            hapusLocalStorage();
                            alert('Ujian berhasil diserahkan! Nilai Anda telah direkap.');
                            window.location.href = "{{ route('siswa.dashboard') }}";
                        } else {
                            sedangSubmitSelesai = false;
                            if (btnSelesai) {
                                btnSelesai.disabled = false;
                                btnSelesai.innerHTML = '<i class="fa-solid fa-check-circle"></i> SELESAI UJIAN';
                            }
                            alert('Gagal menyerahkan ujian: ' + (data.message || 'Terjadi kesalahan'));
                        }
                    })
                    .catch(() => {
                        sedangSubmitSelesai = false;
                        if (btnSelesai) {
                            btnSelesai.disabled = false;
                            btnSelesai.innerHTML = '<i class="fa-solid fa-check-circle"></i> SELESAI UJIAN';
                        }
                        alert('Terjadi kesalahan koneksi. Silakan coba klik SELESAI UJIAN kembali.');
                    });
            }
        </script>
    @endif
</body>

</html>