@extends('layouts.admin')

@section('title', 'Daftar Paket Soal')
@section('header', 'Daftar Paket Soal')

@section('content')
<div class="w-full space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Daftar Paket Soal</h1>
            <p class="text-xs text-slate-500 mt-1">Ringkasan paket ujian berdasarkan kode, mata pelajaran, kelas, dan tipe soal dari pusat.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" 
           class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition inline-flex items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <form method="GET" action="{{ route('admin.soal.paket') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Mata Pelajaran</label>
                <select name="mapel_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach ($mapels as $mapel)
                        <option value="{{ $mapel->id }}" @selected(request('mapel_id') == $mapel->id)>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tingkat Kelas</label>
                <select name="kelas_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition">
                    <option value="">-- Semua Kelas --</option>
                    @foreach ($kelass as $kelas)
                        <option value="{{ $kelas->id }}" @selected(request('kelas_id') == $kelas->id)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cari Kode / Soal</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau isi soal..."
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-semibold text-xs rounded-xl hover:bg-blue-700 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.soal.paket') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Ringkasan Paket -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Rekapitulasi Paket Soal</h2>
            <span class="text-xs text-slate-500">
                Menampilkan <b>{{ $pakets->firstItem() ?? 0 }}</b> - <b>{{ $pakets->lastItem() ?? 0 }}</b> dari <b>{{ $pakets->total() }}</b> paket
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Kode Bank Soal</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4 text-center">Tipe Soal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($pakets as $index => $paket)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-4 text-center font-bold text-slate-400">
                                {{ $pakets->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-blue-600 text-xs block">
                                    {{ $paket->kode_soal ?? 'PAKET-' . $paket->id }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $paket->mapel->nama_mapel ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $paket->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="uppercase font-bold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs">
                                    {{ $paket->tipe ?? 'PG' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 font-medium">
                                Belum ada data paket soal yang tersedia dari pusat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $pakets->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection