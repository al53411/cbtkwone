@extends('layouts.admin')

@section('title', 'Data Siswa')
@section('page_title', 'Siswa')

@section('content')
    <div class="w-full space-y-4 sm:space-y-6">

        <!-- Flash Message Notifikasi -->
        @if(session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs sm:text-sm flex items-center justify-between shadow-sm">
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
            <div
                class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs sm:text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Section Header -->
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Manajemen Data Siswa</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola seluruh data siswa aktif
                    {{ Auth::user()?->sekolah?->nama_sekolah ?? $profilSekolah?->nama_sekolah ?? 'Sekolah' }}.
                </p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <!-- Tombol Import Excel -->
                <button type="button" onclick="document.getElementById('modal-import-siswa').classList.remove('hidden')"
                    class="w-1/2 sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm rounded-lg transition space-x-2 active:bg-emerald-800 shadow-sm">
                    <i class="fa-solid fa-file-excel text-xs"></i>
                    <span>Import Excel</span>
                </button>

                <!-- Tombol Tambah Siswa -->
                <a href="{{ route('admin.siswa.create') }}"
                    class="w-1/2 sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm rounded-lg transition space-x-2 active:bg-blue-800 shadow-sm">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah Siswa</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar Section -->
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-sm">
            <form method="GET" action="{{ route('admin.siswa.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">

                <!-- Input Search Nama / NISN -->
                <div class="sm:col-span-6 lg:col-span-7 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama siswa atau NISN..."
                        class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <!-- Dropdown Filter Kelas -->
                <div class="sm:col-span-4 lg:col-span-3">
                    <select name="kelas_id"
                        class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
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

                <!-- Tombol Submit & Reset -->
                <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-1.5">
                    <button type="submit"
                        class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs sm:text-sm rounded-lg transition shadow-sm flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-filter text-xs"></i>
                        <span>Filter</span>
                    </button>
                    @if(request()->has('search') || request()->has('kelas_id'))
                        <a href="{{ route('admin.siswa.index') }}"
                            class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition text-xs"
                            title="Reset Filter">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- MAIN DATA CONTAINER -->
        <div class="flat-card bg-white rounded-xl border border-gray-200 overflow-hidden w-full shadow-sm">

            <!-- 1. TAMPILAN MOBILE (< sm) -->
            <div class="block sm:hidden divide-y divide-gray-100">
                @forelse($siswas as $index => $siswa)
                    <div class="p-4 hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <span
                                    class="shrink-0 w-7 h-7 flex items-center justify-center rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100 mt-0.5">
                                    {{ $siswas->firstItem() + $index }}
                                </span>

                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-900 text-sm leading-snug truncate">
                                        {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? '-' }}
                                    </h2>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                        <span>NISN:</span>
                                        <span class="font-mono font-medium text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded">
                                            {{ $siswa->nisn ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <span
                                class="shrink-0 bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-md border border-blue-100">
                                {{ $siswa->kelas->nama_kelas ?? '-' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-end gap-2 mt-3 pt-2.5 border-t border-gray-100">
                            <a href="{{ route('admin.siswa.edit', $siswa->id) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition"
                                title="Edit Data">
                                <i class="fa-solid fa-pen text-[10px]"></i>
                                <span>Edit</span>
                            </a>
                            <form action="{{ route('admin.siswa.destroy', $siswa->id) }}" method="POST"
                                onsubmit="return confirm('Hapus data siswa ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition"
                                    title="Hapus Data">
                                    <i class="fa-solid fa-trash text-[10px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-xs">
                        <i class="fa-solid fa-folder-open text-2xl mb-2 block text-gray-300"></i>
                        Tidak ditemukan data siswa yang sesuai.
                    </div>
                @endforelse
            </div>

            <!-- 2. TAMPILAN DESKTOP/TABLET (>= sm) -->
            <div class="hidden sm:block overflow-x-auto w-full">
                <table class="w-full text-left border-collapse min-w-[640px]">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200">
                            <th
                                class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center w-12 sm:w-16">
                                No</th>
                            <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500">NISN</th>
                            <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500">Nama Lengkap</th>
                            <th class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500">Kelas</th>
                            <th
                                class="px-4 sm:px-6 py-3.5 text-xs font-semibold uppercase text-gray-500 text-center w-28 sm:w-36">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($siswas as $index => $siswa)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 text-center font-medium">
                                    {{ $siswas->firstItem() + $index }}
                                </td>
                                <td
                                    class="px-4 sm:px-6 py-4 text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap font-mono">
                                    {{ $siswa->nisn }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm font-medium text-gray-900">
                                    {{ $siswa->nama_siswa ?? $siswa->nama_lengkap ?? '-' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-md border border-blue-100">
                                        {{ $siswa->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-center whitespace-nowrap">
                                    <div class="flex justify-center space-x-1.5 sm:space-x-2">
                                        <a href="{{ route('admin.siswa.edit', $siswa->id) }}"
                                            class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-amber-600 hover:bg-amber-50 transition active:bg-amber-100"
                                            title="Edit Data">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('admin.siswa.destroy', $siswa->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus data siswa ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-red-600 hover:bg-red-50 transition active:bg-red-100"
                                                title="Hapus Data">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-xs sm:text-sm">
                                    <i class="fa-solid fa-folder-open text-2xl mb-2 block text-gray-300"></i>
                                    Tidak ditemukan data siswa yang sesuai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- LINK PAGINATION -->
            @if($siswas->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                    {{ $siswas->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL IMPORT EXCEL DATA SISWA -->
    <div id="modal-import-siswa"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-emerald-600 font-bold text-sm sm:text-base">
                    <i class="fa-solid fa-file-excel text-lg"></i>
                    <span>Import Data Siswa Excel</span>
                </div>
                <button type="button" onclick="document.getElementById('modal-import-siswa').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body & Form -->
            <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-4">
                @csrf

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold">Format Kolom Header Excel:</p>
                        <a href="{{ asset('templates/template_import_siswa.xlsx') }}" download="template_import_siswa.xlsx"
                            class="inline-flex items-center gap-1 text-[11px] bg-blue-600 hover:bg-blue-700 text-white font-medium px-2 py-1 rounded transition">
                            <i class="fa-solid fa-download text-[10px]"></i>
                            <span>Download Template</span>
                        </a>
                    </div>
                    <p><code>nisn</code>, <code>nama_siswa</code>, <code>jenis_kelamin</code> (L/P), <code>kelas_id</code>,
                        <code>alamat</code>.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pilih File (.xlsx / .xls / .csv)</label>
                    <input type="file" name="file" required accept=".xlsx, .xls, .csv"
                        class="block w-full text-xs text-gray-500 border border-gray-200 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                </div>

                <!-- Modal Footer Buttons -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-import-siswa').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs sm:text-sm rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-upload text-xs"></i>
                        <span>Upload & Import</span>
                    </button>
                </div>
            </form>
        </div>

        <div
            class="max-w-4xl mx-auto-center text-[10px] flex flex-col sm:flex-row items-center justify-between gap-1 text-slate-400">
            <div>
                &copy; {{ date('Y') }} Copyright: MahesAPP. All rights reserved.
            </div>
        </div>
    </div>
@endsection