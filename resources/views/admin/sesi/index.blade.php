@extends('layouts.admin')

@section('title', 'Atur Sesi Ujian')
@section('page_title', 'Atur Sesi Ujian')

@section('content')
    @php
        $daftarMapel = $mapels ?? $listMapel ?? collect();
        $daftarKelas = $kelass ?? $listKelas ?? $kelases ?? collect();
        $daftarKodeSoal = $listKodeSoal ?? (\Illuminate\Support\Facades\Schema::hasTable('soals') && \Illuminate\Support\Facades\Schema::hasColumn('soals', 'kode_soal')
            ? \App\Models\Soal::select('kode_soal', 'mapel_id', 'kelas_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_soal'))
                ->whereNotNull('kode_soal')
                ->where('kode_soal', '!=', '')
                ->groupBy('kode_soal', 'mapel_id', 'kelas_id')
                ->orderBy('kode_soal', 'asc')
                ->get()
            : collect());
    @endphp

    <div class="space-y-6">

        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Sesi Ujian</h1>
                <p class="text-xs text-slate-500 mt-1">Atur jadwal, paket kode soal, durasi, dan status pelaksanaan ujian CAT.</p>
            </div>

            <button type="button" onclick="openModalTambah()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition cursor-pointer shrink-0">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Sesi Ujian</span>
            </button>
        </div>

        <!-- Filter & Table Card -->
        <div class="flat-card overflow-hidden">

            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-medium text-slate-500 mr-1">Filter Status:</span>
                    <a href="{{ route('admin.sesi.index', array_filter(['search' => request('search')])) }}"
                        class="px-2.5 py-1 text-xs rounded-sm font-medium transition {{ !request('status') ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.sesi.index', array_filter(['status' => 'aktif', 'search' => request('search')])) }}"
                        class="px-2.5 py-1 text-xs rounded-sm font-medium transition {{ request('status') == 'aktif' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                        Aktif
                    </a>
                    <a href="{{ route('admin.sesi.index', array_filter(['status' => 'draft', 'search' => request('search')])) }}"
                        class="px-2.5 py-1 text-xs rounded-sm font-medium transition {{ request('status') == 'draft' ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                        Draft
                    </a>
                    <a href="{{ route('admin.sesi.index', array_filter(['status' => 'selesai', 'search' => request('search')])) }}"
                        class="px-2.5 py-1 text-xs rounded-sm font-medium transition {{ request('status') == 'selesai' ? 'bg-slate-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                        Selesai
                    </a>
                </div>

                <form action="{{ route('admin.sesi.index') }}" method="GET" class="relative min-w-[220px]">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama ujian / kode soal..."
                        class="flat-input w-full pl-8 pr-3 py-1.5 text-xs">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </form>
            </div>

            <!-- Tabel Sesi Ujian -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-3 text-center w-12">No</th>
                            <th class="p-3">Nama Ujian</th>
                            <th class="p-3">Mata Pelajaran &amp; Paket</th>
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
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3 text-center font-medium">
                                    {{ method_exists($ujians, 'firstItem') ? $ujians->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="p-3 font-semibold text-slate-900">
                                    {{ $item->nama_ujian }}
                                    @if(!empty($item->token))
                                        <div class="text-[10px] font-mono text-slate-500 mt-0.5">
                                            Token: <span class="font-bold text-slate-800">{{ $item->token }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="font-medium text-slate-800">{{ $item->mapel->nama_mapel ?? '-' }}</div>
                                    @if(!empty($item->kode_soal))
                                        <span class="inline-block mt-1 px-1.5 py-0.5 rounded-sm text-[10px] font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Kode: {{ $item->kode_soal }}
                                        </span>
                                    @else
                                        <span class="inline-block mt-1 text-[10px] text-slate-400 italic">
                                            Semua soal mapel
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded-sm bg-slate-100 font-medium text-slate-800 border border-slate-200">
                                        {{ $item->kelas->nama_kelas ?? ($item->kelas_id ? 'Kelas ID: ' . $item->kelas_id : 'Semua Kelas') }}
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="font-medium text-slate-800">
                                        {{ \Carbon\Carbon::parse($item->waktu_mulai)->format('d M Y, H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        s/d {{ \Carbon\Carbon::parse($item->waktu_selesai)->format('d M Y, H:i') }}
                                    </div>
                                </td>
                                <td class="p-3 text-center font-medium">
                                    <span class="px-2 py-0.5 rounded-sm bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $item->durasi }} Menit
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center space-x-1 text-[11px]">
                                        <span class="px-1.5 py-0.5 rounded-sm border {{ $item->acak_soal ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                            Soal: {{ $item->acak_soal ? 'Acak' : 'Urut' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded-sm border {{ $item->acak_jawaban ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                            Opsi: {{ $item->acak_jawaban ? 'Acak' : 'Urut' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    @if($item->status == 'aktif')
                                        <span class="px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <i class="fa-solid fa-circle text-[7px] mr-1"></i> Aktif
                                        </span>
                                    @elseif($item->status == 'draft')
                                        <span class="px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300">
                                            Draft
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase bg-slate-200 text-slate-700 border border-slate-300">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <button type="button" onclick='openModalEdit(@json($item))'
                                            class="p-1.5 rounded-sm text-amber-600 hover:bg-amber-50 border border-transparent hover:border-amber-200 transition cursor-pointer"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('admin.sesi.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi ujian ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-sm text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition cursor-pointer"
                                                title="Hapus">
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

    <!-- MODAL TAMBAH / EDIT SESI UJIAN (FLAT) -->
    <div id="modalSesi"
        class="fixed inset-0 z-50 bg-slate-950/65 hidden flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-md max-w-lg w-full overflow-hidden border border-slate-300">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <h3 id="modalTitle" class="font-bold text-sm">Tambah Sesi Ujian CAT</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-white cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formSesi" action="{{ route('admin.sesi.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="POST">
                <input type="hidden" id="sesi_id" name="id">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Ujian</label>
                    <input type="text" name="nama_ujian" id="nama_ujian" required
                        placeholder="Contoh: Penilaian Tengah Semester Ganjil" class="flat-input w-full p-2.5">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran</label>
                        <select name="mapel_id" id="mapel_id" required onchange="filterKodeSoalByMapel()"
                            class="flat-input w-full p-2.5">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($daftarMapel as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas Target</label>
                        <select name="kelas_id" id="kelas_id" required class="flat-input w-full p-2.5">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Pilihan Paket Kode Soal Spesifik -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Paket / Kode Soal</label>
                    <select name="kode_soal" id="kode_soal" class="flat-input w-full p-2.5">
                        <option value="">-- Semua Soal pada Mapel Ini --</option>
                        @foreach($daftarKodeSoal as $paket)
                            <option value="{{ $paket->kode_soal }}" data-mapel-id="{{ $paket->mapel_id }}">
                                {{ $paket->kode_soal }} ({{ $paket->total_soal }} Soal)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Pilih kode soal spesifik agar paket soal baru pada mata pelajaran yang sama tidak ikut tergabung.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Mulai</label>
                        <input type="datetime-local" name="waktu_mulai" id="waktu_mulai" required
                            class="flat-input w-full p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Selesai</label>
                        <input type="datetime-local" name="waktu_selesai" id="waktu_selesai" required
                            class="flat-input w-full p-2.5">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi (Menit)</label>
                        <input type="number" name="durasi" id="durasi" min="1" required placeholder="60"
                            class="flat-input w-full p-2.5">
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
                        <input type="checkbox" name="acak_soal" id="acak_soal" value="1"
                            class="rounded-sm border-slate-300 text-blue-600 focus:ring-0">
                        <span class="text-xs font-medium text-slate-700">Acak Urutan Soal</span>
                    </label>

                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="acak_jawaban" id="acak_jawaban" value="1"
                            class="rounded-sm border-slate-300 text-blue-600 focus:ring-0">
                        <span class="text-xs font-medium text-slate-700">Acak Opsi Jawaban</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 rounded-sm bg-slate-100 border border-slate-300 hover:bg-slate-200 text-slate-700 text-xs font-semibold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-sm bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold cursor-pointer">
                        Simpan Sesi
                    </button>
                </div>

                <div class="text-[10px] flex items-center justify-between text-slate-400">
                    <div>&copy; {{ date('Y') }} Copyright: MahesAPP. All rights reserved.</div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterKodeSoalByMapel(selectedKode = null) {
            const mapelId = document.getElementById('mapel_id').value;
            const selectKode = document.getElementById('kode_soal');
            const options = selectKode.querySelectorAll('option[data-mapel-id]');

            options.forEach(opt => {
                if (!mapelId || opt.getAttribute('data-mapel-id') === String(mapelId)) {
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });

            if (selectedKode !== null) {
                selectKode.value = selectedKode || '';
            } else {
                const currentOpt = selectKode.options[selectKode.selectedIndex];
                if (currentOpt && currentOpt.disabled) {
                    selectKode.value = '';
                }
            }
        }

        function openModalTambah() {
            document.getElementById('modalTitle').innerText = 'Tambah Sesi Ujian CAT';
            document.getElementById('formSesi').action = "{{ route('admin.sesi.store') }}";
            document.getElementById('methodField').value = 'POST';
            document.getElementById('formSesi').reset();
            filterKodeSoalByMapel('');
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
            filterKodeSoalByMapel(data.kode_soal ?? '');
            document.getElementById('waktu_mulai').value = data.waktu_mulai ? data.waktu_mulai.replace(' ', 'T').substring(0, 16) : '';
            document.getElementById('waktu_selesai').value = data.waktu_selesai ? data.waktu_selesai.replace(' ', 'T').substring(0, 16) : '';
            document.getElementById('durasi').value = data.durasi;
            document.getElementById('status').value = data.status;
            document.getElementById('acak_soal').checked = data.acak_soal == 1 || data.acak_soal === true;
            document.getElementById('acak_jawaban').checked = data.acak_jawaban == 1 || data.acak_jawaban === true;

            document.getElementById('modalSesi').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modalSesi').classList.add('hidden');
        }
    </script>
@endsection