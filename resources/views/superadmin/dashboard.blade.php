@extends('layouts.superadmin')
@section('title', 'Dashboard CAT Superadmin')
@section('header', 'Dashboard Monitoring CATKwOne')

@section('content')

<!-- FLASH MESSAGES -->
@if(session('success'))
<div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-between backdrop-blur-sm transition-all animate-fade-in">
    <div class="flex items-center space-x-3">
        <div class="p-2 bg-emerald-500/20 text-emerald-600 rounded-lg">
            <i class="fa-solid fa-circle-check text-lg"></i>
        </div>
        <span class="text-sm font-medium text-emerald-900">{{ session('success') }}</span>
    </div>
    <button onclick="this.parentElement.remove()" class="p-1 text-emerald-600 hover:text-emerald-800 rounded-lg transition">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-center justify-between backdrop-blur-sm transition-all animate-fade-in">
    <div class="flex items-center space-x-3">
        <div class="p-2 bg-rose-500/20 text-rose-600 rounded-lg">
            <i class="fa-solid fa-circle-exclamation text-lg"></i>
        </div>
        <span class="text-sm font-medium text-rose-900">{{ session('error') }}</span>
    </div>
    <button onclick="this.parentElement.remove()" class="p-1 text-rose-600 hover:text-rose-800 rounded-lg transition">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>
</div>
@endif

<!-- STATS CARDS GRID -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">

    <!-- Card 1: Total Sekolah -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 group flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Sekolah</p>
            <h3 class="text-3xl font-black text-gray-900 mt-2 tracking-tight">{{ $totalSekolah ?? 0 }}</h3>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
            <i class="fa-solid fa-school text-xl"></i>
        </div>
    </div>

    <!-- Card 2: Ujian Aktif (CAT) -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 group flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ujian Aktif</p>
            <div class="flex items-baseline gap-1 mt-2">
                <h3 class="text-3xl font-black text-emerald-600 tracking-tight">{{ $totalUjianAktif ?? $sekolahValidasi ?? 0 }}</h3>
            </div>
            <p class="text-[11px] font-medium text-emerald-600 mt-1 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sedang Berlangsung
            </p>
        </div>
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm">
            <i class="fa-solid fa-laptop-code text-xl"></i>
        </div>
    </div>

    <!-- Card 3: Total Bank Soal / Ujian -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 group flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Ujian CAT</p>
            <h3 class="text-3xl font-black text-indigo-600 mt-2 tracking-tight">{{ $totalUjian ?? 0 }}</h3>
        </div>
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
            <i class="fa-solid fa-file-signature text-xl"></i>
        </div>
    </div>

    <!-- Card 4: Guru / Proktor -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 group flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Guru & Proktor</p>
            <h3 class="text-3xl font-black text-gray-900 mt-2 tracking-tight">{{ $totalGuru ?? 0 }}</h3>
        </div>
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300 shadow-sm">
            <i class="fa-solid fa-chalkboard-user text-xl"></i>
        </div>
    </div>

    <!-- Card 5: Peserta Ujian / Siswa -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 group flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Peserta</p>
            <h3 class="text-3xl font-black text-gray-900 mt-2 tracking-tight">{{ $totalSiswa ?? $totalTendik ?? 0 }}</h3>
        </div>
        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300 shadow-sm">
            <i class="fa-solid fa-users text-xl"></i>
        </div>
    </div>

</div>

<!-- MAIN DATA TABLE: MONITORING CAT PER SEKOLAH -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header Tabel -->
    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/30">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Monitoring Sesi Ujian CAT Sekolah</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pantau pelaksanaan ujian online dan penanggung jawab di setiap unit sekolah</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.kepsek.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs rounded-xl shadow-sm hover:shadow transition-all duration-200">
                <i class="fa-solid fa-plus text-sm"></i> Tambah Sekolah / Akun
            </a>
        </div>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/70 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 w-16">No</th>
                    <th class="px-6 py-4">Informasi Sekolah</th>
                    <th class="px-6 py-4">NPSN & Alamat</th>
                    <th class="px-6 py-4">Proktor / Kepala Sekolah</th>
                    <th class="px-6 py-4 text-center">Status CAT</th>
                    <th class="px-6 py-4 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($sekolahs as $index => $sekolah)
                <tr class="hover:bg-blue-50/30 transition-colors duration-150">
                    <td class="px-6 py-4 text-xs font-bold text-gray-400">
                        {{ \Illuminate\Support\Str::padLeft(($sekolahs->firstItem() ?? 1) + $index, 2, '0') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">{{ $sekolah->nama_sekolah }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 font-mono text-xs font-semibold text-gray-700 mb-1">
                            NPSN: {{ $sekolah->npsn ?? '-' }}
                        </div>
                        <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $sekolah->alamat_sekolah ?? $sekolah->alamat }}">
                            {{ $sekolah->alamat_sekolah ?? $sekolah->alamat ?? 'Alamat Belum Diatur' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $kepsek = optional($sekolah->users)->firstWhere('role', 'kepsek');
                        @endphp

                        @if($kepsek)
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($kepsek->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-xs">{{ $kepsek->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $kepsek->email }}</div>
                            </div>
                        </div>
                        @elseif($sekolah->nama_kepsek)
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($sekolah->nama_kepsek, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-xs">{{ $sekolah->nama_kepsek }}</div>
                                <div class="text-[11px] text-gray-400">NIP: {{ $sekolah->nip_kepsek ?? '-' }}</div>
                            </div>
                        </div>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-600 border border-rose-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Belum Ada Penanggung Jawab
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                            // Mengecek apakah sekolah memiliki ujian aktif
                            $hasActiveExam = $sekolah->users && $sekolah->users->pluck('ujians')->flatten()->where('status', 'aktif')->count() > 0;
                        @endphp

                        @if($hasActiveExam)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            Ujian Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                            Tidak Ada Ujian
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-1">
                            <a href="{{ route('superadmin.sekolah.edit', $sekolah->id) }}"
                                class="w-8 h-8 flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                title="Edit Data Sekolah">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </a>

                            <form action="{{ route('superadmin.sekolah.destroy', $sekolah->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sekolah ini? Semua data ujian terkait akan terhapus.');"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="w-8 h-8 flex items-center justify-center text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                    title="Hapus Sekolah">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center bg-gray-50/20">
                        <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-laptop-code text-2xl"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-700">Belum Ada Data Sekolah CAT</h4>
                        <p class="text-xs text-gray-400 mt-0.5">Silakan tambahkan unit sekolah baru terlebih dahulu.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sekolahs->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
        {{ $sekolahs->links() }}
    </div>
    @endif
</div>

@endsection