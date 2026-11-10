<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - CBT Sekolah</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Scrollbar for Textarea */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col antialiased">

    <!-- TOP NAVBAR -->
    <header class="bg-white border-b border-slate-200 px-6 py-3 shadow-xs sticky top-0 z-50">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('logo.png') }}" alt="Logo CBT" class="w-14 h-14 rounded-x4 object-cover shadow-sm">
                <div>
                    <span class="text-xs font-bold text-slate-800 block">MAHESAPP</span>
                    <span class="text-[10px] text-slate-500 font-medium">CAT Sytem</span>
                </div>
            </div>

            <div>
                <form action="{{ route('siswa.logout') }}" method="POST"
                    onsubmit="return confirm('Keluar dari sistem?')">
                    @csrf
                    <button type="submit"
                        class="px-3.5 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-power-off"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 md:p-8 space-y-6">

        <!-- Sambutan & Profil Singkat Siswa -->
        <div
            class="bg-slate-900 border border-slate-800 p-6 md:p-8 rounded-2xl text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-1.5">
                <span
                    class="inline-flex items-center px-3 py-1 bg-blue-600/30 border border-blue-500/30 text-blue-300 rounded-lg text-xs font-semibold tracking-wide">
                    <i class="fa-solid fa-graduation-cap mr-1.5"></i> Siswa Peserta CBT
                </span>
                @php
                    $namaSiswa = $siswa->nama ?? ($siswa->nama_lengkap ?? ($siswa->name ?? 'Siswa'));
                @endphp
                <h1 class="text-2xl font-bold tracking-tight text-white">Selamat Datang,
                    {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? $siswa->name ?? '-' }}!
                </h1>
                <p class="text-xs text-slate-300 flex flex-wrap items-center gap-2 pt-1">
                    <span>NISN / No: <b class="text-white">{{ $siswa->nisn ?? ($siswa->nis ?? '-') }}</b></span>
                    <span class="text-slate-600">•</span>
                    <span>Kelas: <b class="text-white">{{ $siswa->kelas->nama_kelas ?? 'Belum ditentukan' }}</b></span>
                </p>
            </div>
            <div>
                <form action="{{ route('siswa.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2.5 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-power-off"></i> Keluar Sistem
                    </button>
                </form>
            </div>
        </div>

        <!-- Alert Notifikasi Error -->
        @if(session('error'))
            <div
                class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-3 shadow-2xs">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Alert Notifikasi Sukses -->
        @if(session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-3 shadow-2xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- KOLOM KIRI: Form Input Token & Aturan Ujian -->
            <div class="lg:col-span-7 bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Mulai Mengerjakan Ujian</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Silakan baca tata tertib, masukkan token, lalu centang
                        persetujuan.</p>
                </div>

                <form action="{{ route('siswa.konfirmasi') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Tata Tertib Area -->
                    <!-- Tata Tertib Area -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            <i class="fa-solid fa-clipboard-list text-blue-600 mr-1"></i> Tata Tertib Ujian
                        </label>
                        <div
                            class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 h-32 overflow-y-auto custom-scrollbar leading-relaxed">

                            {{-- LOGIKA DINAMIS: Menampilkan aturan dari ujian yang aktif, atau pengaturan global, atau
                            default --}}
                            @if(isset($aturan_global) && $aturan_global != '')
                                {!! nl2br(e($aturan_global)) !!}

                            @elseif(isset($ujianAktif) && $ujianAktif->count() > 0 && !empty($ujianAktif->first()->aturan_ujian))
                                {!! nl2br(e($ujianAktif->first()->aturan_ujian)) !!}

                            @else
                                1. Peserta ujian wajib menjunjung tinggi kejujuran.<br>
                                2. Dilarang membuka tab lain, browser, atau aplikasi tambahan selama ujian berlangsung
                                (sistem layar penuh/fullscreen akan aktif).<br>
                                3. Jika keluar dari layar ujian lebih dari batas yang ditentukan, sistem otomatis menyimpan
                                jawaban dan mengakhiri sesi ujian Anda.<br>
                                4. Waktu pengerjaan akan terus berjalan meskipun Anda keluar sistem atau perangkat mati.<br>
                                5. Dilarang mendokumentasikan atau menyebarkan soal ujian dalam bentuk apapun.<br>
                                6. Pastikan koneksi internet stabil sebelum mulai mengerjakan.
                            @endif

                        </div>
                    </div>

                    <!-- Input Token -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Token
                            Ujian</label>
                        <div class="relative max-w-sm">
                            <span
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 z-10">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="text" name="token" required value="{{ old('token') }}"
                                placeholder="CONTOH: A7X9M2"
                                class="w-full text-sm font-mono uppercase tracking-widest rounded-xl border border-slate-300 bg-slate-50 pl-11 pr-4 py-3 text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition @error('token') border-rose-500 bg-rose-50/30 @enderror"
                                maxlength="10" autocomplete="off">
                        </div>
                        @error('token')
                            <span class="text-xs text-rose-600 mt-1.5 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Checkbox Persetujuan -->
                    <div class="flex items-start gap-3 p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                        <div class="flex items-center h-5 mt-0.5">
                            <input id="persetujuan" type="checkbox" required onchange="toggleSubmitButton()"
                                class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                        </div>
                        <label for="persetujuan"
                            class="text-xs text-slate-700 cursor-pointer select-none leading-relaxed">
                            Saya telah membaca dan memahami tata tertib di atas. Saya bersedia menaati semua peraturan
                            dan menerima sanksi apabila melakukan pelanggaran selama ujian berlangsung.
                        </label>
                    </div>

                    <!-- Tombol Submit (Disabled by default) -->
                    <button type="submit" id="btn-submit" disabled
                        class="px-6 py-3 w-full sm:w-auto justify-center bg-slate-300 text-slate-500 font-bold text-xs rounded-xl transition shadow-xs inline-flex items-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Ujian Sekarang
                    </button>
                </form>
            </div>

            <!-- KOLOM KANAN: Informasi Ujian Aktif -->
            <div class="lg:col-span-5 bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Jadwal Ujian Aktif</h2>
                    <span class="text-[11px] text-slate-500 font-medium">Real-time status</span>
                </div>

                <div class="space-y-3">
                    @forelse ($ujianAktif as $ujian)
                            <div
                                class="p-4 border border-slate-200 rounded-xl bg-slate-50 hover:bg-white hover:border-blue-300 transition group">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition">
                                        {{ $ujian->nama_ujian }}
                                    </h3>
                                    <span
                                        class="px-2 py-0.5 bg-blue-100 text-blue-800 border border-blue-200 rounded text-[10px] font-extrabold uppercase tracking-wide">
                                        Aktif
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1.5 text-xs text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-book w-4 text-center text-slate-400"></i>
                                        <span
                                            class="font-semibold text-emerald-700">{{ $ujian->mapel->nama_mapel ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2"><i
                                            class="fa-solid fa-book w-4 text-center text-slate-400"></i>
                                        <span class="font-semibold text-emerald-700">
                                            {{ $ujian->kelas->nama_kelas ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock w-4 text-center text-slate-400"></i>
                                    <span>Durasi: <b class="text-slate-700">{{ $ujian->durasi ?? '-' }} Menit</b></span>
                                </div>
                            </div>
                        </div>
                    @empty
                    <div
                        class="py-8 text-center text-slate-500 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-300">
                        Belum ada jadwal ujian yang aktif untuk kelas Anda saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        </div>

    </main>

    <!-- FOOTER -->
    <footer class="py-4 text-center text-xs text-slate-500 border-t border-slate-200 bg-white mt-auto">
        &copy; {{ date('Y') }} Sistem CBT Sekolah &bull; Hak Cipta Dilindungi.
    </footer>

    <!-- SCRIPT UNTUK TOGGLE TOMBOL SUBMIT -->
    <script>
        function toggleSubmitButton() {
            const checkbox = document.getElementById('persetujuan');
            const btnSubmit = document.getElementById('btn-submit');

            if (checkbox.checked) {
                // Aktifkan Tombol
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
                btnSubmit.classList.add('bg-blue-700', 'hover:bg-blue-800', 'active:bg-blue-900', 'text-white', 'cursor-pointer');
            } else {
                // Nonaktifkan Tombol
                btnSubmit.disabled = true;
                btnSubmit.classList.add('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
                btnSubmit.classList.remove('bg-blue-700', 'hover:bg-blue-800', 'active:bg-blue-900', 'text-white', 'cursor-pointer');
            }
        }
    </script>
</body>

</html>