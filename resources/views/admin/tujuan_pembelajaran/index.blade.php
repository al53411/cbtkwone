@extends('layouts.admin')

@section('title', $profilSekolah->nama_sekolah ?? 'SDN Kawu 1')

@section('content')
<div class="w-full bg-white text-slate-800 p-4 sm:p-6 space-y-6">
    
    <!-- Section Form Input TP Baru -->
    <div class="flat-card p-5 sm:p-6 shadow-sm border border-slate-200 rounded-xl bg-white">
        <div class="flex items-center space-x-3 mb-5 pb-3 border-b border-slate-100">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-bullseye text-base"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">Tambah Tujuan Pembelajaran (TP)</h3>
                <p class="text-xs text-slate-500">Tambahkan rumusan TP berdasarkan Capaian Pembelajaran Kurikulum Merdeka.</p>
            </div>
        </div>

        <form action="{{ route('admin.tujuan-pembelajaran.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="mapel_id" id="mapel_id" class="flat-input w-full p-2.5 text-xs" required onchange="fetchElemenOptions()">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas / Tingkat -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kelas / Tingkat</label>
                    <select name="kelas_id" class="flat-input w-full p-2.5 text-xs">
                        <option value="">-- Semua Kelas / Opsional --</option>
                        @foreach($kelases as $kelas)
                            <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fase -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fase</label>
                    <select name="fase" id="fase" class="flat-input w-full p-2.5 text-xs" onchange="fetchElemenOptions()">
                        <option value="">-- Pilih Fase --</option>
                        <option value="Fase A" {{ old('fase') == 'Fase A' ? 'selected' : '' }}>Fase A (Kelas 1 - 2)</option>
                        <option value="Fase B" {{ old('fase') == 'Fase B' ? 'selected' : '' }}>Fase B (Kelas 3 - 4)</option>
                        <option value="Fase C" {{ old('fase') == 'Fase C' ? 'selected' : '' }}>Fase C (Kelas 5 - 6)</option>
                    </select>
                </div>

                <!-- Kode TP -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kode TP (Opsional)</label>
                    <input type="text" name="kode_tp" value="{{ old('kode_tp') }}" placeholder="Contoh: TP 1.1" class="flat-input w-full p-2.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Elemen Pembelajaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Elemen Pembelajaran</label>
                    <select name="elemen" id="elemen" class="flat-input w-full p-2.5 text-xs">
                        <option value="">-- Pilih Mapel & Fase Dulu --</option>
                    </select>
                </div>

                <!-- Deskripsi TP Dinamis (Poin-poin) -->
                <div class="md:col-span-2 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-700">
                            Deskripsi Tujuan Pembelajaran <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" onclick="addTpRow()" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center space-x-1">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Poin TP</span>
                        </button>
                    </div>

                    <div id="tpContainer" class="space-y-2">
                        <div class="flex items-center space-x-2 tp-row">
                            <span class="text-xs font-bold text-slate-400 w-5 text-center row-number">1.</span>
                            <input type="text" name="deskripsi_tp[]" placeholder="Contoh: Peserta didik mampu menjelaskan..." class="flat-input w-full p-2.5 text-xs" required>
                            <button type="button" onclick="removeTpRow(this)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition opacity-50 cursor-not-allowed btn-remove" disabled>
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg shadow-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Simpan Tujuan Pembelajaran</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar TP -->
    <div class="flat-card p-5 sm:p-6 shadow-sm border border-slate-200 rounded-xl bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Daftar Tujuan Pembelajaran</h3>
                <p class="text-xs text-slate-500">Daftar rumusan TP yang telah tersimpan di sistem.</p>
            </div>
            
            <div class="w-full sm:w-64">
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari kode, mapel, atau deskripsi..." class="flat-input w-full p-2 text-xs">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="tpTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <th class="p-3 w-12 text-center">No</th>
                        <th class="p-3 w-28">Kode / Fase</th>
                        <th class="p-3 w-40">Mata Pelajaran</th>
                        <th class="p-3 w-32">Elemen / Kelas</th>
                        <th class="p-3">Deskripsi Tujuan Pembelajaran</th>
                        <th class="p-3 w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($tps as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 text-center font-medium text-slate-500">{{ $index + 1 }}</td>
                            <td class="p-3">
                                <span class="font-bold text-slate-800 block">{{ $item->kode_tp ?? '-' }}</span>
                                <span class="text-[10px] text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded font-medium inline-block mt-0.5">{{ $item->fase ?? 'Semua' }}</span>
                            </td>
                            <td class="p-3 font-semibold text-slate-800">
                                {{ $item->mapel->nama_mapel ?? '-' }}
                            </td>
                            <td class="p-3">
                                <span class="block text-slate-700 font-medium">{{ $item->elemen ?? '-' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $item->kelas->nama_kelas ?? 'Semua Kelas' }}</span>
                            </td>
                            <td class="p-3 leading-relaxed">
                                @if(is_array($item->deskripsi_tp))
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($item->deskripsi_tp as $tp)
                                            <li>{{ $tp }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $item->deskripsi_tp }}
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" onclick="openEditModal({{ json_encode($item) }})" class="p-1.5 rounded-md text-amber-600 bg-amber-50 hover:bg-amber-100 transition" title="Edit TP">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="{{ route('admin.tujuan-pembelajaran.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus TP ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md text-rose-600 bg-rose-50 hover:bg-rose-100 transition" title="Hapus TP">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                Belum ada data Tujuan Pembelajaran yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Edit TP -->
<div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4" onclick="handleModalClick(event)">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden transform transition-all">
        <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center space-x-2">
                <i class="fa-solid fa-pen-to-square text-blue-400"></i>
                <span>Edit Tujuan Pembelajaran</span>
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran</label>
                    <select name="mapel_id" id="edit_mapel_id" class="flat-input w-full p-2 text-xs" required onchange="fetchEditElemenOptions()">
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas / Tingkat</label>
                    <select name="kelas_id" id="edit_kelas_id" class="flat-input w-full p-2 text-xs">
                        <option value="">-- Semua Kelas / Opsional --</option>
                        @foreach($kelases as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase</label>
                    <select name="fase" id="edit_fase" class="flat-input w-full p-2 text-xs" onchange="fetchEditElemenOptions()">
                        <option value="">-- Pilih Fase --</option>
                        <option value="Fase A">Fase A (Kelas 1 - 2)</option>
                        <option value="Fase B">Fase B (Kelas 3 - 4)</option>
                        <option value="Fase C">Fase C (Kelas 5 - 6)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode TP</label>
                    <input type="text" name="kode_tp" id="edit_kode_tp" class="flat-input w-full p-2 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Elemen Pembelajaran</label>
                <select name="elemen" id="edit_elemen" class="flat-input w-full p-2 text-xs">
                    <option value="">-- Pilih Elemen --</option>
                </select>
            </div>

            <!-- Deskripsi TP Dinamis (Modal Edit) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700">Deskripsi Tujuan Pembelajaran <span class="text-rose-500">*</span></label>
                    <button type="button" onclick="addEditTpRow()" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center space-x-1">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Tambah Poin</span>
                    </button>
                </div>
                
                <div id="editTpContainer" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <!-- Row akan di-generate via JS -->
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter / Pencarian Tabel Live
function filterTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("tpTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let textContent = tr[i].textContent || tr[i].innerText;
        if (textContent.toLowerCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

// Dynamic Input Form Utama
function addTpRow(value = '') {
    const container = document.getElementById('tpContainer');
    const rowCount = container.children.length + 1;

    const row = document.createElement('div');
    row.className = 'flex items-center space-x-2 tp-row';
    row.innerHTML = `
        <span class="text-xs font-bold text-slate-400 w-5 text-center row-number">${rowCount}.</span>
        <input type="text" name="deskripsi_tp[]" value="${value}" placeholder="Masukkan poin TP..." class="flat-input w-full p-2.5 text-xs" required>
        <button type="button" onclick="removeTpRow(this)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition btn-remove">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    `;

    container.appendChild(row);
    updateRowNumbers(container);
}

function removeTpRow(button) {
    const container = document.getElementById('tpContainer');
    if (container.children.length > 1) {
        button.closest('.tp-row').remove();
        updateRowNumbers(container);
    }
}

function updateRowNumbers(container) {
    const rows = container.querySelectorAll('.tp-row');
    rows.forEach((row, index) => {
        row.querySelector('.row-number').textContent = `${index + 1}.`;
        const btnRemove = row.querySelector('.btn-remove');
        if (rows.length === 1) {
            btnRemove.disabled = true;
            btnRemove.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btnRemove.disabled = false;
            btnRemove.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
}

// Dynamic Input Modal Edit
function addEditTpRow(value = '') {
    const container = document.getElementById('editTpContainer');
    const rowCount = container.children.length + 1;

    const row = document.createElement('div');
    row.className = 'flex items-center space-x-2 edit-tp-row';
    row.innerHTML = `
        <span class="text-xs font-bold text-slate-400 w-5 text-center edit-row-number">${rowCount}.</span>
        <input type="text" name="deskripsi_tp[]" value="${value}" placeholder="Masukkan poin TP..." class="flat-input w-full p-2 text-xs" required>
        <button type="button" onclick="removeEditTpRow(this)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition btn-edit-remove">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    `;

    container.appendChild(row);
    updateEditRowNumbers();
}

function removeEditTpRow(button) {
    const container = document.getElementById('editTpContainer');
    if (container.children.length > 1) {
        button.closest('.edit-tp-row').remove();
        updateEditRowNumbers();
    }
}

function updateEditRowNumbers() {
    const container = document.getElementById('editTpContainer');
    const rows = container.querySelectorAll('.edit-tp-row');
    rows.forEach((row, index) => {
        row.querySelector('.edit-row-number').textContent = `${index + 1}.`;
        const btnRemove = row.querySelector('.btn-edit-remove');
        if (rows.length === 1) {
            btnRemove.disabled = true;
            btnRemove.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btnRemove.disabled = false;
            btnRemove.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
}

// Fungsi AJAX Form Utama
async function fetchElemenOptions(selectedElemen = '{{ old("elemen") }}') {
    const mapelId = document.getElementById('mapel_id').value;
    let faseFull = document.getElementById('fase').value;
    const elemenSelect = document.getElementById('elemen');

    let fase = faseFull ? faseFull.split('(')[0].trim() : '';

    elemenSelect.innerHTML = '<option value="">Memuat elemen...</option>';

    if (!mapelId || !fase) {
        elemenSelect.innerHTML = '<option value="">-- Pilih Mapel & Fase Dulu --</option>';
        return;
    }

    try {
        const params = new URLSearchParams({ mapel_id: mapelId, fase: fase });
        const response = await fetch(`{{ route('admin.get_elemen_cp') }}?${params.toString()}`);
        const data = await response.json();

        elemenSelect.innerHTML = '<option value="">-- Pilih Elemen Pembelajaran --</option>';
        if (data && data.length > 0) {
            data.forEach(elemen => {
                const option = document.createElement('option');
                option.value = elemen;
                option.textContent = elemen;
                if (elemen === selectedElemen) {
                    option.selected = true;
                }
                elemenSelect.appendChild(option);
            });
        } else {
            elemenSelect.innerHTML = `
                <option value="">-- Data CP Kosong --</option>
                <option value="Umum">Umum</option>
            `;
        }
    } catch (error) {
        console.error('Gagal memuat elemen:', error);
        elemenSelect.innerHTML = '<option value="">Gagal memuat data elemen</option>';
    }
}

// Fungsi AJAX Modal Edit
async function fetchEditElemenOptions(selectedElemen = '') {
    const mapelId = document.getElementById('edit_mapel_id').value;
    let faseFull = document.getElementById('edit_fase').value;
    const elemenSelect = document.getElementById('edit_elemen');

    let fase = faseFull ? faseFull.split('(')[0].trim() : '';

    elemenSelect.innerHTML = '<option value="">Memuat elemen...</option>';

    if (!mapelId || !fase) {
        elemenSelect.innerHTML = '<option value="">-- Pilih Mapel & Fase Dulu --</option>';
        return;
    }

    try {
        const params = new URLSearchParams({ mapel_id: mapelId, fase: fase });
        const response = await fetch(`{{ route('admin.get_elemen_cp') }}?${params.toString()}`);
        const data = await response.json();

        elemenSelect.innerHTML = '<option value="">-- Pilih Elemen Pembelajaran --</option>';
        if (data && data.length > 0) {
            let matched = false;
            data.forEach(elemen => {
                const option = document.createElement('option');
                option.value = elemen;
                option.textContent = elemen;
                if (elemen === selectedElemen) {
                    option.selected = true;
                    matched = true;
                }
                elemenSelect.appendChild(option);
            });

            if (selectedElemen && !matched) {
                const customOption = document.createElement('option');
                customOption.value = selectedElemen;
                customOption.textContent = selectedElemen;
                customOption.selected = true;
                elemenSelect.appendChild(customOption);
            }
        } else {
            if (selectedElemen) {
                elemenSelect.innerHTML = `<option value="${selectedElemen}" selected>${selectedElemen}</option>`;
            } else {
                elemenSelect.innerHTML = '<option value="">-- Data CP Kosong --</option><option value="Umum">Umum</option>';
            }
        }
    } catch (error) {
        console.error('Gagal memuat elemen:', error);
        elemenSelect.innerHTML = '<option value="">Gagal memuat data elemen</option>';
    }
}

// Handle Open/Close Modal
async function openEditModal(item) {
    const form = document.getElementById('editForm');
    form.action = `/admin/tujuan-pembelajaran/${item.id}`;

    document.getElementById('edit_mapel_id').value = item.mapel_id;
    document.getElementById('edit_kelas_id').value = item.kelas_id ?? '';
    document.getElementById('edit_fase').value = item.fase ?? '';
    document.getElementById('edit_kode_tp').value = item.kode_tp ?? '';

    // Populate Dynamic Deskripsi TP
    const editContainer = document.getElementById('editTpContainer');
    editContainer.innerHTML = '';

    let deskripsiList = [];
    if (Array.isArray(item.deskripsi_tp)) {
        deskripsiList = item.deskripsi_tp;
    } else if (typeof item.deskripsi_tp === 'string') {
        try {
            deskripsiList = JSON.parse(item.deskripsi_tp);
        } catch (e) {
            deskripsiList = item.deskripsi_tp.split("\n");
        }
    }

    if (Array.isArray(deskripsiList) && deskripsiList.length > 0) {
        deskripsiList.forEach(tp => {
            if (tp.toString().trim() !== '') addEditTpRow(tp.toString().trim());
        });
    }

    if (editContainer.children.length === 0) {
        addEditTpRow();
    }

    await fetchEditElemenOptions(item.elemen);
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function handleModalClick(event) {
    if (event.target.id === 'editModal') {
        closeEditModal();
    }
}

// Event Listener onload
document.addEventListener("DOMContentLoaded", function() {
    const mapelVal = document.getElementById('mapel_id').value;
    const faseVal = document.getElementById('fase').value;
    if (mapelVal && faseVal) {
        fetchElemenOptions();
    }
});
</script>
@endsection