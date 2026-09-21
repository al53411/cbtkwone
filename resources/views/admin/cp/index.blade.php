@extends('layouts.admin')

@section('title', 'Capaian Pembelajaran')
@section('page_title', 'Capaian Pembelajaran')

@section('content')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
</style>

<div class="w-full bg-white text-slate-800 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Master Capaian Pembelajaran (CP)</h1>
            <p class="text-slate-400 text-sm mt-1">Pengaturan standar Capaian Pembelajaran Kurikulum Merdeka</p>
        </div>
        <button onclick="openModal('modalAdd')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition-all shadow-lg flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah CP Baru
        </button>
    </div>

    <!-- Notification -->
    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 rounded-xl text-sm backdrop-blur-md flex items-center">
        <i class="fa-solid fa-circle-check mr-2 text-base"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Filter Card -->
    <div class="glass-card p-4 rounded-2xl mb-6 shadow-md">
        <form method="GET" action="{{ route('admin.capaian-pembelajaran.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mata Pelajaran</label>
                <select name="mapel_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-white/80 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach($mapels as $m)
                        <option value="{{ $m->id }}" {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fase Pembelajaran</label>
                <select name="fase" onchange="this.form.submit()" class="w-full px-3 py-2 bg-white/80 border border-slate-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Semua Fase --</option>
                    @foreach(['A','B','C','D','E','F'] as $f)
                        <option value="{{ $f }}" {{ request('fase') == $f ? 'selected' : '' }}>Fase {{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <a href="{{ route('admin.capaian-pembelajaran.index') }}" class="w-full py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl text-sm text-center transition-all">Reset Filter</a>
            </div>
        </form>
    </div>

    <!-- Data Tabel -->
    <div class="glass-card rounded-2xl shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/90 text-slate-300 text-xs uppercase tracking-wider border-b border-slate-700">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4 w-40">Mata Pelajaran</th>
                        <th class="p-4 w-24 text-center">Fase</th>
                        <th class="p-4 w-48">Elemen</th>
                        <th class="p-4">Deskripsi Capaian Pembelajaran</th>
                        <th class="p-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-sm font-medium">
                    @forelse($cps as $index => $item)
                    <tr class="hover:bg-white/50 transition-all">
                        <td class="p-4 text-center text-slate-500">
                            {{ method_exists($cps, 'firstItem') ? $cps->firstItem() + $index : $index + 1 }}
                        </td>
                        <td class="p-4 font-semibold text-slate-800">{{ $item->mapel->nama_mapel ?? '-' }}</td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 font-bold rounded-lg text-xs">Fase {{ $item->fase }}</span>
                        </td>
                        <td class="p-4 font-semibold text-slate-700">{{ $item->elemen }}</td>
                        <td class="p-4 text-slate-600 leading-relaxed text-xs sm:text-sm">{{ $item->deskripsi_cp }}</td>
                        <td class="p-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="editModal({{ $item }})" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.capaian-pembelajaran.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data CP ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">Belum ada data Capaian Pembelajaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($cps, 'hasPages') && $cps->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $cps->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah CP -->
<div id="modalAdd" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl w-full max-w-xl p-6 shadow-2xl relative">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Capaian Pembelajaran</h3>
        <form action="{{ route('admin.capaian-pembelajaran.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Mata Pelajaran</label>
                    <select name="mapel_id" required class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($mapels as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Fase</label>
                    <select name="fase" required class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Fase --</option>
                        <option value="A">Fase A (Kelas 1-2 SD)</option>
                        <option value="B">Fase B (Kelas 3-4 SD)</option>
                        <option value="C">Fase C (Kelas 5-6 SD)</option>
                        <option value="D">Fase D (SMP)</option>
                        <option value="E">Fase E (SMA/SMK 10)</option>
                        <option value="F">Fase F (SMA/SMK 11-12)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Elemen CP</label>
                <input type="text" name="elemen" required placeholder="Contoh: Pemahaman IPAS / Menulis / Geometri" class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Deskripsi Capaian Pembelajaran</label>
                <textarea name="deskripsi_cp" rows="4" required placeholder="Tuliskan teks narasi Capaian Pembelajaran secara lengkap..." class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modalAdd')" class="px-4 py-2 text-slate-600 text-sm font-semibold hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700">Simpan CP</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit CP -->
<div id="modalEdit" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl w-full max-w-xl p-6 shadow-2xl relative">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Edit Capaian Pembelajaran</h3>
        <form id="formEdit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Mata Pelajaran</label>
                    <select id="edit_mapel_id" name="mapel_id" required class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        @foreach($mapels as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Fase</label>
                    <select id="edit_fase" name="fase" required class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="A">Fase A</option>
                        <option value="B">Fase B</option>
                        <option value="C">Fase C</option>
                        <option value="D">Fase D</option>
                        <option value="E">Fase E</option>
                        <option value="F">Fase F</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Elemen CP</label>
                <input type="text" id="edit_elemen" name="elemen" required class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Deskripsi Capaian Pembelajaran</label>
                <textarea id="edit_deskripsi_cp" name="deskripsi_cp" rows="4" required class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modalEdit')" class="px-4 py-2 text-slate-600 text-sm font-semibold hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700">Perbarui CP</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    function editModal(data) {
        document.getElementById('formEdit').action = `/admin/capaian-pembelajaran/${data.id}`;
        document.getElementById('edit_mapel_id').value = data.mapel_id;
        document.getElementById('edit_fase').value = data.fase;
        document.getElementById('edit_elemen').value = data.elemen;
        document.getElementById('edit_deskripsi_cp').value = data.deskripsi_cp;
        openModal('modalEdit');
    }
</script>
@endsection