@extends('layouts.guru')

@section('title', 'Modul Ajar')
@section('page_title', 'Modul Ajar')

@section('content')
<div class="w-full bg-white text-slate-800 p-4 sm:p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Modul Ajar (MA)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola modul ajar dan perangkat pembelajaran berbasis Kurikulum Merdeka</p>
        </div>
        <button onclick="openCreateModal()" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Modul Ajar
        </button>
    </div>

    <!-- Data Table -->
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
                        <th class="p-3 text-center w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($modulAjars as $modul)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3 font-semibold text-slate-800 max-w-xs">{{ $modul->judul }}</td>
                            <td class="p-3">{{ $modul->mapel->nama_mapel ?? '-' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $modul->fase ? 'Fase '.$modul->fase : '-' }} {{ $modul->kelas ? '- Kelas '.$modul->kelas : '' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">
                                    Semester {{ $modul->semester }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $modul->tahun_ajaran }}</div>
                            </td>
                            <td class="p-3 text-center">{{ $modul->alokasi_waktu }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $modul->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ ucfirst($modul->status ?? 'draft') }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('guru.modul_ajar.show', $modul->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded font-semibold" title="Lihat Detail">
                                        Lihat
                                    </a>
                                    <a href="{{ route('guru.modul_ajar.print', $modul->id) }}" target="_blank" class="p-1.5 text-sky-600 hover:bg-sky-50 rounded font-semibold" title="Cetak Modul">
                                        Cetak
                                    </a>
                                    <button type="button" onclick='openEditModal(@json($modul->load(["tujuanPembelajarans", "capaianPembelajaran"])))' class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded font-semibold" title="Edit Modul">
                                        Edit
                                    </button>
                                    <form action="{{ route('guru.modul_ajar.destroy', $modul->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded font-semibold" title="Hapus Modul">
                                            Hapus
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
        <div class="p-4 border-t border-slate-100">
            {{ $modulAjars->links() }}
        </div>
    </div>
</div>

<!-- MODAL FORM -->
<div id="modulModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden p-4">
    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 id="modalTitle" class="text-sm font-bold text-slate-800">Tambah Modul Ajar</h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="modulForm" action="{{ route('guru.modul_ajar.store') }}" method="POST" class="flex flex-col overflow-hidden">
            @csrf
            <div id="methodContainer"></div>

            <div class="p-6 space-y-4 overflow-y-auto text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Judul Modul Ajar *</label>
                    <input type="text" id="judul" name="judul" required class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mata Pelajaran *</label>
                        <select id="mapel_id" name="mapel_id" onchange="fetchCpOptions()" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Fase *</label>
                        <select id="fase" name="fase" onchange="fetchCpOptions()" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Pilih Fase --</option>
                            @foreach(['A','B','C','D','E','F'] as $faseOpt)
                                <option value="{{ $faseOpt }}">Fase {{ $faseOpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kelas *</label>
                        <select id="kelas" name="kelas" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelases as $kelasItem)
                                <option value="{{ $kelasItem->id ?? $kelasItem->tingkat }}">{{ $kelasItem->nama_kelas ?? 'Kelas '.$kelasItem->tingkat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <label class="block font-bold text-indigo-900">Capaian Pembelajaran (CP) *</label>
                    <select id="cp_id" name="cp_id" onchange="fetchTpByCp()" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="">-- Pilih Capaian Pembelajaran (Pilih Mapel & Fase dulu) --</option>
                    </select>
                </div>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="font-bold text-indigo-900">Pilih Tujuan Pembelajaran (TP) *</label>
                            <p class="text-[10px] text-slate-500">Centang TP yang sesuai dengan modul ajar ini.</p>
                        </div>
                    </div>
                    <div id="tpContainer" class="space-y-2 max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-lg bg-white">
                        <p class="text-xs text-slate-400">Silakan pilih Capaian Pembelajaran (CP) terlebih dahulu.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Semester *</label>
                        <select id="semester" name="semester" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ (isset($semesterAktif) && $semesterAktif == '1') ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                            <option value="2" {{ (isset($semesterAktif) && $semesterAktif == '2') ? 'selected' : '' }}>Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Thn Ajaran *</label>
                        <select id="tahun_ajaran" name="tahun_ajaran" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg">
                            @foreach($tahunAjarans as $ta)
                                <option value="{{ $ta->tahun ?? $ta->nama }}" {{ (isset($tahunAjaranAktif) && $tahunAjaranAktif == ($ta->tahun ?? $ta->nama)) ? 'selected' : '' }}>
                                    {{ $ta->tahun ?? $ta->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alokasi Waktu *</label>
                        <input type="text" id="alokasi_waktu" name="alokasi_waktu" placeholder="2 x 35 Menit" required class="w-full p-2.5 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status *</label>
                        <select id="status" name="status" required class="w-full text-xs p-2.5 border border-slate-300 rounded-lg">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Peserta Didik *</label>
                        <input type="text" id="target_peserta_didik" name="target_peserta_didik" placeholder="Reguler / Tipikal" required class="w-full p-2.5 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Model Pembelajaran</label>
                        <input type="text" id="model_pembelajaran" name="model_pembelajaran" placeholder="Problem Based Learning" class="w-full p-2.5 border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kompetensi Awal</label>
                        <textarea id="kompetensi_awal" name="kompetensi_awal" rows="3" class="w-full p-2 border border-slate-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pemahaman Bermakna</label>
                        <textarea id="pemahaman_bermakna" name="pemahaman_bermakna" rows="3" class="w-full p-2 border border-slate-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pertanyaan Pemantik</label>
                        <textarea id="pertanyaan_pemantik" name="pertanyaan_pemantik" rows="3" class="w-full p-2 border border-slate-300 rounded-lg"></textarea>
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-xs text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    async function fetchCpOptions(preselectedCpId = null) {
        const mapelId = document.getElementById('mapel_id').value;
        const fase = document.getElementById('fase').value;
        const cpSelect = document.getElementById('cp_id');

        cpSelect.innerHTML = '<option value="">-- Memuat Data CP... --</option>';
        document.getElementById('tpContainer').innerHTML = '<p class="text-xs text-slate-400">Silakan pilih Capaian Pembelajaran (CP) terlebih dahulu.</p>';

        if (!mapelId || !fase) {
            cpSelect.innerHTML = '<option value="">-- Pilih Capaian Pembelajaran (Pilih Mapel & Fase dulu) --</option>';
            return;
        }

        try {
            const params = new URLSearchParams({ mapel_id: mapelId, fase: fase });
            const response = await fetch(`/guru/get-cp-filtered?${params.toString()}`);
            const data = await response.json();

            cpSelect.innerHTML = '<option value="">-- Pilih Capaian Pembelajaran --</option>';
            if (data && data.length > 0) {
                data.forEach(cp => {
                    const elemen = cp.elemen || 'Elemen Umum';
                    const deskripsiAsli = cp.deskripsi_cp || cp.deskripsi || '';
                    const maxLen = 90;
                    const deskripsiPendek = deskripsiAsli.length > maxLen 
                        ? deskripsiAsli.substring(0, maxLen) + '...' 
                        : deskripsiAsli;

                    const displayText = `[${elemen}] ${deskripsiPendek}`;
                    const opt = document.createElement('option');
                    opt.value = cp.id;
                    opt.textContent = displayText;
                    opt.title = `[${elemen}] ${deskripsiAsli}`;
                    
                    if (preselectedCpId && cp.id == preselectedCpId) {
                        opt.selected = true;
                    }
                    cpSelect.appendChild(opt);
                });
            } else {
                cpSelect.innerHTML = '<option value="">-- Tidak ada CP ditemukan untuk Mapel & Fase ini --</option>';
            }
        } catch (e) {
            console.error('Error fetching CP:', e);
            cpSelect.innerHTML = '<option value="">-- Gagal memuat CP --</option>';
        }
    }

    async function fetchTpByCp(preselectedTpIds = []) {
        const cpId = document.getElementById('cp_id').value;
        const tpContainer = document.getElementById('tpContainer');

        if (!cpId) {
            tpContainer.innerHTML = '<p class="text-xs text-slate-400">Silakan pilih Capaian Pembelajaran (CP) terlebih dahulu.</p>';
            return;
        }

        tpContainer.innerHTML = '<p class="text-xs text-slate-400">Memuat Tujuan Pembelajaran...</p>';

        try {
            const url = `{{ url('guru/get-tp-by-cp') }}/${cpId}`;
            const response = await fetch(url);
            const data = await response.json();

            tpContainer.innerHTML = '';
            if (data && data.length > 0) {
                data.forEach(tp => {
                    const isChecked = preselectedTpIds.some(id => id == tp.id) ? 'checked' : '';
                    const kodeText = tp.kode_tp ? `[${tp.kode_tp}] ` : '';
                    const div = document.createElement('div');
                    div.className = 'flex items-start gap-2 p-1.5 hover:bg-slate-50 rounded';
                    div.innerHTML = `
                        <input type="checkbox" name="tp_ids[]" value="${tp.id}" id="tp_${tp.id}" ${isChecked} class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="tp_${tp.id}" class="text-slate-700 cursor-pointer text-xs">${kodeText}${tp.deskripsi_tp || tp.deskripsi || tp.tujuan}</label>
                    `;
                    tpContainer.appendChild(div);
                });
            } else {
                tpContainer.innerHTML = '<p class="text-xs text-slate-400">Tidak ada Tujuan Pembelajaran (TP) yang terdaftar untuk CP ini.</p>';
            }
        } catch (e) {
            console.error('Error fetching TP:', e);
            tpContainer.innerHTML = '<p class="text-xs text-rose-500">Gagal memuat Tujuan Pembelajaran.</p>';
        }
    }

    function openCreateModal() {
        const form = document.getElementById('modulForm');
        form.reset();
        form.action = "{{ route('guru.modul_ajar.store') }}";
        document.getElementById('methodContainer').innerHTML = '';
        document.getElementById('modalTitle').innerText = 'Tambah Modul Ajar';
        document.getElementById('cp_id').innerHTML = '<option value="">-- Pilih Capaian Pembelajaran (Pilih Mapel & Fase dulu) --</option>';
        document.getElementById('tpContainer').innerHTML = '<p class="text-xs text-slate-400">Silakan pilih Capaian Pembelajaran (CP) terlebih dahulu.</p>';
        document.getElementById('modulModal').classList.remove('hidden');
    }

    async function openEditModal(data) {
        const form = document.getElementById('modulForm');
        form.reset();
        form.action = `/guru/modul-ajar/${data.id}`;
        document.getElementById('methodContainer').innerHTML = '@method("PUT")';
        document.getElementById('modalTitle').innerText = 'Edit Modul Ajar';

        document.getElementById('judul').value = data.judul || '';
        document.getElementById('mapel_id').value = data.mapel_id || '';
        
        let faseValue = data.fase || '';
        faseValue = faseValue.replace('Fase ', '').trim();
        document.getElementById('fase').value = faseValue;
        document.getElementById('kelas').value = data.kelas || '';

        await fetchCpOptions(data.cp_id);
        document.getElementById('cp_id').value = data.cp_id || '';

        const attachedTpIds = (data.tujuan_pembelajarans || []).map(tp => tp.id);
        await fetchTpByCp(attachedTpIds);

        document.getElementById('semester').value = data.semester || '';
        document.getElementById('tahun_ajaran').value = data.tahun_ajaran || '';
        document.getElementById('alokasi_waktu').value = data.alokasi_waktu || '';
        document.getElementById('target_peserta_didik').value = data.target_peserta_didik || '';
        document.getElementById('model_pembelajaran').value = data.model_pembelajaran || '';
        document.getElementById('status').value = data.status || 'draft';
        document.getElementById('kompetensi_awal').value = data.kompetensi_awal || '';
        document.getElementById('pemahaman_bermakna').value = data.pemahaman_bermakna || '';
        document.getElementById('pertanyaan_pemantik').value = data.pertanyaan_pemantik || '';

        document.getElementById('modulModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modulModal').classList.add('hidden');
    }
</script>
@endsection