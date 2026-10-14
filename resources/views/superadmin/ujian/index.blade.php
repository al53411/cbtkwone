@extends('layouts.superadmin')

@section('title', 'Pengaturan & Jadwal Ujian')
@section('header', 'Pengaturan & Jadwal Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Pengaturan & Jadwal Ujian</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola jadwal pelaksanaan, token, durasi, dan konfigurasi ujian CAT.</p>
        </div>
        <div>
            <button type="button" onclick="openModalCreate()" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm transition space-x-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Jadwal Ujian</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center justify-between text-xs font-medium">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl flex items-center justify-between text-xs font-medium">
            <span>{{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">✕</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs space-y-1">
            <div class="font-bold mb-1">Terdapat kesalahan pada pengisian form:</div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                @foreach ($errors->all() as$error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Container Card Tabel -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- Filter & Search Bar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row gap-3 items-center justify-between">
            <form action="{{ route('superadmin.ujian.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ujian..." 
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <select name="kelas_id" onchange="this.form.submit()" class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Kelas --</option>
                    @if(isset($kelases))
                        @foreach ($kelases as$kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    @endif
                </select>

                <select name="status" onchange="this.form.submit()" class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Status --</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                @if(request('search') || request('kelas_id') || request('status'))
                    <a href="{{ route('superadmin.ujian.index') }}" class="px-3 py-2 bg-slate-200 text-slate-600 rounded-xl text-xs font-medium transition text-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Data Ujian -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12">#</th>
                        <th class="py-3.5 px-4">Nama Ujian</th>
                        <th class="py-3.5 px-4">Kelas & Mapel</th>
                        <th class="py-3.5 px-4">Durasi & Waktu</th>
                        <th class="py-3.5 px-4 text-center">Token</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if(isset($ujians) && count($ujians) > 0)
                        @foreach ($ujians as $index =>$item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium">
                                    {{ $ujians->firstItem() +$index }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $item->nama_ujian }}
                                </td>
                                <td class="py-3.5 px-4 space-y-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold text-[10px]">
                                        {{ $item->kelas->nama_kelas ?? 'Semua Kelas' }}
                                    </span>
                                    <div class="text-slate-500 font-medium text-[11px]">
                                        {{ $item->mapel->nama_mapel ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 space-y-1">
                                    <div class="text-slate-700 font-medium">
                                        {{ $item->durasi }} Menit
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($item->waktu_mulai)->format('d M Y, H:i') }} - {{ \Carbon\Carbon::parse($item->waktu_selesai)->format('H:i') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-600">
                                    <span class="px-2 py-1 bg-emerald-50 rounded border border-emerald-200 tracking-widest">
                                        {{ $item->token }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($item->status === 'aktif')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @elseif($item->status === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200">
                                            Draft
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button" 
                                            data-ujian="{{ e(json_encode($item)) }}"
                                            onclick="editUjianFromBtn(this)"
                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                            title="Edit Ujian">
                                            Edit
                                        </button>
                                        <button type="button" onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->nama_ujian) }}')"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Hapus Ujian">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Belum ada data jadwal ujian.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($ujians) &&$ujians->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $ujians->links() }}
            </div>
        @endif

    </div>
</div>

<!-- MODAL FORM INPUT (TAMBAH / EDIT UJIAN) -->
<div id="modalUjian" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modalTitle" class="text-sm font-bold text-slate-800">Tambah Jadwal Ujian</h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition">✕</button>
        </div>

        <!-- Form Input -->
        <form id="formUjian" action="{{ route('superadmin.ujian.store') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <input type="hidden" id="ujian_id" name="id">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Ujian <span class="text-rose-500">*</span></label>
                <input type="text" id="nama_ujian" name="nama_ujian" value="{{ old('nama_ujian') }}" required placeholder="Contoh: Penilaian Akhir Semester Matematika"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kelas <span class="text-rose-500">*</span></label>
                    <select id="kelas_id" name="kelas_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <option value="">-- Pilih Kelas --</option>
                        @if(isset($kelases))
                            @foreach ($kelases as$kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select id="mapel_id" name="mapel_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <option value="">-- Pilih Mapel --</option>
                        @if(isset($mapels))
                            @foreach ($mapels as$mapel)
                                <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Durasi (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" id="durasi" name="durasi" value="{{ old('durasi') }}" required min="1" placeholder="90"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Token Ujian <span class="text-rose-500">*</span></label>
                    <div class="flex space-x-2">
                        <input type="text" id="token" name="token" value="{{ old('token') }}" required maxlength="6" placeholder="AX69K2"
                            oninput="this.value = this.value.toUpperCase()"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono uppercase focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <button type="button" onclick="generateRandomToken()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl transition" title="Acak Token">
                            Acak
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Waktu Mulai <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Waktu Selesai <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Status Ujian <span class="text-rose-500">*</span></label>
                <select id="status" name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ old('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Hapus Hidden -->
<form id="formDelete" action="" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    const modal = document.getElementById('modalUjian');
    const form = document.getElementById('formUjian');
    const modalTitle = document.getElementById('modalTitle');
    const formMethod = document.getElementById('formMethod');

    function openModalCreate() {
        form.reset();
        form.action = "{{ route('superadmin.ujian.store') }}";
        formMethod.value = "POST";
        document.getElementById('ujian_id').value = "";
        modalTitle.innerText = "Tambah Jadwal Ujian";
        generateRandomToken();
        modal.classList.remove('hidden');
    }

    function editUjianFromBtn(button) {
        const rawData = button.getAttribute('data-ujian');
        if (rawData) {
            const data = JSON.parse(rawData);
            editUjian(data);
        }
    }

    function editUjian(data) {
        form.reset();
        form.action = `/superadmin/ujian/${data.id}`;
        formMethod.value = "PUT";
        modalTitle.innerText = "Edit Jadwal Ujian";

        document.getElementById('ujian_id').value = data.id || '';
        document.getElementById('nama_ujian').value = data.nama_ujian || '';
        document.getElementById('kelas_id').value = data.kelas_id || '';
        document.getElementById('mapel_id').value = data.mapel_id || '';
        document.getElementById('durasi').value = data.durasi || '';
        document.getElementById('token').value = data.token || '';
        document.getElementById('status').value = data.status || 'draft';

        if (data.waktu_mulai) {
            document.getElementById('waktu_mulai').value = data.waktu_mulai.replace(' ', 'T').substring(0, 16);
        }
        if (data.waktu_selesai) {
            document.getElementById('waktu_selesai').value = data.waktu_selesai.replace(' ', 'T').substring(0, 16);
        }

        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function generateRandomToken() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 6; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('token').value = result;
    }

    function confirmDelete(id, nama) {
        if (confirm(`Apakah Anda yakin ingin menghapus jadwal ujian "${nama}"?`)) {
            const formDelete = document.getElementById('formDelete');
            formDelete.action = `/superadmin/ujian/${id}`;
            formDelete.submit();
        }
    }
</script>
@endsection