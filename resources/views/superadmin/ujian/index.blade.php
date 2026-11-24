@extends('layouts.superadmin')

@section('title', 'Jadwal & Monitoring Ujian')
@section('header', 'Jadwal & Monitoring Ujian')

@section('content')
    <div class="w-full space-y-6">

        <!-- Alert Notifikasi & Error Validasi -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan input:
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter Pencarian, Kelas & Status -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('superadmin.ujian.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cari Ujian / Mapel</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama ujian atau mapel..."
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kelas</label>
                    <select name="kelas_id"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($listKelas ?? ($kelases ?? []) as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Ujian</label>
                    <select name="status"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Semua Status --</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif (Berlangsung)</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-slate-800 transition cursor-pointer">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('superadmin.ujian.index') }}"
                        class="px-3 py-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Jadwal Ujian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Daftar Sesi Ujian</h2>
                    <p class="text-xs text-slate-500">Kelola sesi ujian, buka pengaturan, pantau peserta live, lihat rekap nilai, atau hapus sesi.</p>
                </div>

                <button type="button" onclick="openModalTambahSesi()"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Tambah Sesi Ujian
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">Nama Ujian & Mapel</th>
                            <th class="py-3.5 px-4">Kelas</th>
                            <th class="py-3.5 px-4">Waktu & Durasi</th>
                            <th class="py-3.5 px-4 text-center">Token</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($ujians as $index => $ujianItem)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    {{ $ujians->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $ujianItem->nama_ujian }}</span>
                                    <span class="text-[11px] text-emerald-600 font-semibold">
                                        <i class="fa-solid fa-book-open mr-1"></i>{{ $ujianItem->mapel->nama_mapel ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">
                                        {{ $ujianItem->kelas->nama_kelas ?? 'Semua Kelas' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                    <div class="font-semibold text-slate-700">
                                        {{ $ujianItem->waktu_mulai ? \Carbon\Carbon::parse($ujianItem->waktu_mulai)->format('d M Y, H:i') : '-' }}
                                        @if ($ujianItem->waktu_selesai)
                                            &ndash; {{ \Carbon\Carbon::parse($ujianItem->waktu_selesai)->format('H:i') }}
                                        @endif
                                    </div>
                                    <div>Durasi: <b class="text-slate-700">{{ $ujianItem->durasi }} Menit</b></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono font-bold tracking-wider">
                                        {{ $ujianItem->token }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($ujianItem->status === 'aktif')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-ping"></span> AKTIF
                                        </span>
                                    @elseif ($ujianItem->status === 'selesai')
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold">
                                            SELESAI
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                            DRAFT
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                        <a href="{{ route('superadmin.ujian.edit', $ujianItem->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-white border border-amber-200 rounded-lg text-xs font-semibold transition gap-1.5"
                                            title="Pengaturan Ujian">
                                            <i class="fa-solid fa-sliders"></i> Settings
                                        </a>

                                        @if (\Illuminate\Support\Facades\Route::has('admin.ujian.monitoring'))
                                            <a href="{{ route('admin.ujian.monitoring', $ujianItem->id) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 rounded-lg text-xs font-semibold transition gap-1.5"
                                                title="Monitoring Peserta Live">
                                                <i class="fa-solid fa-desktop"></i> Monitoring
                                            </a>
                                        @endif

                                        <a href="{{ route('superadmin.ujian.rekap', ['ujian_id' => $ujianItem->id]) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 rounded-lg text-xs font-semibold transition gap-1.5"
                                            title="Lihat Rekap Nilai">
                                            <i class="fa-solid fa-chart-column"></i> Rekap
                                        </a>

                                        <form method="POST" action="{{ route('superadmin.ujian.destroy', $ujianItem->id) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus sesi ujian \'{{ addslashes($ujianItem->nama_ujian) }}\'? Semua data jawaban dan nilai terkait sesi ini juga dapat terdampak.');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 rounded-lg text-xs font-semibold transition gap-1.5 cursor-pointer"
                                                title="Hapus Sesi Ujian">
                                                <i class="fa-solid fa-trash-can"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-2xl mb-2 block text-slate-300"></i>
                                    Belum ada jadwal ujian yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $ujians->links() }}
            </div>
        </div>

    </div>

    <!-- Modal Tambah Sesi Ujian -->
    <div id="modalTambahSesi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-2xl rounded-2xl border border-slate-200 shadow-xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Tambah Sesi Ujian Baru</h3>
                    <p class="text-[11px] text-slate-500">Atur jadwal, mapel, kelas, token, tipe soal, bobot (100%), dan KKM.</p>
                </div>
                <button type="button" onclick="closeModalTambahSesi()"
                    class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('superadmin.ujian.store') }}" class="p-5 space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Sesi / Ujian <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_ujian" value="{{ old('nama_ujian') }}" required
                        placeholder="Contoh: Sumatif Tengah Semester 1 - Matematika"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="mapel_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach ($listMapel ?? ($mapels ?? []) as $m)
                                <option value="{{ $m->id }}" @selected(old('mapel_id') == $m->id)>
                                    {{ $m->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kelas <span class="text-rose-500">*</span></label>
                        <select name="kelas_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($listKelas ?? ($kelases ?? []) as $k)
                                <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Waktu Mulai <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Waktu Selesai <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Durasi (Mnt) <span class="text-rose-500">*</span></label>
                        <input type="number" name="durasi" value="{{ old('durasi', 60) }}" min="1" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">KKM</label>
                        <input type="number" step="0.01" name="kkm" value="{{ old('kkm', 75) }}" min="0" max="100"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Token Ujian</label>
                        <div class="flex gap-1">
                            <input type="text" id="inputTokenUjian" name="token" value="{{ old('token') }}" maxlength="10"
                                placeholder="ABCDEF"
                                class="w-full uppercase font-mono font-bold tracking-wider rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" id="btnGenerateToken" onclick="generateTokenUjian()"
                                class="px-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold transition cursor-pointer"
                                title="Generate Token">
                                <i class="fa-solid fa-shuffle"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                            <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                            <option value="selesai" @selected(old('status') === 'selesai')>Selesai</option>
                        </select>
                    </div>
                </div>

                <!-- Komposisi Tipe Soal & Bobot (Total 100%) -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">
                            <i class="fa-solid fa-sliders mr-1 text-indigo-600"></i> Tipe Soal & Bobot Nilai (Total 100%)
                        </span>
                        <span id="modalTotalBobot" class="text-[11px] font-bold text-slate-700">Total: 100%</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200 space-y-1.5">
                            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="tampil_pg" value="1" @checked(old('tampil_pg', 1)) onchange="hitungModalBobot()"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>PG</span>
                            </label>
                            <input type="number" id="modal_bobot_pg" name="bobot_pg" min="0" max="100"
                                value="{{ old('bobot_pg', 100) }}" oninput="hitungModalBobot()"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 p-1.5 text-xs font-semibold">
                        </div>

                        <div class="bg-white p-2.5 rounded-lg border border-slate-200 space-y-1.5">
                            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="tampil_menjodohkan" value="1" @checked(old('tampil_menjodohkan')) onchange="hitungModalBobot()"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Menjodohkan</span>
                            </label>
                            <input type="number" id="modal_bobot_jodoh" name="bobot_menjodohkan" min="0" max="100"
                                value="{{ old('bobot_menjodohkan', 0) }}" oninput="hitungModalBobot()"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 p-1.5 text-xs font-semibold">
                        </div>

                        <div class="bg-white p-2.5 rounded-lg border border-slate-200 space-y-1.5">
                            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="tampil_esai" value="1" @checked(old('tampil_esai')) onchange="hitungModalBobot()"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Esai</span>
                            </label>
                            <input type="number" id="modal_bobot_esai" name="bobot_esai" min="0" max="100"
                                value="{{ old('bobot_esai', 0) }}" oninput="hitungModalBobot()"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 p-1.5 text-xs font-semibold">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 pt-1">
                        <label class="inline-flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                            <input type="checkbox" name="acak_soal" value="1" @checked(old('acak_soal'))
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Acak urutan soal</span>
                        </label>

                        <label class="inline-flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                            <input type="checkbox" name="acak_jawaban" value="1" @checked(old('acak_jawaban'))
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Acak pilihan jawaban</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Aturan / Catatan Ujian (Opsional)</label>
                    <textarea name="aturan_ujian" rows="2" placeholder="Catatan atau tata tertib pengerjaan..."
                        class="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">{{ old('aturan_ujian') }}</textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModalTambahSesi()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Sesi Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModalTambahSesi() {
            const modal = document.getElementById('modalTambahSesi');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const tokenInput = document.getElementById('inputTokenUjian');
            if (!tokenInput.value.trim()) {
                generateTokenUjian();
            }
            hitungModalBobot();
        }

        function closeModalTambahSesi() {
            const modal = document.getElementById('modalTambahSesi');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function hitungModalBobot() {
            const pg = parseInt(document.getElementById('modal_bobot_pg')?.value || '0', 10) || 0;
            const jodoh = parseInt(document.getElementById('modal_bobot_jodoh')?.value || '0', 10) || 0;
            const esai = parseInt(document.getElementById('modal_bobot_esai')?.value || '0', 10) || 0;
            const total = pg + jodoh + esai;

            const el = document.getElementById('modalTotalBobot');
            if (el) {
                el.textContent = `Total: ${total}%`;
                el.className = total === 100
                    ? 'text-[11px] font-bold text-emerald-700'
                    : 'text-[11px] font-bold text-rose-600';
            }
        }

        async function generateTokenUjian() {
            const inputEl = document.getElementById('inputTokenUjian');
            const btnEl = document.getElementById('btnGenerateToken');
            if (btnEl) btnEl.disabled = true;

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
        }

        document.getElementById('modalTambahSesi').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModalTambahSesi();
            }
        });

        @if ($errors->any() || session('error'))
            document.addEventListener('DOMContentLoaded', function () {
                openModalTambahSesi();
            });
        @endif
    </script>
@endsection