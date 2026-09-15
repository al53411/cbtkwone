@extends('layouts.admin')

@section('title', 'Modul Ajar')
@section('page_title', 'Modul Ajar')

@section('content')
<div class="w-full bg-white text-slate-800 p-4 sm:p-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Modul Ajar (MA)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola modul ajar dan perangkat pembelajaran berbasis Kurikulum Merdeka</p>
        </div>
        <div>
            <button onclick="openCreateModal()" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Modul Ajar
            </button>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="mb-4 p-4 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 font-bold">&times;</button>
        </div>
    @endif

    <!-- Alert Error Validation -->
    @if($errors->any())
        <div class="mb-4 p-4 text-xs text-rose-700 bg-rose-50 border border-rose-200 rounded-lg">
            <p class="font-bold mb-1">Terjadi Kesalahan:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 mb-6 shadow-sm">
        <form method="GET" action="{{ route('admin.modul_ajar.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Mata Pelajaran</label>
                <select name="mapel_id" onchange="this.form.submit()" class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Fase</label>
                <select name="fase" onchange="this.form.submit()" class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Semua Fase --</option>
                    @foreach(['A','B','C','D','E','F'] as $fase)
                        <option value="{{ $fase }}" {{ request('fase') == $fase ? 'selected' : '' }}>
                            Fase {{ $fase }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ route('admin.modul_ajar.index') }}" class="px-3 py-2.5 text-xs text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase">
                    <tr>
                        <th class="p-3">Judul Modul</th>
                        <th class="p-3">Mata Pelajaran</th>
                        <th class="p-3">Fase / Kelas</th>
                        <th class="p-3 text-center">Semester / Thn</th>
                        <th class="p-3 text-center">Alokasi Waktu</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($modulAjars as $modul)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3 font-semibold text-slate-800 max-w-xs">
                                <a href="{{ route('admin.modul_ajar.show', $modul->id) }}" class="hover:text-indigo-600 line-clamp-2">
                                    {{ $modul->judul }}
                                </a>
                            </td>
                            <td class="p-3">
                                <div class="font-medium text-slate-800">{{ $modul->mapel->nama_mapel ?? '-' }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    Fase {{ $modul->fase }} - Kelas {{ $modul->kelas }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 rounded text-[10px] font-semibold {{ $modul->semester == 1 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    Sem. {{ $modul->semester }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $modul->tahun_ajaran }}</div>
                            </td>
                            <td class="p-3 text-center font-medium">{{ $modul->alokasi_waktu }}</td>
                            <td class="p-3 text-center">
                                @if($modul->status === 'published')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.modul_ajar.show', $modul->id) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button onclick="openEditModal({{ json_encode($modul) }})" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.modul_ajar.destroy', $modul->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Modul Ajar ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-slate-100 rounded transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">Belum ada data Modul Ajar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($modulAjars->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $modulAjars->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL FORM (TAMBAH / EDIT) -->
<div id="modulModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 id="modalTitle" class="text-sm font-bold text-slate-800">Tambah Modul Ajar (MA)</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <!-- Form Modal -->
        <form id="modulForm" action="{{ route('admin.modul_ajar.store') }}" method="POST">
            @csrf
            <div id="methodContainer"></div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs">
                
                <!-- Judul Modul -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Judul Modul Ajar <span class="text-rose-500">*</span></label>
                    <input type="text" id="judul" name="judul" placeholder="Contoh: Modul Ajar Matematika - Operasi Hitung" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Select Mapel & CP -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select id="mapel_id" name="mapel_id" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Capaian Pembelajaran (CP)</label>
                        <select id="cp_id" name="cp_id" class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih CP (Opsional) --</option>
                            @foreach($cps as $cp)
                                <option value="{{ $cp->id }}">
                                    {{ $cp->elemen ?? 'CP' }} - {{ Str::limit($cp->deskripsi_cp ?? '', 50) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Grid Fase, Kelas, Alokasi, Tahun Ajaran -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Fase <span class="text-rose-500">*</span></label>
                        <select id="fase" name="fase" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach(['A','B','C','D','E','F'] as $fase)
                                <option value="{{ $fase }}" {{ $fase == 'C' ? 'selected' : '' }}>Fase {{ $fase }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kelas <span class="text-rose-500">*</span></label>
                        <input type="text" id="kelas" name="kelas" value="IV" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                        <select id="semester" name="semester" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="2026/2027" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Grid Alokasi Waktu, Target Peserta, Model Pembelajaran, Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alokasi Waktu <span class="text-rose-500">*</span></label>
                        <input type="text" id="alokasi_waktu" name="alokasi_waktu" value="2 x 35 JP" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Peserta Didik <span class="text-rose-500">*</span></label>
                        <input type="text" id="target_peserta_didik" name="target_peserta_didik" value="Reguler / Tipikal" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Model Pembelajaran</label>
                        <input type="text" id="model_pembelajaran" name="model_pembelajaran" value="Problem Based Learning (PBL)" class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Modul <span class="text-rose-500">*</span></label>
                        <select id="status" name="status" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <!-- Textarea Kompetensi Awal -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kompetensi Awal</label>
                    <textarea id="kompetensi_awal" name="kompetensi_awal" rows="2" placeholder="Kompetensi dasar sebelum pembelajaran..." class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <!-- Textarea Pemahaman Bermakna & Pertanyaan Pemantik -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pemahaman Bermakna</label>
                    <textarea id="pemahaman_bermakna" name="pemahaman_bermakna" rows="2" placeholder="Manfaat yang diperoleh peserta didik setelah pembelajaran..." class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pertanyaan Pemantik</label>
                    <textarea id="pertanyaan_pemantik" name="pertanyaan_pemantik" rows="2" placeholder="Pertanyaan untuk menumbuhkan pemikiran kritis..." class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-xs text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Simpan Modul</button>
            </div>
        </form>
    </div>
</div>

<!-- JAVASCRIPT LOGIC -->
<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Modul Ajar (MA)';
        document.getElementById('modulForm').action = "{{ route('admin.modul_ajar.store') }}";
        document.getElementById('methodContainer').innerHTML = '';
        
        // Reset Form Inputs
        document.getElementById('judul').value = '';
        document.getElementById('mapel_id').value = '';
        document.getElementById('cp_id').value = '';
        document.getElementById('fase').value = 'C';
        document.getElementById('kelas').value = 'IV';
        document.getElementById('semester').value = '1';
        document.getElementById('tahun_ajaran').value = '2026/2027';
        document.getElementById('alokasi_waktu').value = '2 x 35 JP';
        document.getElementById('target_peserta_didik').value = 'Reguler / Tipikal';
        document.getElementById('model_pembelajaran').value = 'Problem Based Learning (PBL)';
        document.getElementById('status').value = 'draft';
        document.getElementById('kompetensi_awal').value = '';
        document.getElementById('pemahaman_bermakna').value = '';
        document.getElementById('pertanyaan_pemantik').value = '';

        document.getElementById('modulModal').classList.remove('hidden');
    }

    function openEditModal(modul) {
        document.getElementById('modalTitle').innerText = 'Edit Modul Ajar (MA)';
        document.getElementById('modulForm').action = `/admin/modul_ajar/${modul.id}`;
        document.getElementById('methodContainer').innerHTML = `@method('PUT')`;

        // Fill Form Inputs
        document.getElementById('judul').value = modul.judul || '';
        document.getElementById('mapel_id').value = modul.mapel_id || '';
        document.getElementById('cp_id').value = modul.cp_id || '';
        document.getElementById('fase').value = modul.fase || 'C';
        document.getElementById('kelas').value = modul.kelas || '';
        document.getElementById('semester').value = modul.semester || '1';
        document.getElementById('tahun_ajaran').value = modul.tahun_ajaran || '';
        document.getElementById('alokasi_waktu').value = modul.alokasi_waktu || '';
        document.getElementById('target_peserta_didik').value = modul.target_peserta_didik || '';
        document.getElementById('model_pembelajaran').value = modul.model_pembelajaran || '';
        document.getElementById('status').value = modul.status || 'draft';
        document.getElementById('kompetensi_awal').value = modul.kompetensi_awal || '';
        document.getElementById('pemahaman_bermakna').value = modul.pemahaman_bermakna || '';
        document.getElementById('pertanyaan_pemantik').value = modul.pertanyaan_pemantik || '';

        document.getElementById('modulModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modulModal').classList.add('hidden');
    }
</script>
@endsection