@extends('layouts.guru')

@section('title', $profilSekolah->nama_sekolah ?? 'SDN Kawu 1')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto px-2 sm:px-4">

    <!-- Header Halaman & Filter / Form Cetak Rekap Word -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Jurnal Pembelajaran Guru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catatan aktivitas KBM harian yang terintegrasi dengan validasi Kepala Sekolah.</p>
        </div>

        <!-- Form Filter & Cetak Word -->
        <form action="{{ route('guru.jurnal.cetak') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
            <div>
                <select name="bulan" class="text-xs rounded-xl border-slate-200 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Semua Bulan --</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ request('bulan') == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <select name="tahun" class="text-xs rounded-xl border-slate-200 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Semua Tahun --</option>
                    @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <button type="submit"
                class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl text-xs transition flex items-center justify-center gap-2 shadow-sm whitespace-nowrap">
                <span>📄</span> Cetak Rekap Word
            </button>
        </form>
    </div>

    <!-- Alert Notifikasi Sukses -->
    @if(session('success'))
    <div class="p-3.5 sm:p-4 text-xs sm:text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-base">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Alert Notifikasi Error -->
    @if(session('error'))
    <div class="p-3.5 sm:p-4 text-xs sm:text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-base">🚫</span>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Alert Notifikasi Error Validation -->
    @if($errors->any())
    <div class="p-3.5 sm:p-4 text-xs sm:text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200">
        <div class="font-bold mb-1 flex items-center gap-1.5">
            <span>⚠️</span> Terjadi kesalahan input:
        </div>
        <ul class="list-disc list-inside space-y-1 text-xs pl-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form Input Jurnal -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span>📝</span> Input Jurnal Mengajar Baru
        </h2>

        <form action="{{ route('guru.jurnal.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Mengajar</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                        class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                    <select name="kelas_id" required
                        class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelases as $kelas)
                        <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mata Pelajaran</label>
                    <select name="mapel" id="mapel" class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->nama_mapel }}" {{ old('mapel') == $mapel->nama_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jam Ke -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jam Ke-</label>
                    <input type="text" name="jam_ke" value="{{ old('jam_ke') }}"
                        placeholder="Contoh: 1-2 (07.00 - 08.10)" required
                        class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
                <!-- Materi / TP -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Materi / TP Pembelajaran</label>
                    <textarea name="materi" rows="3" placeholder="Tuliskan materi pembelajaran hari ini..." required
                        class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 p-3">{{ old('materi') }}</textarea>
                </div>

                <!-- Kegiatan Pembelajaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kegiatan Pembelajaran</label>
                    <textarea name="kegiatan" rows="3" placeholder="Deskripsi aktivitas/metode pembelajaran..." required
                        class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 p-3">{{ old('kegiatan') }}</textarea>
                </div>
            </div>

            <!-- Keterangan / Catatan Selesai -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                    placeholder="Contoh: Selesai / Dilanjutkan minggu depan"
                    class="w-full text-base sm:text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <span>💾</span> Simpan Jurnal
                </button>
            </div>

        </form>
    </div>

    <!-- Riwayat Jurnal -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span>📖</span> Riwayat Jurnal Pembelajaran
        </h2>

        <!-- TAMPILAN MOBILE (Card View) -->
        <div class="block md:hidden space-y-3.5">
            @forelse($jurnals as $jurnal)
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                <div class="flex items-start justify-between gap-2 border-b border-slate-200/80 pb-2.5">
                    <div>
                        <div class="font-bold text-sm text-slate-800">{{ $jurnal->hari }}, {{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d M Y') }}</div>
                        <div class="text-xs text-indigo-600 font-medium">Jam Ke: {{ $jurnal->jam_ke }}</div>
                    </div>
                    <div>
                        @if($jurnal->status_validasi === 'Disetujui')
                        <span class="px-2.5 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 rounded-full inline-block">
                            Disetujui
                        </span>
                        @elseif($jurnal->status_validasi === 'Ditolak')
                        <span class="px-2.5 py-0.5 text-[11px] font-semibold bg-rose-100 text-rose-800 rounded-full inline-block">
                            Ditolak
                        </span>
                        @else
                        <span class="px-2.5 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 rounded-full inline-block">
                            Pending
                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded border border-blue-100">
                        {{ $jurnal->kelas->nama_kelas ?? 'Kelas -' }}
                    </span>
                    <span class="font-semibold text-xs text-slate-800">{{ $jurnal->mapel }}</span>
                </div>

                <div class="text-xs space-y-1.5 text-slate-700">
                    <div>
                        <span class="font-semibold text-slate-500 block">Materi / TP:</span>
                        <p class="text-slate-800">{{ $jurnal->materi }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-500 block">Kegiatan:</span>
                        <p class="text-slate-600">{{ $jurnal->kegiatan }}</p>
                    </div>
                    @if($jurnal->keterangan)
                    <div class="italic text-slate-500 pt-1">
                        Ket: {{ $jurnal->keterangan }}
                    </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-200/60 flex justify-end items-center">
                    @if($jurnal->status_validasi !== 'Disetujui')
                    <form action="{{ route('guru.jurnal.destroy', $jurnal->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurnal ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-lg transition font-medium flex items-center gap-1 border border-rose-200">
                            <span>🗑️</span> Hapus
                        </button>
                    </form>
                    @else
                    <span class="text-slate-400 text-xs font-medium flex items-center gap-1">🔒 Terkunci</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-xl">
                Belum ada riwayat jurnal mengajar.
            </div>
            @endforelse
        </div>

        <!-- TAMPILAN DESKTOP & TABLET (Table View) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Hari, Tanggal & Jam</th>
                        <th class="px-4 py-3">Kelas & Mapel</th>
                        <th class="px-4 py-3">Materi / TP</th>
                        <th class="px-4 py-3">Kegiatan</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3 text-center">Status Validasi</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jurnals as $jurnal)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-4 py-4 font-medium text-slate-900 whitespace-nowrap">
                            <div class="font-bold text-slate-800">{{ $jurnal->hari }}</div>
                            <div class="text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d M Y') }}</div>
                            <div class="text-xs text-indigo-600 font-medium">Jam: {{ $jurnal->jam_ke }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded border border-blue-100 block w-max mb-1">
                                {{ $jurnal->kelas->nama_kelas ?? 'Kelas -' }}
                            </span>
                            <span class="font-semibold text-slate-800">{{ $jurnal->mapel }}</span>
                        </td>
                        <td class="px-4 py-4 max-w-xs">
                            <p class="line-clamp-2 text-slate-800 font-medium">{{ $jurnal->materi }}</p>
                        </td>
                        <td class="px-4 py-4 max-w-xs">
                            <p class="line-clamp-2 text-slate-600">{{ $jurnal->kegiatan }}</p>
                        </td>
                        <td class="px-4 py-4 italic text-slate-500 text-xs whitespace-nowrap">
                            {{ $jurnal->keterangan ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($jurnal->status_validasi === 'Disetujui')
                            <span class="px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                Disetujui
                            </span>
                            @elseif($jurnal->status_validasi === 'Ditolak')
                            <span class="px-3 py-1 text-xs font-semibold bg-rose-100 text-rose-800 rounded-full">
                                Ditolak
                            </span>
                            @else
                            <span class="px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">
                                Pending
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($jurnal->status_validasi !== 'Disetujui')
                            <form action="{{ route('guru.jurnal.destroy', $jurnal->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurnal ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                    title="Hapus Jurnal">
                                    🗑️
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-xs">🔒 Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            Belum ada riwayat jurnal mengajar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection