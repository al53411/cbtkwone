@extends('layouts.superadmin')

@section('title', 'Jadwal & Monitoring Ujian')
@section('header', 'Jadwal & Monitoring Ujian')

@section('content')
    <div class="w-full space-y-6">

        <!-- Filter Pencarian, Kelas & Status -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.ujian.index') }}"
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
                        @foreach ($listKelas as $k)
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
                        class="flex-1 px-4 py-2.5 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-slate-800 transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.ujian.index') }}"
                        class="px-3 py-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Jadwal Ujian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Daftar Sesi Ujian</h2>
                    <p class="text-xs text-slate-500">Pilih sesi ujian untuk memantau peserta live atau melihat rekap nilai.</p>
                </div>
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
                        @forelse ($ujians as $index => $ujian)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    {{ $ujians->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $ujian->nama_ujian }}</span>
                                    <span class="text-[11px] text-emerald-600 font-semibold">
                                        <i class="fa-solid fa-book-open mr-1"></i>{{ $ujian->mapel->nama_mapel ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">
                                        {{ $ujian->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                    <div class="font-semibold text-slate-700">
                                        {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d M Y, H:i') }}
                                    </div>
                                    <div>Durasi: <b class="text-slate-700">{{ $ujian->durasi }} Menit</b></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono font-bold tracking-wider">
                                        {{ $ujian->token }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($ujian->status === 'aktif')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-ping"></span> AKTIF
                                        </span>
                                    @elseif ($ujian->status === 'selesai')
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
                                        <a href="{{ route('admin.ujian.monitoring', $ujian->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 rounded-lg text-xs font-semibold transition gap-1.5"
                                            title="Monitoring Peserta Live">
                                            <i class="fa-solid fa-desktop"></i> Monitoring
                                        </a>

                                        <a href="{{ route('admin.ujian.rekap', ['ujian_id' => $ujian->id]) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 rounded-lg text-xs font-semibold transition gap-1.5"
                                            title="Lihat Rekap Nilai">
                                            <i class="fa-solid fa-chart-column"></i> Rekap
                                        </a>
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
@endsection