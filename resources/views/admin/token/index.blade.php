@extends('layouts.admin')

@section('title', 'Token & Ujian Aktif')
@section('page_title', 'Token Ujian')

@section('content')
<div class="w-full space-y-4 sm:space-y-6">

    <!-- Flash Message Notifikasi -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs sm:text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs sm:text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Monitoring Token & Ujian Aktif</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Pantau token ujian dan pelaksanaan ujian berdasarkan kelas serta mata pelajaran.
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Form Rilis Token Baru -->
            <form action="{{ route('admin.token.generate') }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" 
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm rounded-lg transition space-x-2 active:bg-blue-800 shadow-sm">
                    <i class="fa-solid fa-rotate text-xs"></i>
                    <span>Rilis Token Baru</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Highlight Display Token Aktif -->
    <div class="bg-gradient-to-r from-slate-900 to-blue-950 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 opacity-10 text-white pointer-events-none">
            <i class="fa-solid fa-key text-9xl"></i>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10">
            <div class="text-center sm:text-left space-y-1">
                <div class="inline-flex items-center gap-2 bg-blue-500/20 border border-blue-400/30 text-blue-200 px-3 py-1 rounded-full text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>TOKEN UTAMA AKTIF</span>
                </div>
                <h2 class="text-xs text-slate-400 font-medium pt-2">Berlaku untuk seluruh ujian berlangsung saat ini</h2>
            </div>

            <!-- Kode Token & Action Copy -->
            <div class="flex items-center gap-3">
                <span id="token-code" class="font-mono text-4xl sm:text-5xl font-black tracking-widest bg-white/10 backdrop-blur-md border border-white/20 px-6 py-2.5 rounded-2xl shadow-inner text-amber-300">
                    {{ $tokenAktif->token ?? '------' }}
                </span>
                <button onclick="copyToken()" title="Salin Token" class="p-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition text-white hover:text-amber-300 active:scale-95">
                    <i class="fa-solid fa-copy text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Bar Section (Filter Kelas & Mapel) -->
    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.token.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            
            <!-- Dropdown Filter Kelas -->
            <div class="sm:col-span-5 lg:col-span-5">
                <label class="block text-[11px] font-medium text-gray-500 mb-1">Pilih Kelas</label>
                <select name="kelas_id" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <option value="">-- Semua Kelas --</option>
                    @if(isset($listKelas))
                        @foreach($listKelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Dropdown Filter Mata Pelajaran -->
            <div class="sm:col-span-5 lg:col-span-5">
                <label class="block text-[11px] font-medium text-gray-500 mb-1">Pilih Mata Pelajaran</label>
                <select name="mapel_id" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @if(isset($listMapel))
                        @foreach($listMapel as $m)
                            <option value="{{ $m->id }}" {{ request('mapel_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nama_mapel }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="sm:col-span-2 lg:col-span-2 flex items-end gap-1.5">
                <button type="submit" class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs sm:text-sm rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 h-[38px]">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filter</span>
                </button>
                @if(request()->has('kelas_id') || request()->has('mapel_id'))
                    <a href="{{ route('admin.token.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition text-xs h-[38px] flex items-center justify-center" title="Reset Filter">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- MAIN DATA CONTAINER (DAFTAR UJIAN & MAPEL AKTIF) -->
    <div class="flat-card bg-white rounded-xl border border-gray-200 overflow-hidden w-full shadow-sm">
        
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-file-pen text-blue-600"></i>
                <span>Jadwal & Status Pelaksanaan Ujian</span>
            </h3>
            <span class="text-xs text-gray-500 font-medium">Total: {{ $ujians->count() ?? 0 }} Sesi Ujian</span>
        </div>

        <!-- 1. TAMPILAN MOBILE (< sm) -->
        <div class="block sm:hidden divide-y divide-gray-100">
            @forelse($ujians as $index => $ujian)
            <div class="p-4 hover:bg-gray-50/80 transition-colors">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                        </span>
                        <h2 class="font-semibold text-gray-900 text-sm leading-snug">
                            {{ $ujian->mapel->nama_mapel ?? $ujian->nama_ujian }}
                        </h2>
                        <p class="text-xs text-gray-500">
                            <i class="fa-regular fa-clock mr-1"></i>
                            {{ $ujian->durasi ?? 60 }} Menit
                        </p>
                    </div>

                    <div>
                        @if($ujian->status == 'aktif')
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-[10px] font-medium px-2.5 py-1 rounded-full">
                                {{ ucfirst($ujian->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-gray-100 text-xs text-gray-600">
                    <div>
                        Token: <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ $ujian->token ?? '-' }}</span>
                    </div>
                    <div class="font-medium text-gray-500">
                        Siswa: <span class="text-blue-600 font-bold">{{ $ujian->siswa_selesai_count ?? 0 }}</span>/{{ $ujian->total_siswa_count ?? 0 }}
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">
                <i class="fa-solid fa-folder-open text-2xl mb-2 block text-gray-300"></i>
                Tidak ada data ujian atau mata pelajaran yang aktif.
            </div>
            @endforelse
        </div>

        <!-- 2. TAMPILAN DESKTOP/TABLET (>= sm) -->
        <div class="hidden sm:block overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200">
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center w-12 sm:w-16">No</th>
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500">Mata Pelajaran / Ujian</th>
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500">Kelas Target</th>
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center">Token Ujian</th>
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center">Durasi</th>
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center">Status Ujian</th>
                        <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center">Progres Siswa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($ujians as $index => $ujian)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 text-center font-medium">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm font-semibold text-gray-900">
                            {{ $ujian->mapel->nama_mapel ?? $ujian->nama_ujian }}
                            <div class="text-[11px] font-normal text-gray-400">{{ $ujian->nama_ujian ?? 'CBT-Exam' }}</div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                            <span class="inline-flex items-center bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-md border border-blue-100">
                                {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center whitespace-nowrap">
                            <span class="font-mono font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-lg tracking-wider">
                                {{ $ujian->token }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 text-center whitespace-nowrap">
                            <span class="font-medium text-gray-800">{{ $ujian->durasi ?? 60 }}</span> Menit
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-center whitespace-nowrap">
                            @if($ujian->status == 'aktif')
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold px-2.5 py-1 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Aktif</span>
                                </span>
                            @else
                                <span class="inline-flex items-center bg-gray-100 text-gray-500 text-xs font-medium px-2.5 py-1 rounded-full">
                                    {{ ucfirst($ujian->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-center whitespace-nowrap">
                            <span class="font-bold text-blue-600">{{ $ujian->siswa_selesai_count ?? 0 }}</span>
                            <span class="text-gray-400">/ {{ $ujian->total_siswa_count ?? 0 }} Siswa</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 text-xs sm:text-sm">
                            <i class="fa-solid fa-folder-open text-2xl mb-2 block text-gray-300"></i>
                            Tidak ada jadwal ujian yang sesuai dengan filter kelas & mapel.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- LINK PAGINATION -->
        @if(method_exists($ujians, 'hasPages') && $ujians->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
            {{ $ujians->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- SweetAlert2 Notification Script for Copying Token -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function copyToken() {
        const tokenText = document.getElementById('token-code').innerText.trim();
        if (tokenText === '------') return;
        
        navigator.clipboard.writeText(tokenText);
        Swal.fire({
            icon: 'success',
            title: 'Token Tersalin!',
            text: 'Kode token ' + tokenText + ' berhasil disalin ke clipboard.',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
</script>
@endsection