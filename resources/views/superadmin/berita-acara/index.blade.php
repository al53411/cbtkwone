@extends('layouts.admin')

@section('title', 'Arsip Berita Acara Sekolah')
@section('page_title', 'Arsip Berita Acara Sekolah')

@section('content')
    <div class="max-w-7xl mx-auto space-y-5">

        @if(session('success'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Header & Statistik Singkat -->
        <div class="flat-card p-5 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-folder-tree text-base"></i>
                </span>
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">
                        Monitoring Arsip Berita Acara Sekolah
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pantau, verifikasi, dan unduh scan Berita Acara pelaksanaan CAT yang telah diunggah oleh masing-masing satuan pendidikan.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="px-3.5 py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-right">
                    <p class="text-[10px] font-bold uppercase text-emerald-700">Sudah Upload</p>
                    <p class="text-base font-extrabold font-mono text-emerald-900">{{ $totalSudahUpload }} <span class="text-[11px] font-normal">Dokumen</span></p>
                </div>
                <div class="px-3.5 py-2 rounded-lg bg-amber-50 border border-amber-200 text-right">
                    <p class="text-[10px] font-bold uppercase text-amber-700">Belum Upload</p>
                    <p class="text-base font-extrabold font-mono text-amber-900">{{ $totalBelumUpload }} <span class="text-[11px] font-normal">Sesi</span></p>
                </div>
            </div>
        </div>

        <!-- Bar Filter Sekolah & Status -->
        <div class="flat-card p-4 shadow-xs">
            <form method="GET" action="{{ route('superadmin.berita-acara.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                <select name="sekolah_id" class="flat-input px-3 py-2 text-xs font-semibold">
                    <option value="">-- Semua Satuan Pendidikan --</option>
                    @foreach($listSekolah as $skl)
                        <option value="{{ $skl->id }}" {{ request('sekolah_id') == $skl->id ? 'selected' : '' }}>
                            {{ $skl->nama_sekolah }} ({{ $skl->npsn }})
                        </option>
                    @endforeach
                </select>

                <select name="status_upload" class="flat-input px-3 py-2 text-xs font-semibold">
                    <option value="sudah" {{ request('status_upload', 'sudah') === 'sudah' ? 'selected' : '' }}>
                        Sudah Upload Scan BA
                    </option>
                    <option value="belum" {{ request('status_upload') === 'belum' ? 'selected' : '' }}>
                        Belum Upload Scan BA
                    </option>
                    <option value="semua" {{ request('status_upload') === 'semua' ? 'selected' : '' }}>
                        Tampilkan Semua Sesi
                    </option>
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ujian / mapel..."
                    class="flat-input px-3.5 py-2 text-xs">

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-xs font-bold inline-flex items-center justify-center gap-1.5 transition cursor-pointer">
                        <i class="fa-solid fa-filter"></i>
                        <span>Terapkan Filter</span>
                    </button>
                    @if(request()->hasAny(['sekolah_id', 'status_upload', 'search']))
                        <a href="{{ route('superadmin.berita-acara.index') }}"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md text-xs font-semibold">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Berita Acara Sekolah -->
        <div class="flat-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Satuan Pendidikan</th>
                            <th class="py-3.5 px-4">Sesi Ujian &amp; Mapel</th>
                            <th class="py-3.5 px-4">Petugas (Proktor &amp; Pengawas)</th>
                            <th class="py-3.5 px-4 text-center">Peserta</th>
                            <th class="py-3.5 px-4">Waktu Upload</th>
                            <th class="py-3.5 px-4 text-right">Dokumen Arsip</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ujians as $index => $ujian)
                            @php
                                $sekolahItem = $ujian->sekolah ?? ($ujian->sekolah_id ? \App\Models\Sekolah::find($ujian->sekolah_id) : null);
                            @endphp
                            <tr class="hover:bg-slate-50/90 transition">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                    {{ $ujians->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900">
                                        {{ $sekolahItem->nama_sekolah ?? 'Sekolah Tidak Terikat' }}
                                    </p>
                                    <p class="text-[11px] font-mono text-slate-500 mt-0.5">
                                        NPSN: {{ $sekolahItem->npsn ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900">{{ $ujian->nama_ujian }}</p>
                                    <p class="text-[11px] text-blue-700 font-semibold mt-0.5">
                                        {{ $ujian->mapel->nama_mapel ?? '-' }} • {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-700 space-y-0.5">
                                    <div>
                                        <span class="text-slate-400">Proktor:</span>
                                        <span class="font-semibold">{{ $sekolahItem->nama_proktor ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400">Pengawas:</span>
                                        <span class="font-semibold">{{ $sekolahItem->nama_pengawas ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 font-mono font-bold text-[11px]">
                                        {{ $ujian->nilai_ujians_count ?? 0 }} Siswa
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                    @if(!empty($ujian->uploaded_ba_at))
                                        <span class="text-emerald-700 font-semibold block">
                                            {{ \Carbon\Carbon::parse($ujian->uploaded_ba_at)->format('d/m/Y H:i') }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            {{ \Carbon\Carbon::parse($ujian->uploaded_ba_at)->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @if(!empty($ujian->file_berita_acara))
                                            <a href="{{ asset('storage/' . $ujian->file_berita_acara) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-bold shadow-2xs transition">
                                                <i class="fa-solid fa-file-arrow-down"></i>
                                                <span>Lihat Scan BA</span>
                                            </a>
                                        @else
                                            <span class="px-2.5 py-1 rounded bg-amber-50 border border-amber-200 text-amber-700 text-[11px] font-semibold">
                                                Belum Diunggah
                                            </span>
                                        @endif

                                        <a href="{{ route('admin.ujian.berita-acara', $ujian->id) }}" target="_blank"
                                            title="Pratinjau Format Sistem"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-md text-xs font-bold transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <i class="fa-solid fa-file-circle-xmark text-2xl text-slate-300 mb-2 block"></i>
                                    <p class="font-semibold text-slate-600">Tidak ada arsip Berita Acara ditemukan.</p>
                                    <p class="text-[11px] mt-0.5">Ubah filter status upload atau pilih satuan pendidikan lain.</p>
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