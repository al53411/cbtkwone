<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- TAMBAHKAN BARIS INI -->
    <title>Ujian CAT - {{ $ujian->nama_ujian ?? 'Ujian Seleksi' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col antialiased">

    <!-- TOP HEADER / NAVBAR (Gaya Resmi CAT BKN) -->
    <header class="bg-white border-b border-slate-300 px-6 py-3 shadow-xs sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">

            <!-- Kiri: Logo & Judul Ujian -->
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <!-- Kiri: Logo & Judul Ujian Dinamis dari Database -->
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <div class="flex items-center gap-2">
                            <!-- Logo Sekolah / Aplikasi -->
                            <div
                                class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center font-black text-white text-lg shadow-sm overflow-hidden border border-blue-600">
                                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-extrabold text-slate-900 text-sm tracking-wider">MAHESAPP</span>
                                    <span
                                        class="text-[10px] text-slate-500 font-semibold border-l border-slate-300 pl-1.5">CBT
                                        CAT System</span>
                                </div>
                                <p class="text-xs font-bold text-blue-900 tracking-tight uppercase">
                                    {{ $ujian->nama_ujian ?? 'Ujian Sekolah' }}
                                    &bull; <span
                                        class="text-slate-700 font-semibold">{{ $ujian->mapel->nama_mapel ?? '' }}</span>
                                    @if(isset($ujian->kelas->nama_kelas))
                                        &bull; <span class="text-emerald-700 font-semibold">Kelas
                                            {{ $ujian->kelas->nama_kelas }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tengah/Kanan: Identitas Peserta & Timer -->
            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                <!-- Identitas Peserta -->
                <div class="text-right hidden sm:block">
                    <h2 class="font-semibold text-gray-900 text-sm leading-snug truncate">
                        {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->name ?? '-' }}
                    </h2>
                    <div class="text-xs text-gray-600 mt-0.5 flex items-center justify-end gap-1.5">
                        <span>NISN:</span>
                        <span
                            class="font-mono font-medium text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">
                            {{ $siswa->nisn ?? '-' }}
                        </span>
                    </div>
                </div>

                <!-- Kotak Timer Sisa Waktu (Khas BKN) -->
                <div
                    class="bg-white border-2 border-slate-300 px-4 py-1.5 rounded-lg flex items-center gap-3 shadow-inner">
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block leading-none">Sisa
                            Waktu</span>
                        <span id="timer"
                            class="text-xl font-black font-mono text-slate-900 tracking-wider">01:26:54</span>
                    </div>
                </div>
            </div>

        </div>
    </header>

    @php
        $adaSoal = isset($soals) && count($soals) > 0;
    @endphp

    <!-- KONTEN UTAMA -->
    <main class="max-w-7xl w-full mx-auto p-4 md:p-6 flex-1">
        @if(!$adaSoal)
            <div class="bg-white p-12 rounded-lg border border-slate-300 text-center space-y-3 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-3xl"></i>
                <h2 class="text-sm font-bold text-slate-800">Soal Belum Tersedia</h2>
                <p class="text-xs text-slate-600">Belum ada butir soal yang diinputkan untuk ujian ini.</p>
                <a href="{{ route('siswa.dashboard') }}"
                    class="inline-block px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold rounded transition">Kembali
                    ke Dashboard</a>
            </div>
        @else
            <!-- Grid Layout Utama: Kiri Soal (70%), Kanan Navigasi & Legenda (30%) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- KOLOM KIRI: PANEL SOAL & OPSI (Span 8) -->
                <div
                    class="lg:col-span-8 bg-white border border-slate-300 rounded-lg shadow-sm flex flex-col justify-between min-h-[580px] p-6">
                    <div>
                        <!-- Header Kategori / Nomor Soal -->
                        <div class="border-b border-slate-200 pb-3 mb-5">
                            <h2 id="header-kategori-soal" class="text-base font-extrabold text-slate-900 uppercase">
                                SOAL NO. <span id="nomor-soal-teks">1</span> -
                                {{ strtoupper($ujian->mapel->nama_mapel ?? 'TES UJIAN KOMPETENSI') }}
                            </h2>
                        </div>

                        <!-- Teks Pertanyaan Soal -->
                        <div class="text-sm md:text-base text-slate-900 font-medium leading-relaxed mb-6">
                            <div id="teks-soal" class="prose max-w-none text-slate-900">Memuat soal...</div>
                        </div>

                        <!-- Wadah Gambar Soal (Jika Ada) -->
                        <div id="gambar-soal-container" class="mb-6 hidden">
                            <img id="gambar-soal-el" src="" alt="Gambar Soal"
                                class="max-h-72 rounded-lg border border-slate-200 object-contain shadow-xs">
                        </div>

                        <!-- Opsi Jawaban (A, B, C, D, E) -->
                        <div class="space-y-3 mb-6" id="opsi-jawaban-container">
                            <!-- Render via JavaScript -->
                        </div>
                    </div>

                    <!-- Bar Tombol Aksi Bawah (Sebelumnya, Ragu-ragu, Selanjutnya) -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-200 mt-6">
                        <button type="button" id="btn-sebelumnya" onclick="gantiSoal(indeksAktif - 1)"
                            class="px-5 py-2.5 text-xs md:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded shadow-2xs transition disabled:opacity-40 disabled:cursor-not-allowed">
                            SEBELUMNYA
                        </button>

                        <button type="button" id="btn-ragu" onclick="toggleRaguRagu(indeksAktif)"
                            class="px-6 py-2.5 text-xs md:text-sm font-bold text-slate-900 bg-amber-400 hover:bg-amber-500 rounded shadow-2xs transition">
                            RAGU-RAGU
                        </button>

                        <button type="button" id="btn-selanjutnya" onclick="gantiSoal(indeksAktif + 1)"
                            class="px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 rounded shadow-2xs transition">
                            SELANJUTNYA
                        </button>
                    </div>
                </div>

                <!-- KOLOM KANAN: DAFTAR SOAL & LEGENDA STATUS (Span 4) -->
                <div class="lg:col-span-4 space-y-4">

                    <!-- Box Kotak Status (Legenda Khas BKN) -->
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

                    <!-- Kotak Navigasi Daftar Soal -->
                    <div class="bg-white border border-slate-300 rounded-lg p-4 shadow-sm space-y-3">
                        <h3
                            class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">
                            Daftar Soal
                        </h3>

                        <!-- Grid Nomor Soal -->
                        <div class="grid grid-cols-5 gap-2 max-h-[360px] overflow-y-auto p-1.5 border border-slate-200 rounded bg-slate-50"
                            id="navigasi-nomor-container">
                            @foreach ($soals as $index => $soal)
                                <button type="button" id="nav-btn-{{ $index }}" onclick="gantiSoal({{ $index }})"
                                    class="w-full aspect-square rounded text-xs font-bold flex items-center justify-center border border-slate-300 bg-white text-slate-800 hover:bg-slate-100 transition shadow-2xs">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Tombol Selesai Ujian -->
                        <div class="pt-2">
                            <button type="button" onclick="konfirmasiSelesai()"
                                class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded transition shadow-sm flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check-circle"></i> SELESAI UJIAN
                            </button>
                        </div>
                        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                            <div>
                                &copy; {{ date('Y') }} Copyrigth:MahesAPP. All rights reserved.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        @endif
    </main>

    <!-- FOOTER MINIMALIS -->
    <footer class="py-4 text-center text-xs text-slate-500 border-t border-slate-200 bg-white mt-auto">
        &copy; {{ date('Y') }} Sistem CAT BKN &bull; Ujian Berbasis Komputer Sekolah.
    </footer>

    @if($adaSoal)
        <script>
            const daftarSoal = @json($soals);
            let indeksAktif = 0;

            // Key unik LocalStorage khusus per Ujian
            const UJIAN_ID = "{{ $ujian->id }}";
            const KEY_JAWABAN = `cbt_jawaban_${UJIAN_ID}`;
            const KEY_RAGU = `cbt_ragu_${UJIAN_ID}`;

            // 1. MEMBACA JAWABAN & STATUS RAGU DARI LOCALSTORAGE (Pencegah hilang saat reload)
            let jawabanSiswa = JSON.parse(localStorage.getItem(KEY_JAWABAN)) || {};
            let raguSiswa = JSON.parse(localStorage.getItem(KEY_RAGU)) || {};

            // Helper untuk menyimpan ke LocalStorage
            function simpanKeLocalStorage() {
                localStorage.setItem(KEY_JAWABAN, JSON.stringify(jawabanSiswa));
                localStorage.setItem(KEY_RAGU, JSON.stringify(raguSiswa));
            }

            function muatSoal(index) {
                if (index < 0 || index >= daftarSoal.length) return;
                indeksAktif = index;
                const soal = daftarSoal[index];

                document.getElementById('nomor-soal-teks').textContent = index + 1;
                document.getElementById('teks-soal').innerHTML = soal.soal || soal.pertanyaan || 'Tidak ada teks soal.';

                // Render Gambar Soal jika ada
                let containerGambar = document.getElementById('gambar-soal-container');
                let elGambar = document.getElementById('gambar-soal-el');
                if (soal.gambar) {
                    elGambar.src = "{{ asset('storage') }}/" + soal.gambar;
                    containerGambar.classList.remove('hidden');
                } else {
                    elGambar.src = '';
                    containerGambar.classList.add('hidden');
                }

                // Update teks tombol Ragu-ragu sesuai status
                let btnRagu = document.getElementById('btn-ragu');
                if (raguSiswa[index]) {
                    btnRagu.textContent = "BATAL RAGU";
                    btnRagu.className = "px-6 py-2.5 text-xs md:text-sm font-bold text-white bg-slate-700 hover:bg-slate-800 rounded shadow-2xs transition";
                } else {
                    btnRagu.textContent = "RAGU-RAGU";
                    btnRagu.className = "px-6 py-2.5 text-xs md:text-sm font-bold text-slate-900 bg-amber-400 hover:bg-amber-500 rounded shadow-2xs transition";
                }

                // Render Opsi Jawaban (A, B, C, D, E)
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
                        let borderClass = isChecked ? 'border-blue-700 bg-blue-50/70 ring-1 ring-blue-700 font-semibold' : 'border-slate-300 bg-white hover:bg-slate-50';

                        let html = `
                                <label class="flex items-center gap-3.5 p-3 rounded-lg border ${borderClass} cursor-pointer transition select-none shadow-2xs">
                                    <input type="radio" name="pilihan_jawaban" value="${opsi.key}" ${isChecked} onclick="pilihJawaban(${index}, '${opsi.key}')" class="text-blue-700 focus:ring-blue-600 w-4 h-4 cursor-pointer">
                                    <span class="text-xs font-bold text-slate-900 w-7 h-7 flex items-center justify-center bg-slate-100 rounded border border-slate-300 shrink-0">${opsi.key}.</span>
                                    <span class="text-xs md:text-sm text-slate-900 flex-1 leading-relaxed">${opsi.teks}</span>
                                </label>
                            `;
                        containerOpsi.insertAdjacentHTML('beforeend', html);
                    }
                });

                document.getElementById('btn-sebelumnya').disabled = (index === 0);
                document.getElementById('btn-selanjutnya').style.display = (index === daftarSoal.length - 1) ? 'none' : 'inline-flex';

                updateTombolNavigasi();
            }

            // FUNGSI PILIH JAWABAN & AUTO-SAVE KE LOCALSTORAGE & DATABASE
            function pilihJawaban(indexSoal, pilihan) {
                jawabanSiswa[indexSoal] = pilihan;
                simpanKeLocalStorage(); // Simpan instan ke LocalStorage

                let soalId = daftarSoal[indexSoal].id;

                // Kirim otomatis ke server (Auto-save Database)
                fetch("{{ route('siswa.ujian.simpan-jawaban') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ujian_id: "{{ $ujian->id }}",
                        soal_id: soalId,
                        jawaban: pilihan
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Jawaban tersimpan otomatis ke database.");
                    })
                    .catch(error => console.error('Gagal menyimpan ke server, disimpan di LocalStorage:', error));

                updateTombolNavigasi();
                muatSoal(indexSoal);
            }

            // TOGGLE STATUS RAGU-RAGU
            function toggleRaguRagu(indexSoal) {
                raguSiswa[indexSoal] = !raguSiswa[indexSoal];
                simpanKeLocalStorage(); // Simpan status Ragu-Ragu ke LocalStorage
                muatSoal(indexSoal);
            }

            function gantiSoal(index) {
                muatSoal(index);
            }

            function updateTombolNavigasi() {
                daftarSoal.forEach((_, idx) => {
                    let btn = document.getElementById(`nav-btn-${idx}`);
                    if (btn) {
                        let hasAnswer = jawabanSiswa[idx] !== undefined && jawabanSiswa[idx] !== null && jawabanSiswa[idx] !== '';
                        let isRagu = raguSiswa[idx];

                        let baseClass = "w-full aspect-square rounded text-xs font-bold flex items-center justify-center border transition shadow-2xs ";
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

            document.addEventListener("DOMContentLoaded", () => {
                muatSoal(0);
            });

            // Timer Mundur Ujian
            let durasiMenit = {{ $ujian->durasi ?? 90 }};
            let timerdetik = durasiMenit * 60;
            const timerElement = document.getElementById('timer');

            const countdown = setInterval(() => {
                let jam = Math.floor(timerdetik / 3600);
                let menit = Math.floor((timerdetik % 3600) / 60);
                let detik = timerdetik % 60;

                if (timerElement) {
                    timerElement.textContent =
                        String(jam).padStart(2, '0') + ':' +
                        String(menit).padStart(2, '0') + ':' +
                        String(detik).padStart(2, '0');
                }

                if (timerdetik <= 0) {
                    clearInterval(countdown);
                    alert('Waktu ujian telah habis! Sistem akan mengirimkan jawaban Anda secara otomatis.');
                    submitUjianOtomatis();
                } else {
                    timerdetik--;
                }
            }, 1000);

            // FUNGSI VALIDASI KETAT & KONFIRMASI SELESAI UJIAN
            function konfirmasiSelesai() {
                let totalSoal = daftarSoal.length;
                let belumDijawab = 0;
                let jumlahRagu = 0;

                // Hitung soal yang belum dijawab dan ragu-ragu
                daftarSoal.forEach((_, idx) => {
                    let ans = jawabanSiswa[idx];
                    let isRagu = raguSiswa[idx];

                    if (ans === undefined || ans === null || ans === '') {
                        belumDijawab++;
                    }
                    if (isRagu) {
                        jumlahRagu++;
                    }
                });

                // VALIDASI VETO: Mencegah kirim jika ada belum dijawab ATAU masih ragu-ragu
                if (belumDijawab > 0 || jumlahRagu > 0) {
                    let pesan = "Ujian belum dapat diserahkan!\n\n";
                    if (belumDijawab > 0) {
                        pesan += `- Masih ada ${belumDijawab} soal yang BELUM DIJAWAB.\n`;
                    }
                    if (jumlahRagu > 0) {
                        pesan += `- Masih ada ${jumlahRagu} soal berstatus RAGU-RAGU.\n`;
                    }
                    pesan += "\nSilakan lengkapi semua jawaban dan hilangkan status Ragu-Ragu terlebih dahulu.";

                    alert(pesan);
                    return; // Hentikan proses pengiriman
                }

                // Jika semua soal terisi dan tidak ada yang ragu
                if (confirm(`Semua ${totalSoal} soal telah terjawab dengan lengkap. Apakah Anda yakin ingin mengakhiri dan mengirimkan ujian ini?`)) {
                    kirimSinyalSelesai();
                }
            }

            // Helper untuk membersihkan LocalStorage saat ujian selesai
            function hapusLocalStorage() {
                localStorage.removeItem(KEY_JAWABAN);
                localStorage.removeItem(KEY_RAGU);
            }

            function submitUjianOtomatis() {
                hapusLocalStorage(); // Hapus cache lokal setelah waktu habis
                fetch("{{ route('siswa.ujian.selesai') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ujian_id: "{{ $ujian->id }}"
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        window.location.href = "{{ route('siswa.dashboard') }}";
                    });
            }

            function kirimSinyalSelesai() {
                fetch("{{ route('siswa.ujian.selesai') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ujian_id: "{{ $ujian->id }}"
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            hapusLocalStorage(); // Hapus cache lokal saat berhasil diserahkan
                            alert('Ujian berhasil diserahkan! Nilai Anda telah direkap.');
                            window.location.href = "{{ route('siswa.dashboard') }}";
                        } else {
                            alert('Gagal menyerahkan ujian: ' + (data.message || 'Terjadi kesalahan'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan koneksi.');
                    });
            }
        </script>
    @endif
</body>

</html>