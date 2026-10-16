@extends('layouts.admin')

@section('title', 'Atur Sesi Ujian')
@section('page_title', 'Atur Sesi Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Sesi Ujian</h1>
            <p class="text-xs text-slate-500 mt-1">Atur jadwal, durasi, dan status pelaksanaan ujian CAT.</p>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="flat-card overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-medium text-slate-500">Filter Status:</span>
                <a href="{{ route('admin.sesi.index') }}" 
                    class="px-2.5 py-1 text-xs rounded-md font-medium transition {{ !request('status') ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                    Semua
                </a>
                <a href="{{ route('admin.sesi.index', ['status' => 'aktif']) }}" 
                    class="px-2.5 py-1 text-xs rounded-md font-medium transition {{ request('status') == 'aktif' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                    Aktif
                </a>
                <a href="{{ route('admin.sesi.index', ['status' => 'draft']) }}" 
                    class="px-2.5 py-1 text-xs rounded-md font-medium transition {{ request('status') == 'draft' ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                    Draft
                </a>
                <a href="{{ route('admin.sesi.index', ['status' => 'selesai']) }}" 
                    class="px-2.5 py-1 text-xs rounded-md font-medium transition {{ request('status') == 'selesai' ? 'bg-slate-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                    Selesai
                </a>
            </div>

            <div class="relative min-w-[200px]">
                <input type="text" placeholder="Cari nama ujian..." 
                    class="flat-input w-full pl-8 pr-3 py-1.5 text-xs">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
            </div>
        </div>

        <!-- Tabel Sesi Ujian -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3">Nama Ujian</th>
                        <th class="p-3">Mata Pelajaran</th>
                        <th class="p-3 text-center">Kelas</th>
                        <th class="p-3 text-center">Waktu Pelaksanaan</th>
                        <th class="p-3 text-center">Durasi</th>
                        <th class="p-3 text-center">Pengaturan</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($ujians as $index => $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3 text-center font-medium">{{ $ujians->firstItem() + $index }}</td>
                        <td class="p-3 font-semibold text-slate-900">{{ $item->nama_ujian }}</td>
                        <td class="p-3">{{ $item->mapel->nama_mapel ?? '-' }}</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded bg-slate-100 font-medium text-slate-800 border border-slate-200">
                                {{ $item->kelas->nama_kelas ?? 'Kelas ID: '.$item->kelas_id }}
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($item->waktu_mulai)->format('d M Y, H:i') }}</div>
                            <div class="text-[11px] text-slate-400">s/d {{ \Carbon\Carbon::parse($item->waktu_selesai)->format('d M Y, H:i') }}</div>
                        </td>
                        <td class="p-3 text-center font-medium">
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $item->durasi }} Menit
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center space-x-1 text-[11px]">
                                <span class="px-1.5 py-0.5 rounded {{ $item->acak_soal ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                    Soal: {{ $item->acak_soal ? 'Acak' : 'Urut' }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded {{ $item->acak_jawaban ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                    Opsi: {{ $item->acak_jawaban ? 'Acak' : 'Urut' }}
                                </span>
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            @if($item->status == 'aktif')
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-circle text-[8px] mr-1"></i> Aktif
                                </span>
                            @elseif($item->status == 'draft')
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                    Draft
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-200 text-slate-700">
                                    Selesai
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <button onclick="openModalEdit({{ json_encode($item) }})" 
                                    class="p-1.5 rounded text-amber-600 hover:bg-amber-50 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.sesi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi ujian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block"></i>
                            Belum ada sesi ujian yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if(method_exists($ujians, 'hasPages') && $ujians->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $ujians->links() }}
        </div>
        @endif
    </div>
</div>

<!-- MODAL TAMBAH / EDIT SESI UJIAN -->
<div id="modalSesi" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 id="modalTitle" class="font-bold text-sm">Tambah Sesi Ujian CAT</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form id="formSesi" action="{{ route('admin.sesi.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">
            <input type="hidden" id="sesi_id" name="id">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Ujian</label>
                <input type="text" name="nama_ujian" id="nama_ujian" required placeholder="Contoh: Penilaian Tengah Semester Ganjil" class="flat-input w-full p-2.5">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran</label>
                    <select name="mapel_id" id="mapel_id" required class="flat-input w-full p-2.5">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($mapels ?? [] as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas Target</label>
                    <select name="kelas_id" id="kelas_id" required class="flat-input w-full p-2.5">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelass ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Mulai</label>
                    <input type="datetime-local" name="waktu_mulai" id="waktu_mulai" required class="flat-input w-full p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Selesai</label>
                    <input type="datetime-local" name="waktu_selesai" id="waktu_selesai" required class="flat-input w-full p-2.5">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi (Menit)</label>
                    <input type="number" name="durasi" id="durasi" min="1" required placeholder="60" class="flat-input w-full p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Ujian</label>
                    <select name="status" id="status" class="flat-input w-full p-2.5">
                        <option value="draft">Draft</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-6 pt-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="acak_soal" id="acak_soal" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700">Acak Urutan Soal</span>
                </label>

                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="acak_jawaban" id="acak_jawaban" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700">Acak Opsi Jawaban</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">Simpan Sesi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambah() {
    document.getElementById('modalTitle').innerText = 'Tambah Sesi Ujian CAT';
    document.getElementById('formSesi').action = "{{ route('admin.sesi.store') }}";
    document.getElementById('methodField').value = 'POST';
    document.getElementById('formSesi').reset();
    document.getElementById('modalSesi').classList.remove('hidden');
}

function openModalEdit(data) {
    document.getElementById('modalTitle').innerText = 'Edit Sesi Ujian CAT';
    document.getElementById('formSesi').action = "{{ url('admin/sesi') }}/" + data.id;
    document.getElementById('methodField').value = 'PUT';
    
    document.getElementById('sesi_id').value = data.id;
    document.getElementById('nama_ujian').value = data.nama_ujian;
    document.getElementById('mapel_id').value = data.mapel_id;
    document.getElementById('kelas_id').value = data.kelas_id;
    document.getElementById('waktu_mulai').value = data.waktu_mulai.replace(' ', 'T').substring(0, 16);
    document.getElementById('waktu_selesai').value = data.waktu_selesai.replace(' ', 'T').substring(0, 16);
    document.getElementById('durasi').value = data.durasi;
    document.getElementById('status').value = data.status;
    document.getElementById('acak_soal').checked = data.acak_soal == 1;
    document.getElementById('acak_jawaban').checked = data.acak_jawaban == 1;

    document.getElementById('modalSesi').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalSesi').classList.add('hidden');
}
</script>
@endsection