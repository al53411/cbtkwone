@extends('layouts.admin')
@section('page_title', 'Tahun Pelajaran')

@section('content')
<div class="w-full bg-white text-slate-800 p-4 sm:p-6">
    <div class="w-full space-y-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Tahun Ajaran</h1>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200">
            + Tambah Tahun Ajaran
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">No</th>
                    <th class="py-3 px-6 text-left">Tahun Ajaran</th>
                    <th class="py-3 px-6 text-center">Semester</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @forelse($tahunAjaran as $index => $item)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left whitespace-nowrap font-medium">{{ $index + 1 }}</td>
                        <td class="py-3 px-6 text-left">{{ $item->tahun }}</td>
                        <td class="py-3 px-6 text-center">{{ $item->semester }}</td>
                        <td class="py-3 px-6 text-center">
                            @if($item->is_aktif)
                                <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs font-semibold">Aktif</span>
                            @else
                                <span class="bg-gray-200 text-gray-600 py-1 px-3 rounded-full text-xs font-semibold">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if(!$item->is_aktif)
                                <form action="{{ route('admin.tahun-ajaran.set-aktif', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="bg-indigo-500 text-white px-3 py-1 rounded text-xs hover:bg-indigo-600">
                                        Aktifkan
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400 italic">Sedang Digunakan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">Belum ada data tahun ajaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Tambah Tahun Ajaran</h2>
        <form action="{{ route('admin.tahun-ajaran.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tahun Ajaran (Contoh: 2024/2025)</label>
                <input type="text" name="tahun" required placeholder="2024/2025" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Semester</label>
                <select name="semester" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection