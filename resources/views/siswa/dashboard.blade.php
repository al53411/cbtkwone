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
    @php
        $logoPath = public_path('logo.png');
        $logoUrl = is_file($logoPath)
            ? asset('logo.png') . '?v=' . @filemtime($logoPath)
            : (is_file(public_path('favicon.png')) ? asset('favicon.png') : '');
    @endphp
    @if ($logoUrl)
        <link rel="icon" type="image/png" href="{{ $logoUrl }}">
    @endif
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

@php
    $daftarUjian = $ujianAktif ?? collect();
    $ujianUtama = $daftarUjian->firstWhere('status', 'aktif') ?? $daftarUjian->first();
    $actionFormUjian = \Illuminate\Support\Facades\Route::has('siswa.ujian.verifikasi')
        ? route('siswa.ujian.verifikasi')
        : route('siswa.konfirmasi');

    $daftarHasil = isset($riwayatNilais)
        ? $riwayatNilais
        : (isset($riwayatPengerjaan)
            ? $riwayatPengerjaan->filter(function ($item) {
                $status = strtolower((string) ($item->status ?? ''));
                return in_array($status, ['selesai', 'finished', 'completed']) || !empty($item->waktu_selesai);
            })->values()
            : collect());
@endphp

<body class="bg-slate-100 min-h-screen flex flex-col antialiased">

    <!-- TOP NAVBAR -->
    <header class="bg-white border-b border-slate-200 px-6 py-3 shadow-xs sticky top-0 z-50">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo CBT" class="w-11 h-11 rounded-xl object-contain shadow-xs">
                @else
                    <div class="w-11 h-11 rounded-xl bg-blue-700 text-white flex items-center justify-center font-extrabold text-sm shadow-xs">
                        CAT
                    </div>
                @endif
                <div>
                    <span class="text-xs font-bold text-slate-800 block">MAHESAPP</span>
                    <span class="text-[10px] text-slate-500 font-medium">CAT System</span>
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
                <h1 class="text-2xl font-bold tracking-tight text-white">Selamat Datang,
                    {{ $siswa->nama_siswa ?? $siswa->nama ?? $siswa->nama_lengkap ?? $siswa->name ?? '-' }}!
                </h1>
                <p class="text-xs text-slate-300 flex flex-wrap items-center gap-2 pt-1">
                    <span>NISN / No: <b class="text-white">{{ $siswa->nisn ?? ($siswa->nis ?? '-') }}</b></span>
                    <span class="text-slate-600">•</span>
                    <span>Kelas: <b class="text-white">{{ $siswa->kelas->nama_kelas ?? 'Belum ditentukan' }}</b></span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2.5 rounded-xl bg-slate-800/90 border border-slate-700 text-right">
                    <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Ujian Selesai</span>
                    <span class="text-lg font-extrabold text-emerald-400">{{ $daftarHasil->count() }}</span>
                    <span class="text-xs text-slate-400">Sesi</span>
                </div>
            </div>
        </div>

        <!-- Alert Notifikasi Error -->
        @if (session('error'))
            <div
                class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-3 shadow-2xs">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Alert Notifikasi Sukses -->
        @if (session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-3 shadow-2xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs space-y-1 shadow-2xs">
                <div class="font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Periksa kembali isian Anda:
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- KOLOM KIRI: Form Input Token & Aturan Ujian -->
            <div class="lg:col-span-7 bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Mulai Mengerjakan Ujian
                        </h2>
                        <p class="text-xs text-slate-600 mt-0.5">Pilih jadwal di sebelah kanan (jika lebih dari satu),
                            baca tata tertib, masukkan token, lalu centang persetujuan.</p>
                    </div>
                    @if ($ujianUtama)
                        <span id="badge-ujian-terpilih"
                            class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-bold">
                            Sesi: {{ $ujianUtama->nama_ujian ?? ($ujianUtama->mapel->nama_mapel ?? 'Ujian CBT') }}
                        </span>
                    @endif
                </div>

                <form action="{{ $actionFormUjian }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="ujian_id" id="input-ujian-id"
                        value="{{ old('ujian_id', $ujianUtama?->id) }}">

                    <!-- Tata Tertib Area -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            <i class="fa-solid fa-clipboard-list text-blue-600 mr-1"></i> Tata Tertib Ujian
                        </label>
                        <div id="box-tata-tertib"
                            class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 h-32 overflow-y-auto custom-scrollbar leading-relaxed">
                            @if (isset($aturan_global) && $aturan_global != '')
                                {!! nl2br(e($aturan_global)) !!}
                            @elseif ($ujianUtama && !empty($ujianUtama->aturan_ujian))
                                {!! nl2br(e($ujianUtama->aturan_ujian)) !!}
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
                        <label for="input-token" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            Token Ujian
                        </label>
                        <div class="relative max-w-sm">
                            <span
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 z-10">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input id="input-token" type="text" name="token" required value="{{ old('token') }}"
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

            <!-- KOLOM KANAN: Informasi Jadwal Ujian Khusus Kelas Siswa -->
            <div class="lg:col-span-5 bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Jadwal Ujian Kelas</h2>
                        <p class="text-[11px] text-slate-500">Khusus {{ $siswa->kelas->nama_kelas ?? 'Kelas Anda' }}</p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Real-time
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($daftarUjian as $ujian)
                        @php
                            $pengerjaan = isset($riwayatPengerjaan) ? $riwayatPengerjaan->get($ujian->id) : null;
                            $sudahSelesai = $pengerjaan && ($pengerjaan->status === 'selesai' || !empty($pengerjaan->waktu_selesai));
                            $sedangMengerjakan = $pengerjaan && !$sudahSelesai;
                            $isAktif = ($ujian->status ?? 'aktif') === 'aktif';
                            $namaSesi = $ujian->nama_ujian ?? $ujian->judul ?? ($ujian->mapel->nama_mapel ?? 'Ujian CBT');
                            $isTerpilih = (string) old('ujian_id', $ujianUtama?->id) === (string) $ujian->id;

                            $nilaiDirilis = method_exists($ujian, 'isNilaiDirilis')
                                ? $ujian->isNilaiDirilis()
                                : (bool) ($ujian->tampilkan_nilai ?? false);
                        @endphp

                        <div data-ujian-card="{{ $ujian->id }}" data-ujian-nama="{{ $namaSesi }}"
                            data-ujian-aturan="{{ $ujian->aturan_ujian ?? '' }}" onclick="pilihUjian(this)"
                            class="p-4 border rounded-xl transition group cursor-pointer {{ $isTerpilih ? 'ring-2 ring-blue-500 border-blue-400 bg-blue-50/20' : ($isAktif && !$sudahSelesai ? 'border-emerald-200 bg-emerald-50/30 hover:bg-white hover:border-emerald-400' : 'border-slate-200 bg-slate-50 hover:bg-white') }}">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition">
                                    {{ $namaSesi }}
                                </h3>

                                @if ($sudahSelesai)
                                    <span
                                        class="shrink-0 px-2 py-0.5 bg-indigo-100 text-indigo-800 border border-indigo-200 rounded text-[10px] font-extrabold uppercase tracking-wide">
                                        Selesai
                                    </span>
                                @elseif ($sedangMengerjakan)
                                    <span
                                        class="shrink-0 px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-200 rounded text-[10px] font-extrabold uppercase tracking-wide">
                                        Lanjutkan
                                    </span>
                                @elseif ($isAktif)
                                    <span
                                        class="shrink-0 px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded text-[10px] font-extrabold uppercase tracking-wide">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="shrink-0 px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[10px] font-extrabold uppercase tracking-wide">
                                        Terjadwal
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-col gap-1.5 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-book w-4 text-center text-slate-400"></i>
                                    <span
                                        class="font-semibold text-emerald-700">{{ $ujian->mapel->nama_mapel ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-users w-4 text-center text-slate-400"></i>
                                    <span
                                        class="font-semibold text-slate-700">{{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}</span>
                                </div>
                                @if (!empty($ujian->waktu_mulai))
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar w-4 text-center text-slate-400"></i>
                                        <span>
                                            {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->translatedFormat('d M Y, H:i') }}
                                            @if (!empty($ujian->waktu_selesai))
                                                - {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock w-4 text-center text-slate-400"></i>
                                    <span>Durasi: <b class="text-slate-700">{{ $ujian->durasi ?? '-' }} Menit</b></span>
                                </div>
                            </div>

                            @if ($sudahSelesai)
                                <div
                                    class="mt-3 pt-2.5 border-t border-slate-200/80 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">Hasil Ujian:</span>
                                    @if ($nilaiDirilis)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-blue-600 text-white font-extrabold">
                                            <i class="fa-solid fa-award text-[10px]"></i>
                                            Nilai: {{ number_format((float) ($pengerjaan->nilai ?? 0), 2) }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold">
                                            <i class="fa-solid fa-lock text-[10px]"></i> Menunggu Rilis Nilai
                                        </span>
                                    @endif
                                </div>
                            @elseif ($isAktif)
                                <div
                                    class="mt-3 pt-2.5 border-t border-slate-200/80 flex items-center justify-between text-[11px] font-semibold text-blue-700">
                                    <span>Klik untuk memilih sesi & ketik token</span>
                                    <i class="fa-solid fa-arrow-left hidden lg:inline"></i>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div
                            class="py-8 px-4 text-center text-slate-500 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            Belum ada jadwal ujian untuk kelas {{ $siswa->kelas->nama_kelas ?? 'Anda' }} saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- BAGIAN BAWAH: Riwayat & Hasil Ujian CAT (Berdasarkan Status Rilis Nilai SuperAdmin) -->
        <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-300 shadow-xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-square-poll-vertical text-blue-600"></i>
                        <span>Hasil &amp; Nilai Ujian Saya</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Nilai akhir akan tampil secara otomatis setelah SuperAdmin merilis hasil ujian.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold">
                    Total Selesai: {{ $daftarHasil->count() }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-y border-slate-200 uppercase text-[11px]">
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-3">Nama Ujian &amp; Mapel</th>
                            <th class="py-3 px-3">Waktu Selesai</th>
                            <th class="py-3 px-3 text-center">KKM</th>
                            <th class="py-3 px-3 text-center">Nilai Akhir</th>
                            <th class="py-3 px-3 text-center">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($daftarHasil as $idx => $hasil)
                            @php
                                $ujianHasil = $hasil->ujian;
                                $isDirilis = isset($hasil->is_nilai_dirilis)
                                    ? (bool) $hasil->is_nilai_dirilis
                                    : ($ujianHasil && (method_exists($ujianHasil, 'isNilaiDirilis')
                                        ? $ujianHasil->isNilaiDirilis()
                                        : (bool) ($ujianHasil->tampilkan_nilai ?? false)));

                                $nilaiAngka = (float) ($hasil->nilai ?? 0);
                                $kkmUjian = (float) ($ujianHasil->kkm ?? 75);
                                $lulusKkm = $nilaiAngka >= $kkmUjian;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 text-center font-semibold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">
                                        {{ $ujianHasil->nama_ujian ?? 'Ujian CAT #' . $hasil->ujian_id }}
                                    </div>
                                    <div class="text-[11px] text-emerald-700 font-medium">
                                        {{ $ujianHasil->mapel->nama_mapel ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    @if (!empty($hasil->waktu_selesai))
                                        {{ \Carbon\Carbon::parse($hasil->waktu_selesai)->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                    @elseif (!empty($hasil->updated_at))
                                        {{ \Carbon\Carbon::parse($hasil->updated_at)->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-semibold text-slate-600">
                                    {{ number_format($kkmUjian, 0) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($isDirilis)
                                        <span
                                            class="inline-block px-3 py-1 rounded-lg font-extrabold text-sm {{ $lulusKkm ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                            {{ number_format($nilaiAngka, 2) }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold">
                                            <i class="fa-solid fa-eye-slash text-[10px]"></i>
                                            Belum Dirilis
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($isDirilis)
                                        @if ($lulusKkm)
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">
                                                <i class="fa-solid fa-check"></i> Tuntas
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-extrabold uppercase">
                                                <i class="fa-solid fa-rotate-right"></i> Belum Tuntas
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Dalam penilaian / dikunci</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                    Belum ada riwayat ujian yang selesai dikerjakan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="py-4 text-center text-xs text-slate-500 border-t border-slate-200 bg-white mt-auto">
        &copy; {{ date('Y') }} Copyright: MahesAPP. All rights reserved.
    </footer>

    <!-- SCRIPT UNTUK TOGGLE TOMBOL SUBMIT & PILIH JADWAL UJIAN -->
    <script>
        const defaultTataTertibHtml = `1. Peserta ujian wajib menjunjung tinggi kejujuran.<br>
2. Dilarang membuka tab lain, browser, atau aplikasi tambahan selama ujian berlangsung (sistem layar penuh/fullscreen akan aktif).<br>
3. Jika keluar dari layar ujian lebih dari batas yang ditentukan, sistem otomatis menyimpan jawaban dan mengakhiri sesi ujian Anda.<br>
4. Waktu pengerjaan akan terus berjalan meskipun Anda keluar sistem atau perangkat mati.<br>
5. Dilarang mendokumentasikan atau menyebarkan soal ujian dalam bentuk apapun.<br>
6. Pastikan koneksi internet stabil sebelum mulai mengerjakan.`;

        function toggleSubmitButton() {
            const checkbox = document.getElementById('persetujuan');
            const btnSubmit = document.getElementById('btn-submit');

            if (checkbox && checkbox.checked) {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
                btnSubmit.classList.add('bg-blue-700', 'hover:bg-blue-800', 'active:bg-blue-900', 'text-white', 'cursor-pointer');
            } else if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.classList.add('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
                btnSubmit.classList.remove('bg-blue-700', 'hover:bg-blue-800', 'active:bg-blue-900', 'text-white', 'cursor-pointer');
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }

        function pilihUjian(cardEl) {
            if (!cardEl) return;

            const ujianId = cardEl.getAttribute('data-ujian-id') || '';
            const ujianNama = cardEl.getAttribute('data-ujian-nama') || 'Ujian CBT';
            const ujianAturan = cardEl.getAttribute('data-ujian-aturan') || '';

            const hiddenUjian = document.getElementById('input-ujian-id');
            if (hiddenUjian && ujianId) {
                hiddenUjian.value = ujianId;
            }

            const badgeEl = document.getElementById('badge-ujian-terpilih');
            if (badgeEl) {
                badgeEl.textContent = `Sesi: ${ujianNama}`;
            }

            const aturanBox = document.getElementById('box-tata-tertib');
            if (aturanBox) {
                aturanBox.innerHTML = ujianAturan.trim() !== ''
                    ? escapeHtml(ujianAturan)
                    : defaultTataTertibHtml;
            }

            document.querySelectorAll('[data-ujian-card]').forEach(el => {
                el.classList.remove('ring-2', 'ring-blue-500', 'border-blue-400', 'bg-blue-50/20');
            });
            cardEl.classList.add('ring-2', 'ring-blue-500', 'border-blue-400', 'bg-blue-50/20');

            const inputToken = document.getElementById('input-token');
            if (inputToken) {
                inputToken.scrollIntoView({ behavior: 'smooth', block: 'center' });
                inputToken.focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            toggleSubmitButton();

            const inputToken = document.getElementById('input-token');
            if (inputToken) {
                inputToken.addEventListener('input', function () {
                    this.value = this.value.toUpperCase().replace(/\s+/g, '');
                });
            }
        });
    </script>
</body>

</html>