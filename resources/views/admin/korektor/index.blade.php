@extends('layouts.admin')

@section('title', 'Data Korektor')
@section('page_title', 'Korektor')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Data Korektor</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola guru yang berperan sebagai korektor ujian esai.</p>
    </div>
    <a href="{{ route('admin.korektor.create') }}"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Tambah Korektor</span>
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center w-10">No</th>
                    <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Lengkap</th>
                    <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">NIP / NUPTK</th>
                    <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Mata Pelajaran</th>
                    <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Kelas Diampu</th>
                    <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($korektors as $guru)
                <tr class="hover:bg-gray-50/60 transition">
                    <td class="px-5 py-4 text-sm text-gray-500 text-center">{{ $loop->iteration }}</td>
                    <td class="px-5 py-4 text-sm font-semibold text-gray-900">{{ $guru->nama_lengkap }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">
                        <div>NIP: {{ $guru->nip ?? '-' }}</div>
                        <div class="text-xs text-gray-400">NUPTK: {{ $guru->nuptk ?? '-' }}</div>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-700">{{ $guru->mata_pelajaran ?? '-' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-700">
                        {{ $guru->kelas->pluck('nama_kelas')->join(', ') ?: '-' }}
                    </td>
                    <td class="px-5 py-4 text-sm text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.korektor.edit', $guru->id) }}"
                                class="px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-100 transition">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <form action="{{ route('admin.korektor.destroy', $guru->id) }}" method="POST"
                                onsubmit="return confirm('Hapus korektor {{ $guru->nama_lengkap }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 bg-red-50 border border-red-300 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100 transition">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-10 text-center text-gray-400">
                        <i class="fa-solid fa-user-slash text-3xl mb-2 block"></i>
                        Belum ada data korektor.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
