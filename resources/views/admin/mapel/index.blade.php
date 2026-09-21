@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page_title', 'Mata Pelajaran')

@section('content')
<!-- PENYESUAIAN 1: Menggunakan w-full, p-4/p-6 tanpa membatasi lebar container -->
<div class="w-full bg-white text-slate-800 p-4 sm:p-6">
    <!-- PENYESUAIAN 2: Mengubah max-w-7xl menjadi w-full -->
    <div class="w-full space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Mata Pelajaran</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola data mata pelajaran dan pemetaan kelas pengampu</p>
            </div>
            <button type="button" 
                    onclick="openModal('modalTambahMapel')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl transition-all duration-200 shadow-md shadow-blue-500/20 active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Mapel</span>
            </button>
        </div>

        <!-- Alert Notification -->
        @if(session('success'))
            <div id="alertSuccess" class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-lg text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="document.getElementById('alertSuccess').remove()" class="text-emerald-600 hover:text-emerald-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Data Table Container -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th scope="col" class="py-4 px-6 w-16 text-center">No</th>
                            <th scope="col" class="py-4 px-6 w-36">Kode Mapel</th>
                            <th scope="col" class="py-4 px-6">Nama Mata Pelajaran</th>
                            <th scope="col" class="py-4 px-6">Diajarkan di Kelas</th>
                            <th scope="col" class="py-4 px-6 w-36 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($mapels as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 text-center font-medium text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $item->kode_mapel ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-900">
                                    {{ $item->nama_mapel }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($item->kelases as $kls)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $kls->nama_kelas }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">Belum dihubungkan ke kelas</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" 
                                                onclick="openModal('modalEditMapel{{ $item->id }}')"
                                                class="p-2 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition-colors" 
                                                title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('admin.mapel.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mapel ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-2xl mb-2 block text-slate-300"></i>
                                    Belum ada data mata pelajaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Mapel -->
<div id="modalTambahMapel" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-xl transform transition-all">
        <form action="{{ route('admin.mapel.store') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Tambah Mata Pelajaran</h3>
                <button type="button" onclick="closeModal('modalTambahMapel')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kode Mapel</label>
                    <input type="text" name="kode_mapel" placeholder="Contoh: MTK-01" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_mapel" placeholder="Contoh: Matematika" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-2">Pilih Kelas yang Mengampu</label>
                    <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-xl">
                        @foreach($kelases as $kelas)
                            <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-100 cursor-pointer">
                                <input type="checkbox" name="kelas_ids[]" value="{{ $kelas->id }}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs text-slate-700 font-medium">{{ $kelas->nama_kelas }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                <button type="button" onclick="closeModal('modalTambahMapel')" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-medium rounded-xl transition shadow-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-xl shadow-md shadow-blue-500/20 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Mapel -->
@foreach($mapels as $item)
    <div id="modalEditMapel{{ $item->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-xl transform transition-all">
            <form action="{{ route('admin.mapel.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Edit Mata Pelajaran</h3>
                    <button type="button" onclick="closeModal('modalEditMapel{{ $item->id }}')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Kode Mapel</label>
                        <input type="text" name="kode_mapel" value="{{ $item->kode_mapel }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_mapel" value="{{ $item->nama_mapel }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-2">Pilih Kelas</label>
                        <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($kelases as $kelas)
                                <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-100 cursor-pointer">
                                    <input type="checkbox" name="kelas_ids[]" value="{{ $kelas->id }}" {{ $item->kelases->contains($kelas->id) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-xs text-slate-700 font-medium">{{ $kelas->nama_kelas }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                    <button type="button" onclick="closeModal('modalEditMapel{{ $item->id }}')" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-medium rounded-xl transition shadow-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-xl shadow-md shadow-blue-500/20 transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection