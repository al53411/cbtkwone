@extends('layouts.superadmin')

@section('title', 'Rekap Nilai Ujian')
@section('header', 'Rekap Nilai Ujian')

@section('content')
    <div class="w-full space-y-6">

        <!-- Header & Kontrol Rilis Nilai -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            @if($ujian)
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-book text-[10px] mr-1.5"></i> {{ $ujian->mapel->nama_mapel ?? '-' }} &bull; Kelas {{ $ujian->kelas->nama_kelas ?? '-' }}
                        </span>

                        @if($ujian->isNilaiDirilis())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-solid fa-lock-open text-[10px] mr-1.5"></i> Status Admin: Nilai Dirilis
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fa-solid fa-lock text-[10px] mr-1.5"></i> Status Admin: Terkunci (Dalam Penilaian)
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl font-bold text-slate-800">{{ $ujian->nama_ujian }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Token Ujian: <b class="text-indigo-600 font-mono">{{ $ujian->token }}</b>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Tombol Toggle Rilis / Kunci Nilai ke Menu Admin -->
                    <form action="{{ route('superadmin.ujian.toggle-rilis', $ujian->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        @if($ujian->isNilaiDirilis())
                            <button type="submit"
                                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition">
                                <i class="fa-solid fa-lock"></i>
                                <span>Kunci Nilai dari Admin</span>
                            </button>
                        @else
                            <button type="submit"
                                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                                <i class="fa-solid fa-lock-open"></i>
                                <span>Rilis Nilai ke Admin</span>
                            </button>
                        @endif
                    </form>

                    <a href="{{ route('superadmin.ujian.rekap') }}"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        <i class="fa-solid fa-list"></i>
                        <span>Semua Ujian</span>
                    </a>
                </div>
            @else
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Rekap Seluruh Nilai Ujian</h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pilih <b>Sesi Ujian</b> pada filter di bawah untuk merilis atau mengunci tampilan nilai di menu Admin.
                    </p>
                </div>
            @endif
        </div>

        <!-- Form Filter Sesi Ujian, Sekolah & Kelas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('superadmin.ujian.rekap') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                <!-- Filter Sesi Ujian -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sesi Ujian</label>
                    <select name="ujian_id"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition">
                        <option value="">-- Semua Sesi Ujian --</option>
                        @foreach ($listUjian as $u)
                            <option value="{{ $u->id }}" @selected(($ujian->id ?? request('ujian_id')) == $u->id)>
                                {{ $u->nama_ujian }} ({{ $u->mapel->nama_mapel ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Sekolah -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sekolah</label>
                    <select name="sekolah_id"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition">
                        <option value="">-- Semua Sekolah --</option>
                        @foreach ($sekolahs as $sekolah)
                            <option value="{{ $sekolah->id }}" @selected(request('sekolah_id') == $sekolah->id)>
                                {{ $sekolah->nama_sekolah ?? $sekolah->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kelas -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tingkat Kelas</label>
                    <select name="kelas_id"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($kelases as $kelas)
                            <option value="{{ $kelas->id }}" @selected(request('kelas_id') == $kelas->id)>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Aksi Filter -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-slate-800 transition">
                        <i class="fa-solid fa-filter mr-1.5"></i> Filter
                    </button>
                    <a href="{{ route('superadmin.ujian.rekap') }}"
                        class="px-3 py-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>

            </form>
        </div>

        <!-- Tabel Rekap Nilai Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Daftar Nilai Peserta Ujian</h2>
                    <span class="text-xs text-slate-500">Total Ditampilkan: <b>{{ $rekapNilais->count() }}</b> peserta</span>
                </div>

                <a href="{{ route('superadmin.ujian.cetak-rekap', ['id' => $ujian?->id, 'sekolah_id' => request('sekolah_id'), 'kelas_id' => request('kelas_id')]) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl transition gap-1.5 shadow-xs">
                    <i class="fa-solid fa-print"></i> Cetak Laporan
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">Nama Siswa</th>
                            <th class="py-3.5 px-4">NISN</th>
                            @if(!$ujian)
                                <th class="py-3.5 px-4">Sesi Ujian</th>
                            @endif
                            <th class="py-3.5 px-4 text-center">Benar</th>
                            <th class="py-3.5 px-4 text-center">Salah</th>
                            <th class="py-3.5 px-4 text-center">Kosong</th>
                            <th class="py-3.5 px-4 text-center">Nilai Akhir</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($rekapNilais as $index => $item)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-4 text-center font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800">
                                    {{ $item->siswa->nama_siswa ?? ($item->siswa->nama ?? ($item->siswa->nama_lengkap ?? ($item->siswa->name ?? '-'))) }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-600">
                                    {{ $item->siswa->nisn ?? '-' }}
                                </td>
                                @if(!$ujian)
                                    <td class="py-4 px-4">
                                        <span class="font-semibold text-slate-700 block">{{ $item->ujian->nama_ujian ?? '-' }}</span>
                                        <span class="text-[11px] text-emerald-600">{{ $item->ujian->mapel->nama_mapel ?? '-' }}</span>
                                    </td>
                                @endif
                                <td class="py-4 px-4 text-center font-bold text-emerald-600">
                                    {{ $item->jumlah_benar }}
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-rose-600">
                                    {{ $item->jumlah_salah }}
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-amber-600">
                                    {{ $item->tidak_dijawab }}
                                </td>
                                <td class="py-4 px-4 text-center font-black text-blue-700 text-sm">
                                    {{ number_format($item->nilai, 2) }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @php
                                        $ambangBatas = 75.00;
                                        $isLulus = $item->nilai >= $ambangBatas;
                                    @endphp
                                    @if($isLulus)
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-bold">
                                            LULUS
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-bold">
                                            TIDAK LULUS
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center text-slate-500">
                                    {{ $item->waktu_selesai ? $item->waktu_selesai->format('d/m/Y H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $ujian ? 9 : 10 }}" class="py-8 text-center text-slate-400 font-medium">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                                    Tidak ada data nilai yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection