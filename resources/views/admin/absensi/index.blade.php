@extends('layouts.admin')

@section('title', 'Absensi Siswa')
@section('page_title', 'Absensi')

@section('content')
<div class="w-full bg-white text-slate-800 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Kehadiran Siswa</h1>
        <p class="text-gray-500">Kelola seluruh data siswa aktif {{ $profilSekolah->nama_sekolah }}.</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.absensi.cetak-mapel') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition border border-slate-300 shadow-sm">
            <i class="fas fa-print text-xs"></i> Cetak Presensi Mapel
        </a>

        <form action="{{ route('admin.absensi.index') }}" method="GET" class="flex items-center">
            <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()"
                class="px-3 py-2 flat-input font-medium bg-white">
        </form>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-green-50 border border-green-300 text-green-800 text-sm rounded">
    <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('success') }}
</div>
@endif

<form action="{{ route('admin.absensi.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <div class="flat-card overflow-hidden bg-white mb-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500 text-center w-16">No</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">NISN</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Nama Lengkap</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500">Kelas</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase text-gray-500 text-center w-80">Status Kehadiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswas as $index => $siswa)
                @php
                    // Ambil status tersimpan, default ke 'Hadir' jika belum absen
                    $statusTerpilih = $absensiHariIni[$siswa->id] ?? 'Hadir';
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $siswa->nisn }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $siswa->nama_siswa }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $siswa->kelas->nama_kelas }}</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center space-x-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="status[{{ $siswa->id }}]" value="Hadir"
                                    {{ $statusTerpilih == 'Hadir' ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-0">
                                <span class="ml-1.5 text-xs font-bold text-gray-700">H</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="status[{{ $siswa->id }}]" value="Izin"
                                    {{ $statusTerpilih == 'Izin' ? 'checked' : '' }}
                                    class="w-4 h-4 text-amber-500 border-gray-300 focus:ring-0">
                                <span class="ml-1.5 text-xs font-bold text-amber-600">I</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="status[{{ $siswa->id }}]" value="Sakit"
                                    {{ $statusTerpilih == 'Sakit' ? 'checked' : '' }}
                                    class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-0">
                                <span class="ml-1.5 text-xs font-bold text-emerald-600">S</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="status[{{ $siswa->id }}]" value="Alpa"
                                    {{ $statusTerpilih == 'Alpa' ? 'checked' : '' }}
                                    class="w-4 h-4 text-red-500 border-gray-300 focus:ring-0">
                                <span class="ml-1.5 text-xs font-bold text-red-600">A</span>
                            </label>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">Belum ada data siswa di database.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswas->count() > 0)
    <div class="flex justify-end">
        <button type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded transition flex items-center gap-2">
            <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan Absensi Hari Ini
        </button>
    </div>
    @endif
</form>
<div
            class="max-w-4xl mx-auto-center text-[10px] flex flex-col sm:flex-row items-center justify-between gap-1 text-slate-400">
            <div>
                &copy; {{ date('Y') }} Copyright: MahesAPP. All rights reserved.
            </div>
        </div>
</div>

@endsection