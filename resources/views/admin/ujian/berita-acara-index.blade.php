@extends('layouts.admin')

@section('title', 'Berita Acara Ujian CAT')
@section('page_title', 'Berita Acara Ujian CAT')

@section('content')
    <div class="max-w-6xl mx-auto space-y-5">

        <!-- Alert Notifikasi Upload / Hapus -->
        @if(session('success'))
            <div
                class="p-3.5 bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div
                class="p-3.5 bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Header & Filter Sesi Ujian -->
        <div class="flat-card p-5 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-signature"></i>
                    </span>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">
                            Berita Acara &amp; Administrasi Ujian CAT
                        </h1>
                        <p class="text-xs text-slate-500">
                            Cetak Berita Acara pelaksanaan ujian, Rekap Hasil CAT resmi, dan unggah arsip scan bertanda
                            tangan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Filter Kelas & Pencarian -->
            <form method="GET" action="{{ route('admin.ujian.berita-acara-index') }}"
                class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full lg:w-auto">
                <select name="kelas_id" class="flat-input px-3 py-2 text-xs font-semibold w-full sm:w-44">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($listKelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ujian / mapel..."
                    class="flat-input px-3.5 py-2 text-xs w-full sm:w-52">

                <button type="submit"
                    class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-md text-xs font-bold inline-flex items-center justify-center gap-1.5 transition cursor-pointer shrink-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Filter</span>
                </button>

                @if(request()->filled('search') || request()->filled('kelas_id'))
                    <a href="{{ route('admin.ujian.berita-acara-index') }}"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md text-xs font-semibold transition shrink-0">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Info Proktor & Pengawas Aktif dari Profil Sekolah -->
        @php
            $userAuth = Auth::user();
            $sekolahUser = $userAuth?->sekolah_id
                ? \App\Models\Sekolah::find($userAuth->sekolah_id)
                : \App\Models\Sekolah::first();
        @endphp
        <div
            class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-blue-900">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-blue-600"></i>
                    <span>
                        Proktor:
                        <strong>{{ $sekolahUser->nama_proktor ?? 'Belum diatur' }}</strong>
                        @if(!empty($sekolahUser->nip_proktor))
                            <span class="font-mono text-blue-700">(NIP. {{ $sekolahUser->nip_proktor }})</span>
                        @endif
                    </span>
                </span>
                <span class="hidden sm:inline text-blue-300">|</span>
                <span>
                    Pengawas:
                    <strong>{{ $sekolahUser->nama_pengawas ?? 'Belum diatur' }}</strong>
                    @if(!empty($sekolahUser->nip_pengawas))
                        <span class="font-mono text-blue-700">(NIP. {{ $sekolahUser->nip_pengawas }})</span>
                    @endif
                </span>
            </div>
            <a href="{{ route('admin.sekolah.index') }}"
                class="text-blue-700 hover:text-blue-900 font-bold underline inline-flex items-center gap-1 self-start sm:self-auto shrink-0">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Ubah Data Petugas</span>
            </a>
        </div>

        <!-- Tabel Daftar Sesi Ujian -->
        <div class="flat-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Ujian &amp; Mata Pelajaran</th>
                            <th class="py-3.5 px-4">Kelas / Rombel</th>
                            <th class="py-3.5 px-4">Jadwal Pelaksanaan</th>
                            <th class="py-3.5 px-4 text-center">Peserta Hadir</th>
                            <th class="py-3.5 px-4 text-center">Arsip Scan BA</th>
                            <th class="py-3.5 px-4 text-right">Cetak &amp; Upload Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ujians as $index => $ujian)
                            <tr class="hover:bg-slate-50/90 transition">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                    {{ $ujians->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ $ujian->nama_ujian }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] text-blue-700 font-semibold">
                                            <i class="fa-solid fa-book-open mr-1"></i>{{ $ujian->mapel->nama_mapel ?? '-' }}
                                        </span>
                                        @php
                                            $badgeClass = match ($ujian->status) {
                                                'aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'selesai' => 'bg-slate-200 text-slate-700 border-slate-300',
                                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                                            };
                                        @endphp
                                        <span
                                            class="px-2 py-0.2 rounded border text-[9.5px] font-extrabold uppercase {{ $badgeClass }}">
                                            {{ $ujian->status }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700">
                                    {{ $ujian->kelas->nama_kelas ?? 'Semua Kelas' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-mono text-[11px]">
                                    @if(!empty($ujian->waktu_mulai))
                                        {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d/m/Y H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 font-mono font-bold text-[11px]">
                                        {{ $ujian->nilai_ujians_count ?? 0 }} Siswa
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if(!empty($ujian->file_berita_acara))
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ asset('storage/' . $ujian->file_berita_acara) }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-md text-[11px] font-bold transition">
                                                <i class="fa-solid fa-file-pdf"></i>
                                                <span>Lihat Arsip</span>
                                            </a>
                                            <form action="{{ route('admin.ujian.hapus-berita-acara', $ujian->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus file arsip scan Berita Acara ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Arsip"
                                                    class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-md transition cursor-pointer">
                                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Belum diunggah</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                        <!-- Tombol Cetak Berita Acara -->
                                        <a href="{{ route('admin.ujian.berita-acara', $ujian->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-md text-xs font-bold shadow-2xs transition">
                                            <i class="fa-solid fa-file-signature"></i>
                                            <span>Berita Acara</span>
                                        </a>

                                        <!-- Tombol Cetak Rekap Hasil CAT -->
                                        <a href="{{ route('admin.ujian.cetak-hasil', $ujian->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-md text-xs font-bold shadow-2xs transition">
                                            <i class="fa-solid fa-print"></i>
                                            <span>Hasil CAT</span>
                                        </a>

                                        <!-- Tombol Upload Scan Berita Acara -->
                                        <button type="button"
                                            onclick="bukaModalUploadBa('{{ route('admin.ujian.upload-berita-acara', $ujian->id) }}', @js($ujian->nama_ujian))"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-bold shadow-2xs transition cursor-pointer">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                            <span>{{ !empty($ujian->file_berita_acara) ? 'Ganti Scan' : 'Upload BA' }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <i class="fa-solid fa-folder-open text-2xl text-slate-300 mb-2 block"></i>
                                    <p class="font-semibold text-slate-600">Belum ada sesi ujian yang tersedia.</p>
                                    <p class="text-[11px] mt-0.5">Silakan buat sesi ujian terlebih dahulu pada menu Atur Sesi
                                        Ujian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ujians->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/60">
                    {{ $ujians->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL UPLOAD SCAN BERITA ACARA -->
    <div id="modal-upload-ba" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="flat-card bg-white w-full max-w-md overflow-hidden shadow-lg">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Upload Scan Berita Acara</h3>
                    <p id="modal-nama-ujian" class="text-[11px] text-slate-500 mt-0.5">-</p>
                </div>
                <button type="button" onclick="tutupModalUploadBa()"
                    class="text-slate-400 hover:text-slate-700 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form id="form-upload-ba" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        File Scan Berita Acara (PDF / JPG / PNG)
                    </label>
                    <input type="file" name="file_berita_acara" accept=".pdf,.jpg,.jpeg,.png" required
                        class="flat-input w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-white hover:file:bg-slate-900 p-1.5">
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        * Unggah dokumen Berita Acara yang telah ditandatangani &amp; distempel basah (Maks. 5 MB).
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                    <button type="button" onclick="tutupModalUploadBa()"
                        class="px-4 py-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        <span>Simpan Arsip</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalUploadBa(actionUrl, namaUjian) {
            const modal = document.getElementById('modal-upload-ba');
            const form = document.getElementById('form-upload-ba');
            const label = document.getElementById('modal-nama-ujian');

            form.action = actionUrl;
            label.textContent = 'Sesi Ujian: ' + namaUjian;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupModalUploadBa() {
            const modal = document.getElementById('modal-upload-ba');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection