@extends('layouts.admin')

@section('title', 'Berita Acara Ujian CAT')
@section('page_title', 'Berita Acara Ujian CAT')

@section('content')
    <div class="max-w-6xl mx-auto space-y-5">

        <!-- Header & Filter Sesi Ujian -->
        <div class="flat-card p-5 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-signature"></i>
                    </span>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">
                            Berita Acara & Administrasi Ujian CAT
                        </h1>
                        <p class="text-xs text-slate-500">
                            Cetak Berita Acara pelaksanaan ujian dan Rekap Hasil CAT resmi per sesi ujian.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Filter Kelas & Pencarian -->
            <form method="GET" action="{{ route('admin.ujian.berita-acara-index') }}"
                class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full lg:w-auto">
                <select name="kelas_id" class="flat-input px-3 py-2 text-xs font-semibold w-full sm:w-44">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($listKelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ujian / mapel..."
                    class="flat-input px-3.5 py-2 text-xs w-full sm:w-52">

                <button type="submit"
                    class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-md text-xs font-bold inline-flex items-center justify-center gap-1.5 transition cursor-pointer shrink-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Filter</span>
                </button>

                @if(request()->filled('search') || request()->filled('kelas_id'))
                    <a href="{{ route('admin.ujian.berita-acara-index') }}"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md text-xs font-semibold transition shrink-0">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Info Proktor Aktif dari Profil Sekolah -->
        @php
            $sekolahUser = Auth::user()->sekolah ?? null;
        @endphp
        <div
            class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 text-blue-900">
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                <span>
                    Proktor Tercatat:
                    <strong>{{ $sekolahUser->nama_proktor ?? 'Belum diatur di Profil Sekolah' }}</strong>
                    @if(!empty($sekolahUser->nip_proktor))
                        <span class="font-mono text-blue-700">(NIP. {{ $sekolahUser->nip_proktor }})</span>
                    @endif
                </span>
            </div>
            <a href="{{ route('admin.sekolah.index') }}"
                class="text-blue-700 hover:text-blue-900 font-bold underline inline-flex items-center gap-1 self-start sm:self-auto">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Ubah Data Proktor</span>
            </a>
        </div>

        <!-- Tabel Daftar Sesi Ujian -->
        <div class="flat-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Ujian & Mata Pelajaran</th>
                            <th class="py-3.5 px-4">Kelas / Rombel</th>
                            <th class="py-3.5 px-4">Jadwal Pelaksanaan</th>
                            <th class="py-3.5 px-4 text-center">Peserta Hadir</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Cetak Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ujians as $index => $ujian)
                            <tr class="hover:bg-slate-50/90 transition">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                    {{ $ujians->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ $ujian->nama_ujian }}</p>
                                    <p class="text-[11px] text-blue-700 font-semibold mt-0.5">
                                        <i class="fa-solid fa-book-open mr-1"></i>{{ $ujian->mapel->nama_mapel ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700">
                                    {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-mono text-[11px]">
                                    @if(!empty($ujian->waktu_mulai))
                                        {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d/m/Y H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 font-mono font-bold text-[11px]">
                                        {{ $ujian->nilai_ujians_count ?? 0 }} Siswa
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $badgeClass = match ($ujian->status) {
                                            'aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'selesai' => 'bg-slate-200 text-slate-700 border-slate-300',
                                            default => 'bg-amber-100 text-amber-800 border-amber-200',
                                        };
                                    @endphp
                                    <span
                                        class="px-2.5 py-0.5 rounded-full border text-[10px] font-extrabold uppercase {{ $badgeClass }}">
                                        {{ $ujian->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <!-- Tombol Cetak Berita Acara -->
                                        <a href="{{ route('admin.ujian.berita-acara', $ujian->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-md text-xs font-bold shadow-2xs transition">
                                            <i class="fa-solid fa-file-signature"></i>
                                            <span>Berita Acara</span>
                                        </a>

                                        <!-- Tombol Cetak Rekap Hasil CAT -->
                                        <a href="{{ route('admin.ujian.cetak-hasil', $ujian->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-md text-xs font-bold shadow-2xs transition">
                                            <i class="fa-solid fa-print"></i>
                                            <span>Hasil CAT</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <i class="fa-solid fa-folder-open text-2xl text-slate-300 mb-2 block"></i>
                                    <p class="font-semibold text-slate-600">Belum ada sesi ujian yang tersedia.</p>
                                    <p class="text-[11px] mt-0.5">Silakan buat sesi ujian terlebih dahulu pada menu Atur Sesi
                                        Ujian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ujians->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/60">
                    {{ $ujians->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection