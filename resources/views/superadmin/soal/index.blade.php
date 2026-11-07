@extends('layouts.superadmin')

@section('title', 'Master Bank Soal')
@section('header', 'Master Bank Soal')

@section('content')
<div class="w-full space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Master Bank Soal</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola bank soal ujian, filter berdasarkan mata pelajaran dan tingkat kelas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Pemicu Modal Upload Gambar Soal -->
            <button type="button" onclick="document.getElementById('modal-upload-gambar').classList.remove('hidden')"
                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 border border-blue-300 transition duration-150">
                <i class="fa-solid fa-image text-sm"></i>
                <span>Upload Gambar Soal</span>
            </button>

            <a href="{{ route('superadmin.soal.import') }}"
                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 transition duration-150">
                <i class="fa-solid fa-file-excel text-sm"></i>
                <span>Impor Soal (Excel/Word)</span>
            </a>
            <a href="{{ route('superadmin.soal.create') }}"
                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition duration-150">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Buat Bank Soal</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Paket Soal</p>
                <h3 class="text-lg font-bold text-slate-800">{{ number_format($totalPaket ?? 0) }} Paket</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Soal Pilihan Ganda</p>
                <h3 class="text-lg font-bold text-slate-800">{{ number_format($totalPg ?? 0) }} Butir</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Soal Esai / Uraian</p>
                <h3 class="text-lg font-bold text-slate-800">{{ number_format($totalEsai ?? 0) }} Butir</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-book"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mata Pelajaran Aktif</p>
                <h3 class="text-lg font-bold text-slate-800">{{ number_format($totalMapel ?? 0) }} Mapel</h3>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <form method="GET" action="{{ route('superadmin.soal.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Filter Mata Pelajaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Mata Pelajaran</label>
                <select name="mapel_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach ($mapels as $mapel)
                        <option value="{{ $mapel->id }}" @selected(request('mapel_id') == $mapel->id)>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tingkat Kelas</label>
                <select name="kelas_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition">
                    <option value="">-- Semua Kelas --</option>
                    @foreach ($kelass as $kelas)
                        <option value="{{ $kelas->id }}" @selected(request('kelas_id') == $kelas->id)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Keyword -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cari Kode / Pertanyaan Soal</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari isi soal atau kode..."
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 pl-9 pr-3 py-2.5 text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
            </div>

            <!-- Action Button Filter -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-slate-800 transition">
                    <i class="fa-solid fa-filter mr-1.5"></i> Terapkan Filter
                </button>
                <a href="{{ route('superadmin.soal.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- Tabel Master Bank Soal -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Paket Bank Soal</h2>
            <span class="text-xs text-slate-500">
                Menampilkan <b>{{ $soals->firstItem() ?? 0 }}</b> - <b>{{ $soals->lastItem() ?? 0 }}</b> dari <b>{{ $soals->total() }}</b> paket
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Kode & Judul / Pertanyaan Soal</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4 text-center">Tipe & Opsi</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    
                    @forelse ($soals as $index => $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-4 text-center font-bold text-slate-400">
                                {{ $soals->firstItem() + $index }}
                            </td>
                            
                            <td class="py-4 px-4">
                                <span class="font-bold text-indigo-600 text-xs block mb-0.5">
                                    {{ $item->kode_soal ?? 'SOAL-' . $item->id }}
                                </span>
                                <span class="text-slate-600 text-xs line-clamp-1">
                                    {{ $item->soal }}
                                </span>
                            </td>

                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-book text-[10px] mr-1.5"></i> {{ $item->mapel->nama_mapel ?? '-' }}
                                </span>
                            </td>

                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $item->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span class="uppercase font-bold px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[10px]">
                                    {{ $item->tipe ?? 'pg' }}
                                </span>
                                <span class="text-slate-500 text-[11px] block mt-0.5">
                                    ({{ $item->jumlah_opsi ?? 5 }} Opsi)
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center space-x-1">
                                <a href="{{ route('superadmin.soal.edit', $item->id) }}" class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition inline-block" title="Edit Soal">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <button type="button" onclick="confirmDelete({{ $item->id }})" class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition inline-block" title="Hapus Soal">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                                <!-- Form Hapus Hidden -->
                                <form id="delete-form-{{ $item->id }}" action="{{ route('superadmin.soal.destroy', $item->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                                Belum ada data soal yang ditemukan.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Footer Pagination -->
        <div class="p-4 border-t border-slate-100">
            {{ $soals->withQueryString()->links() }}
        </div>

    </div>

</div>

<!-- MODAL UPLOAD GAMBAR SOAL DENGAN BACKDROP BLUR -->
<div id="modal-upload-gambar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2 text-blue-600 font-bold text-sm sm:text-base">
                <i class="fa-solid fa-image text-lg"></i>
                <span>Upload Gambar Soal Terpisah</span>
            </div>
            <button type="button" onclick="document.getElementById('modal-upload-gambar').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Modal Body & Form -->
        <form action="{{ route('superadmin.soal.process-upload-gambar') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <!-- DROPDOWN KODE PAKET SOAL -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Paket Soal <span class="text-rose-500">*</span></label>
                <select name="kode_soal" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Pilih Kode Paket Soal --</option>
                    @if(isset($kodeSoals))
                        @foreach($kodeSoals as $kode)
                            <option value="{{ $kode }}" {{ old('kode_soal') == $kode ? 'selected' : '' }}>
                                {{ $kode }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Pilih kode soal yang tersedia di database.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas_id" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelass as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Soal <span class="text-rose-500">*</span></label>
                    <input type="number" name="nomor_soal" min="1" required placeholder="Contoh: 1, 2, 3..."
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 border focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">File Gambar Soal <span class="text-rose-500">*</span></label>
                <input type="file" name="gambar" required accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50 p-2">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maksimal 2MB).</p>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-upload-gambar').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-xl transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-upload text-xs"></i>
                    <span>Upload Gambar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Soal?',
        text: "Data soal ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-2xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endsection