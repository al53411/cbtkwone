@extends('layouts.superadmin')

@php
    /** @var \App\Models\Ujian|null $ujian */
    /** @var \App\Models\Ujian|null $ujianItem */
    $ujianItem = $ujian ?? null;

    $tipePgAktif = (bool) old(
        'tampil_pg',
        $ujianItem ? (method_exists($ujianItem, 'isPgAktif') ? $ujianItem->isPgAktif() : (bool) ($ujianItem->tampil_pg ?? true)) : true
    );
    $tipeJodohAktif = (bool) old(
        'tampil_menjodohkan',
        $ujianItem ? (method_exists($ujianItem, 'isMenjodohkanAktif') ? $ujianItem->isMenjodohkanAktif() : (bool) ($ujianItem->tampil_menjodohkan ?? false)) : false
    );
    $tipeEsaiAktif = (bool) old(
        'tampil_esai',
        $ujianItem ? (method_exists($ujianItem, 'isEsaiAktif') ? $ujianItem->isEsaiAktif() : (bool) ($ujianItem->tampil_esai ?? false)) : false
    );

    $acakSoalAktif = (bool) old(
        'acak_soal',
        $ujianItem ? (method_exists($ujianItem, 'isSoalAcak') ? $ujianItem->isSoalAcak() : (bool) ($ujianItem->acak_soal ?? false)) : false
    );
    $acakJawabanAktif = (bool) old(
        'acak_jawaban',
        $ujianItem ? (method_exists($ujianItem, 'isJawabanAcak') ? $ujianItem->isJawabanAcak() : (bool) ($ujianItem->acak_jawaban ?? false)) : false
    );
    $rilisNilaiAktif = (bool) old(
        'tampilkan_nilai',
        $ujianItem ? (method_exists($ujianItem, 'isNilaiDirilis') ? $ujianItem->isNilaiDirilis() : (bool) ($ujianItem->tampilkan_nilai ?? false)) : false
    );
    $tampilSoalAktif = (bool) old(
        'tampilkan_soal',
        $ujianItem ? (method_exists($ujianItem, 'isSoalDitampilkan') ? $ujianItem->isSoalDitampilkan() : (bool) ($ujianItem->tampilkan_soal ?? false)) : false
    );
@endphp

@section('title', 'Pengaturan Ujian - ' . ($ujianItem?->nama_ujian ?? 'Sesi Ujian'))
@section('header', 'Pengaturan Ujian')

@section('content')
    <div class="w-full space-y-6">

        <!-- Breadcrumb & Header Bar -->
        <div
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('superadmin.ujian.index') }}" class="hover:text-blue-600 transition font-medium">
                        <i class="fa-solid fa-calendar-days mr-1"></i> Jadwal & Monitoring Ujian
                    </a>
                    <span>/</span>
                    <span class="text-slate-600 font-semibold">Pengaturan Ujian</span>
                </div>
                <h2 class="text-base font-bold text-slate-800">{{ $ujianItem?->nama_ujian ?? 'Sesi Ujian' }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Mapel: <span class="font-semibold text-emerald-600">{{ $ujianItem?->mapel?->nama_mapel ?? '-' }}</span>
                    &bull;
                    Kelas: <span
                        class="font-semibold text-slate-700">{{ $ujianItem?->kelas?->nama_kelas ?? 'Semua Kelas' }}</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('superadmin.ujian.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>

                @if ($ujianItem?->id && \Illuminate\Support\Facades\Route::has('admin.ujian.monitoring'))
                    <a href="{{ route('admin.ujian.monitoring', $ujianItem->id) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 text-xs font-semibold transition">
                        <i class="fa-solid fa-desktop"></i> Monitoring
                    </a>
                @endif

                @if ($ujianItem?->id && \Illuminate\Support\Facades\Route::has('superadmin.ujian.rekap'))
                    <a href="{{ route('superadmin.ujian.rekap', ['ujian_id' => $ujianItem->id]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 text-xs font-semibold transition">
                        <i class="fa-solid fa-chart-column"></i> Rekap Nilai
                    </a>
                @endif
            </div>
        </div>

        <!-- Alert Notifikasi & Error Validasi -->
        @if (session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div
                class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i> Periksa kembali isian pengaturan:
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Pengaturan Lengkap -->
        @if ($ujianItem?->id)
            <form method="POST" action="{{ route('superadmin.ujian.update', $ujianItem->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Kolom Kiri (2/3) -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- 1. Identitas & Jadwal Ujian -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-clock text-blue-600"></i> Informasi Sesi & Jadwal
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Atur nama sesi, mata pelajaran, kelas sasaran, waktu
                                    pelaksanaan, dan durasi.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 mb-1.5">Nama Ujian <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" name="nama_ujian" value="{{ old('nama_ujian', $ujianItem->nama_ujian) }}"
                                        required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span
                                            class="text-rose-500">*</span></label>
                                    <select name="mapel_id" required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Pilih Mata Pelajaran --</option>
                                        @foreach ($listMapel ?? ($mapels ?? []) as $m)
                                            <option value="{{ $m->id }}" @selected(old('mapel_id', $ujianItem->mapel_id) == $m->id)>
                                                {{ $m->nama_mapel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1.5">Kelas Sasaran <span
                                            class="text-rose-500">*</span></label>
                                    <select name="kelas_id" required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach ($listKelas ?? ($kelases ?? []) as $k)
                                            <option value="{{ $k->id }}" @selected(old('kelas_id', $ujianItem->kelas_id) == $k->id)>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1.5">Waktu Mulai <span
                                            class="text-rose-500">*</span></label>
                                    <input type="datetime-local" name="waktu_mulai"
                                        value="{{ old('waktu_mulai', $ujianItem->waktu_mulai ? \Carbon\Carbon::parse($ujianItem->waktu_mulai)->format('Y-m-d\TH:i') : '') }}"
                                        required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1.5">Waktu Selesai <span
                                            class="text-rose-500">*</span></label>
                                    <input type="datetime-local" name="waktu_selesai"
                                        value="{{ old('waktu_selesai', $ujianItem->waktu_selesai ? \Carbon\Carbon::parse($ujianItem->waktu_selesai)->format('Y-m-d\TH:i') : '') }}"
                                        required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1.5">Durasi Pengerjaan (Menit) <span
                                            class="text-rose-500">*</span></label>
                                    <input type="number" name="durasi" value="{{ old('durasi', $ujianItem->durasi ?? 60) }}"
                                        min="1" max="600" required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1.5">KKM / Nilai Kelulusan</label>
                                    <input type="number" step="0.01" name="kkm" value="{{ old('kkm', $ujianItem->kkm ?? 75) }}"
                                        min="0" max="100"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 mb-1.5">Aturan / Catatan Ujian
                                        (Opsional)</label>
                                    <textarea name="aturan_ujian" rows="2"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">{{ old('aturan_ujian', $ujianItem->aturan_ujian ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Pengaturan Tipe Soal, Bobot (Total 100%) & Pengacakan -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                            <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <i class="fa-solid fa-sliders text-indigo-600"></i> Tipe Soal, Bobot Nilai & Pengacakan
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Total bobot tipe soal aktif harus berjumlah tepat
                                        100%.</p>
                                </div>
                                <span id="totalBobotBadge"
                                    class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">
                                    Total Bobot: <span id="totalBobotAngka">100</span>%
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="tampil_pg" value="1" @checked($tipePgAktif)
                                            onchange="hitungTotalBobot()"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="font-bold text-slate-700">Pilihan Ganda</span>
                                    </label>
                                    <div>
                                        <label class="block text-[11px] text-slate-500 mb-1">Bobot PG (%)</label>
                                        <input type="number" id="bobot_pg" name="bobot_pg" min="0" max="100"
                                            value="{{ old('bobot_pg', $ujianItem->bobot_pg ?? 100) }}"
                                            oninput="hitungTotalBobot()"
                                            class="w-full rounded-lg border-slate-200 bg-white p-2 text-slate-700 font-semibold">
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="tampil_menjodohkan" value="1" @checked($tipeJodohAktif)
                                            onchange="hitungTotalBobot()"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="font-bold text-slate-700">Menjodohkan</span>
                                    </label>
                                    <div>
                                        <label class="block text-[11px] text-slate-500 mb-1">Bobot Menjodohkan (%)</label>
                                        <input type="number" id="bobot_menjodohkan" name="bobot_menjodohkan" min="0" max="100"
                                            value="{{ old('bobot_menjodohkan', $ujianItem->bobot_menjodohkan ?? 0) }}"
                                            oninput="hitungTotalBobot()"
                                            class="w-full rounded-lg border-slate-200 bg-white p-2 text-slate-700 font-semibold">
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="tampil_esai" value="1" @checked($tipeEsaiAktif)
                                            onchange="hitungTotalBobot()"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="font-bold text-slate-700">Uraian / Esai</span>
                                    </label>
                                    <div>
                                        <label class="block text-[11px] text-slate-500 mb-1">Bobot Esai (%)</label>
                                        <input type="number" id="bobot_esai" name="bobot_esai" min="0" max="100"
                                            value="{{ old('bobot_esai', $ujianItem->bobot_esai ?? 0) }}"
                                            oninput="hitungTotalBobot()"
                                            class="w-full rounded-lg border-slate-200 bg-white p-2 text-slate-700 font-semibold">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                                <label
                                    class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200/80 hover:border-slate-300 cursor-pointer">
                                    <input type="checkbox" name="acak_soal" value="1" @checked($acakSoalAktif)
                                        class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span>
                                        <span class="font-semibold text-slate-700 block">Acak Urutan Soal</span>
                                        <span class="text-[11px] text-slate-500">Setiap peserta mendapat urutan nomor soal
                                            berbeda.</span>
                                    </span>
                                </label>

                                <label
                                    class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200/80 hover:border-slate-300 cursor-pointer">
                                    <input type="checkbox" name="acak_jawaban" value="1" @checked($acakJawabanAktif)
                                        class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span>
                                        <span class="font-semibold text-slate-700 block">Acak Pilihan Jawaban</span>
                                        <span class="text-[11px] text-slate-500">Opsi jawaban diacak khusus pada soal pilihan
                                            ganda.</span>
                                    </span>
                                </label>

                                <label
                                    class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200/80 hover:border-slate-300 cursor-pointer">
                                    <input type="checkbox" name="tampilkan_nilai" value="1" @checked($rilisNilaiAktif)
                                        class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span>
                                        <span class="font-semibold text-slate-700 block">Rilis Nilai</span>
                                        <span class="text-[11px] text-slate-500">Buka akses tampilan nilai sesuai pengaturan
                                            sistem.</span>
                                    </span>
                                </label>

                                <label
                                    class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200/80 hover:border-slate-300 cursor-pointer">
                                    <input type="checkbox" name="tampilkan_soal" value="1" @checked($tampilSoalAktif)
                                        class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span>
                                        <span class="font-semibold text-slate-700 block">Buka Akses Butir Soal</span>
                                        <span class="text-[11px] text-slate-500">Izinkan melihat butir soal pada panel
                                            terkait.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- Kolom Kanan (1/3) -->
                    <div class="space-y-6">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4 text-xs">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-key text-amber-500"></i> Token & Status Sesi
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Kelola token akses peserta dan status aktif ujian.</p>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Status Ujian <span
                                        class="text-rose-500">*</span></label>
                                <select name="status" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 font-semibold focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                                    <option value="draft" @selected(old('status', $ujianItem->status) === 'draft')>Draft (Belum
                                        Dibuka)</option>
                                    <option value="aktif" @selected(old('status', $ujianItem->status) === 'aktif')>Aktif
                                        (Berlangsung)</option>
                                    <option value="selesai" @selected(old('status', $ujianItem->status) === 'selesai')>Selesai
                                        (Ditutup)</option>
                                </select>
                            </div>

                            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label for="inputTokenUjian" class="font-bold text-indigo-950">Token Akses Ujian <span
                                            class="text-rose-500">*</span></label>
                                    <button type="button" onclick="copyTokenUjian()"
                                        class="text-[11px] font-semibold text-indigo-700 hover:text-indigo-900 inline-flex items-center gap-1 cursor-pointer">
                                        <i class="fa-regular fa-copy"></i> Salin
                                    </button>
                                </div>

                                <div class="flex gap-2">
                                    <input type="text" id="inputTokenUjian" name="token"
                                        value="{{ old('token', $ujianItem->token) }}" maxlength="10" required
                                        class="w-full uppercase font-mono text-base font-extrabold tracking-widest text-center rounded-xl border-indigo-200 bg-white p-2.5 text-indigo-800 focus:border-indigo-500 focus:ring-indigo-500">

                                    <button type="button" id="btnGenerateToken" onclick="generateTokenUjian()"
                                        class="px-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer"
                                        title="Generate Token Baru">
                                        <i class="fa-solid fa-shuffle"></i>
                                        <span>Generate</span>
                                    </button>
                                </div>

                                <p id="tokenGenerateStatus" class="text-[11px] text-indigo-700/80 min-h-[16px]">
                                    Klik <b>Generate</b> untuk membuat token baru secara otomatis.
                                </p>
                            </div>

                            <div class="pt-2 space-y-2.5">
                                <button type="submit"
                                    class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition inline-flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-floppy-disk"></i> Simpan Semua Pengaturan
                                </button>

                                <a href="{{ route('superadmin.ujian.index') }}"
                                    class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition inline-flex items-center justify-center gap-2">
                                    Batal & Kembali ke Daftar
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        @else
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                Data sesi ujian tidak ditemukan.
            </div>
        @endif

    </div>

    <script>
        function hitungTotalBobot() {
            const pg = parseInt(document.getElementById('bobot_pg')?.value || '0', 10) || 0;
            const jodoh = parseInt(document.getElementById('bobot_menjodohkan')?.value || '0', 10) || 0;
            const esai = parseInt(document.getElementById('bobot_esai')?.value || '0', 10) || 0;
            const total = pg + jodoh + esai;

            const angkaEl = document.getElementById('totalBobotAngka');
            const badgeEl = document.getElementById('totalBobotBadge');
            if (angkaEl) angkaEl.textContent = total;

            if (badgeEl) {
                badgeEl.className = 'px-3 py-1 rounded-lg text-xs font-bold ' + (
                    total === 100
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-rose-50 text-rose-700 border border-rose-200'
                );
            }
        }

        async function generateTokenUjian() {
            const inputEl = document.getElementById('inputTokenUjian');
            const statusEl = document.getElementById('tokenGenerateStatus');
            const btnEl = document.getElementById('btnGenerateToken');

            if (btnEl) btnEl.disabled = true;
            if (statusEl) statusEl.textContent = 'Menghasilkan token baru...';

            try {
                const res = await fetch("{{ route('superadmin.ujian.generate-token') }}", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    const tokenServer = data.token || data.data?.token || '';
                    if (tokenServer) {
                        inputEl.value = String(tokenServer).toUpperCase();
                        if (statusEl) statusEl.textContent = 'Token baru berhasil digenerate. Jangan lupa klik Simpan.';
                        return;
                    }
                }
            } catch (err) {
                // Fallback lokal
            } finally {
                if (btnEl) btnEl.disabled = false;
            }

            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let token = '';
            for (let i = 0; i < 6; i++) {
                token += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            inputEl.value = token;
            if (statusEl) statusEl.textContent = 'Token acak lokal dibuat. Jangan lupa klik Simpan.';
        }

        function copyTokenUjian() {
            const inputEl = document.getElementById('inputTokenUjian');
            const statusEl = document.getElementById('tokenGenerateStatus');
            if (!inputEl || !inputEl.value) return;

            navigator.clipboard?.writeText(inputEl.value.trim().toUpperCase()).then(() => {
                if (statusEl) statusEl.textContent = `Token "${inputEl.value.trim().toUpperCase()}" disalin ke clipboard.`;
            }).catch(() => {
                inputEl.select();
                document.execCommand('copy');
                if (statusEl) statusEl.textContent = 'Token disalin ke clipboard.';
            });
        }

        document.addEventListener('DOMContentLoaded', hitungTotalBobot);
    </script>
@endsection