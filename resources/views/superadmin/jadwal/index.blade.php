@extends('layouts.superadmin') {{-- Sesuaikan dengan nama layout superadmin Anda --}}

@section('title', 'Jadwal Ujian Otomatis')

@section('content')
<div class="w-full space-y-6">

    <!-- Header & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 rounded-full border border-indigo-200">
                    Sinkron Otomatis
                </span>
                <span class="text-xs text-slate-400">• Update real-time dari Sesi Ujian</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 mt-1">
                Jadwal Pelaksanaan Ujian (CBT)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Seluruh jadwal di bawah ini otomatis terisi saat Superadmin membuat atau mengubah sesi ujian.
            </p>
        </div>

        <div class="flex items-center gap-2.5 print:hidden">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Jadwal</span>
            </button>
            {{-- Sesuaikan nama route tambah/kelola sesi ujian Anda --}}
            <a href="{{ route('superadmin.ujian.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-plus"></i>
                <span>Atur Sesi Ujian</span>
            </a>
        </div>
    </div>

    <!-- Kartu Ringkasan Status -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 print:hidden">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Total Sesi Jadwal</p>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ $ringkasan['total'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <i class="fa-solid fa-calendar-days text-lg"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Ujian Hari Ini</p>
                <p class="text-2xl font-black text-indigo-600 mt-1">{{ $ringkasan['hari_ini'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="fa-solid fa-clock text-lg"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-emerald-700">Sedang Berlangsung</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $ringkasan['berlangsung'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="fa-solid fa-circle-play text-lg animate-pulse"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-amber-700">Akan Datang</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $ringkasan['akan_datang'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="fa-solid fa-hourglass-half text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Filter Pencarian Jadwal -->
    <form method="GET" action="{{ route('superadmin.jadwal.index') }}"
        class="bg-white p-4 rounded-2xl border border-slate-200 grid grid-cols-1 sm:grid-cols-4 gap-3 print:hidden">
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Ujian</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                class="w-full rounded-xl border-slate-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Kelas</label>
            <select name="kelas_id" class="w-full rounded-xl border-slate-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach($daftarKelas as $kelas)
                    <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Pelaksanaan</label>
            <select name="status" class="w-full rounded-xl border-slate-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Status</option>
                <option value="berlangsung" {{ request('status') === 'berlangsung' ? 'selected' : '' }}>Sedang Berlangsung</option>
                <option value="akan_datang" {{ request('status') === 'akan_datang' ? 'selected' : '' }}>Akan Datang</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['tanggal', 'kelas_id', 'status']))
                <a href="{{ route('superadmin.jadwal.index') }}"
                    class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Daftar Jadwal Dikelompokkan per Hari & Tanggal -->
    @forelse($jadwalPerTanggal as $hariTanggal => $daftarSesi)
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <!-- Header Hari & Tanggal -->
            <div class="bg-slate-800 text-white px-5 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="fa-regular fa-calendar-check text-indigo-400"></i>
                    <h2 class="text-sm font-bold tracking-wide">{{ $hariTanggal }}</h2>
                </div>
                <span class="text-[11px] font-semibold bg-slate-700 px-2.5 py-0.5 rounded-full text-slate-200">
                    {{ $daftarSesi->count() }} Sesi Ujian
                </span>
            </div>

            <!-- Tabel Sesi Ujian -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                            <th class="py-3 px-4">Waktu & Durasi</th>
                            <th class="py-3 px-4">Mata Pelajaran / Judul Ujian</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Token</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right print:hidden">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($daftarSesi as $ujian)
                            @php
                                $mulai = \Carbon\Carbon::parse($ujian->waktu_mulai);
                                $selesai = \Carbon\Carbon::parse($ujian->waktu_selesai);
                                $sedangAktif = $sekarang->between($mulai, $selesai);
                                $belumMulai = $sekarang->lt($mulai);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">
                                        {{ $mulai->format('H:i') }} - {{ $selesai->format('H:i') }} WIB
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        Durasi: {{ $ujian->durasi ?? $mulai->diffInMinutes($selesai) }} Menit
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">
                                        {{ $ujian->mapel->nama_mapel ?? $ujian->judul ?? 'Ujian CBT' }}
                                    </div>
                                    @if(!empty($ujian->judul) && isset($ujian->mapel))
                                        <div class="text-[11px] text-slate-500">{{ $ujian->judul }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-[11px]">
                                        {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-black tracking-widest px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-300 text-slate-800">
                                        {{ $ujian->token ?? '-' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($sedangAktif)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                            Berlangsung
                                        </span>
                                    @elseif($belumMulai)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">
                                            <i class="fa-regular fa-clock"></i>
                                            Akan Datang
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                            <i class="fa-solid fa-check"></i>
                                            Selesai
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap print:hidden">
                                    <a href="{{ route('superadmin.ujian.edit', $ujian->id) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-semibold transition"
                                        title="Ubah Sesi Ujian">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Edit Sesi</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center space-y-3">
            <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                <i class="fa-regular fa-calendar-xmark text-2xl"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-700">Belum Ada Jadwal Sesi Ujian</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Jadwal ujian akan otomatis muncul di halaman ini segera setelah Anda menambahkan sesi ujian baru.
            </p>
            <div class="pt-2">
                <a href="{{ route('superadmin.ujian.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl">
                    <i class="fa-solid fa-plus"></i> Buat Sesi Ujian Sekarang
                </a>
            </div>
        </div>
    @endforelse

</div>
@endsection