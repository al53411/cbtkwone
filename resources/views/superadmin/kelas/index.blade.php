@extends('layouts.superadmin')

@section('title', 'Master Bank Soal')
@section('header', 'Master Bank Soal')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Manajemen Data Kelas</h1>
            <p class="text-xs text-slate-500">Kelola seluruh daftar kelas yang terdaftar di sistem.</p>
        </div>
        <a href="{{ route('superadmin.kelas.create') }}" 
           class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-200 transition">
            + Tambah Kelas
        </a>
    </div>

    {{-- Alert Success --}}
    @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter & Search --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <form action="{{ route('superadmin.kelas.index') }}" method="GET" class="w-full sm:w-72">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama kelas / tingkat..."
                   class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </form>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="p-4 w-16 text-center">No</th>
                        <th class="p-4">Nama Kelas</th>
                        <th class="p-4">Tingkat</th>
                        <th class="p-4 w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kelass as $index => $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 text-center font-medium">{{ $kelass->firstItem() + $index }}</td>
                            <td class="p-4 font-semibold text-slate-800">{{ $item->nama_kelas }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 text-slate-600 rounded-lg">
                                    {{ $item->tingkat ?? '-' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('superadmin.kelas.edit', $item->id) }}" 
                                       class="px-2.5 py-1.5 text-[11px] font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('superadmin.kelas.destroy', $item->id) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-2.5 py-1.5 text-[11px] font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($kelass->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $kelass->links() }}
            </div>
        @endif
    </div>
</div>
@endsection