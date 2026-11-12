@extends('layouts.admin')

@section('title', 'Hasil & Rekap Nilai Ujian')
@section('header', 'Hasil & Rekap Nilai Ujian')

@section('content')
    <div class="w-full space-y-6">

        <!-- Filter Sesi Ujian & Kelas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.ujian.rekap') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sesi Ujian</label>
                    <select name="ujian_id"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Semua Sesi Ujian --</option>
                        @foreach ($listUjian as $u)
                            <option value="{{ $u->id }}" @selected(request('ujian_id') == $u->id)>
                                {{ $u->nama_ujian }} ({{ $u->mapel->nama_mapel ?? '-' }})
                            </option>
                        @endforeach
                    </select>
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cari Siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NISN..."
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-slate-800 transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.ujian.rekap') }}"
                        class="px-3 py-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Hasil Ujian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-slate-800">Daftar Nilai Peserta Ujian</h2>

                @if(request('ujian_id'))
                    @php
                        $ujianTerpilih = $listUjian->firstWhere('id', request('ujian_id'));
                    @endphp

                    @if($ujianTerpilih && $ujianTerpilih->isNilaiDirilis())
                        <a href="{{ route('admin.ujian.cetak-hasil', ['id' => $ujianTerpilih->id, 'kelas_id' => request('kelas_id')]) }}"
                            target="_blank"
                            class="inline-flex items-center justify-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl transition gap-1.5 shadow-xs">
                            <i class="fa-solid fa-print"></i> Cetak Laporan
                        </a>
                    @else
                        <button type="button" disabled
                            class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 text-slate-400 border border-slate-200 font-semibold text-xs rounded-xl cursor-not-allowed gap-1.5">
                            <i class="fa-solid fa-lock"></i> Cetak Terkunci (Menunggu Rilis)
                        </button>
                    @endif
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">NISN & Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Nama Ujian & Mapel</th>
                            <th class="py-3 px-4 text-center">Benar / Salah</th>
                            <th class="py-3 px-4 text-center">Nilai</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($hasilUjians as $index => $hasil)
                            @php
                                $sudahDirilis = $hasil->ujian?->isNilaiDirilis() ?? false;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    {{ $hasilUjians->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block">
                                        {{ $hasil->siswa->nama_lengkap ?? $hasil->siswa->nama_siswa ?? $hasil->siswa->nama ?? '-' }}
                                    </span>
                                    <span class="text-[11px] font-mono text-slate-400">
                                        {{ $hasil->siswa->nisn ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">
                                        {{ $hasil->siswa->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-700 block">
                                        {{ $hasil->ujian->nama_ujian ?? '-' }}
                                    </span>
                                    <span class="text-[11px] text-emerald-600 font-semibold">
                                        {{ $hasil->ujian->mapel->nama_mapel ?? '-' }}
                                    </span>
                                </td>

                                @if($sudahDirilis)
                                    <!-- JIKA SUPERADMIN SUDAH MERILIS NILAI -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-emerald-600 font-bold">{{ $hasil->jumlah_benar ?? $hasil->benar ?? 0 }}</span>
                                        /
                                        <span class="text-rose-600 font-bold">{{ $hasil->jumlah_salah ?? $hasil->salah ?? 0 }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-3 py-1 rounded-lg font-extrabold text-xs {{ ($hasil->nilai ?? 0) >= 75 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                                            {{ number_format($hasil->nilai ?? 0, 1) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if(($hasil->nilai ?? 0) >= 75.00)
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-bold">
                                                LULUS
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-bold">
                                                TIDAK LULUS
                                            </span>
                                        @endif
                                    </td>
                                @else
                                    <!-- JIKA NILAI MASIH DIKUNCI SUPERADMIN -->
                                    <td class="py-3.5 px-4 text-center text-slate-400">
                                        <span class="text-[11px] italic"><i class="fa-solid fa-lock mr-1"></i>Terkunci</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-semibold">
                                            <i class="fa-solid fa-clock text-slate-400 mr-1.5"></i>Dalam Penilaian
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-[10px] font-bold">
                                            <i class="fa-solid fa-hourglass-half mr-1"></i>MENUNGGU RILIS
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-clipboard-list text-2xl mb-2 block text-slate-300"></i>
                                    Belum ada data hasil ujian yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $hasilUjians->links() }}
            </div>
        </div>
    </div>
@endsection