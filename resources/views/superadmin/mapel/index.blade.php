@extends('layouts.superadmin')

@section('title', 'Kelola Mata Pelajaran')
@section('header', 'Mata Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- HEADER & ACTION BUTTON -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Master Data Mata Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola daftar mata pelajaran yang terdaftar pada sistem CAT/CBT.</p>
        </div>
        <button onclick="openModal('modal-mapel')"
            class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-emerald-600/20 transition duration-200">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Tambah Mapel Baru</span>
        </button>
    </div>

    <!-- STATISTIK RINGKAS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-book"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Mapel</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $mapels->total() ?? count($mapels ?? []) }}</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status Aktif</p>
                <h3 class="text-lg font-bold text-slate-800">
                    {{ isset($mapels) ?$mapels->where('status', 'aktif')->count() : 0 }}
                </h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Bank Soal</p>
                <h3 class="text-lg font-bold text-slate-800">--</h3>
            </div>
        </div>
    </div>

    <!-- CARD TABEL MATA PELAJARAN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- SEARCH & FILTER BAR -->
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" action="{{ route('superadmin.mapel.index') }}" class="relative flex-1 max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode atau nama mata pelajaran..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </form>

            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-500 font-medium hidden sm:inline">Urutkan:</span>
                <select class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:border-emerald-500">
                    <option value="terbaru">Terbaru</option>
                    <option value="nama">Nama (A-Z)</option>
                    <option value="kode">Kode Mapel</option>
                </select>
            </div>
        </div>

        <!-- TABLE DATA -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Kode Mapel</th>
                        <th class="py-3.5 px-4">Nama Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Kelompok</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($mapels as $index =>$item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-medium">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-indigo-600">
                                {{ $item->kode_mapel ?? 'MP-' . sprintf('\%03d', $item->id) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $item->nama_mapel ?? $item->nama }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 font-medium text-[11px]">
                                    {{ $item->kelompok ?? 'Wajib' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(($item->status ?? 'aktif') === 'aktif')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="editMapel({{ json_encode($item) }})"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>
                                    <button onclick="confirmDelete({{ $item->id }}, '{{ $item->nama_mapel ?? $item->nama }}')"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                        title="Hapus">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-book-open text-xl"></i>
                                    </div>
                                    <p class="text-xs font-medium">Belum ada data mata pelajaran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if(method_exists($mapels, 'links'))
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $mapels->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL TAMBAH / EDIT MATA PELAJARAN        -->
<!-- ========================================== -->
<div id="modal-mapel" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeModal('modal-mapel')"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 id="modal-title" class="text-base font-bold text-slate-900">Tambah Mata Pelajaran</h3>
                <button onclick="closeModal('modal-mapel')" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form Modal -->
            <form id="form-mapel" action="{{ route('superadmin.mapel.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" id="method-field" name="_method" value="POST">
                <input type="hidden" id="mapel-id" name="id">

                <!-- Kode Mapel -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Mapel</label>
                    <input type="text" id="kode_mapel" name="kode_mapel" placeholder="Contoh: MTK-X" required
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 uppercase focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Nama Mapel -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Mata Pelajaran</label>
                    <input type="text" id="nama_mapel" name="nama_mapel" placeholder="Contoh: Matematika Wajib" required
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Kelompok Mapel -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelompok / Kategori</label>
                    <select id="kelompok" name="kelompok"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500">
                        <option value="Wajib">Kelompok Wajib</option>
                        <option value="Pilihan">Kelompok Pilihan / Pematan</option>
                        <option value="Muatan Lokal">Muatan Lokal (Mulok)</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Aktivasi</label>
                    <select id="status" name="status"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <!-- Footer Modal / Action -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-mapel')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form tersembunyi untuk Hapus Data -->
<form id="form-delete" action="" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<!-- SCRIPT PENDUKUNG -->
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById('modal-title').innerText = 'Tambah Mata Pelajaran';
        document.getElementById('form-mapel').action = "{{ route('superadmin.mapel.store') }}";
        document.getElementById('method-field').value = 'POST';
        document.getElementById('form-mapel').reset();
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function editMapel(data) {
        document.getElementById('modal-title').innerText = 'Edit Mata Pelajaran';
        document.getElementById('form-mapel').action = `/superadmin/mapel/${data.id}`;
        document.getElementById('method-field').value = 'PUT';
        
        document.getElementById('mapel-id').value = data.id;
        document.getElementById('kode_mapel').value = data.kode_mapel || '';
        document.getElementById('nama_mapel').value = data.nama_mapel || data.nama || '';
        document.getElementById('kelompok').value = data.kelompok || 'Wajib';
        document.getElementById('status').value = data.status || 'aktif';

        document.getElementById('modal-mapel').classList.remove('hidden');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Mata Pelajaran?',
            text: `Anda yakin ingin menghapus "${name}"? Data yang terhapus tidak dapat dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-delete');
                form.action = `/superadmin/mapel/${id}`;
                form.submit();
            }
        });
    }
</script>
@endsection