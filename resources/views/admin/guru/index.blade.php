@extends('layouts.admin')

@section('title', 'Data Guru')
@section('page_title', 'Guru')

@section('content')
    <!-- Header & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Data Guru</h1>
            <p class="text-gray-500 text-sm mt-1">
                Kelola seluruh data guru aktif di
                {{ Auth::user()?->sekolah?->nama_sekolah ?? $profilSekolah?->nama_sekolah ?? 'Sekolah' }}.
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Tombol Buka Modal Import Guru -->
            <button type="button" onclick="document.getElementById('modal-import-guru').classList.remove('hidden')"
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm transition space-x-2">
                <i class="fa-solid fa-file-import text-xs"></i>
                <span>Import Excel</span>
            </button>

            <!-- Tombol Tambah Guru -->
            <a href="{{ route('admin.guru.create') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition space-x-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Guru</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Flash Session -->
    @if(session('success'))
        <div
            class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-circle-exclamation text-red-600 text-lg"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Bar Pencarian & Filter -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('admin.guru.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari berdasarkan nama, NIP, atau NIK..."
                    class="w-full pl-9 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="w-full md:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="w-full border rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="PNS" {{ request('status') == 'PNS' ? 'selected' : '' }}>PNS</option>
                    <option value="PPPK" {{ request('status') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    <option value="GTT" {{ request('status') == 'GTT' ? 'selected' : '' }}>GTT / Honorer</option>
                </select>
            </div>

            <a href="{{ route('admin.guru.template') }}"
                class="inline-flex items-center justify-center gap-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-2 rounded-lg transition border border-slate-300">
                <i class="fa-solid fa-download text-xs text-slate-500"></i>
                <span>Download Template</span>
            </a>
        </form>
    </div>

    <!-- CONTAINER UTAMA DATA GURU -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">

        <!-- 📱 TAMPILAN MOBILE (CARD VIEW) -->
        <div class="block sm:hidden divide-y divide-gray-200">
            @forelse($gurus as $guru)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">{{ $guru->nama_lengkap }}</h3>
                            <p class="text-xs text-gray-500">L/P: {{ $guru->jenis_kelamin ?? '-' }} • Pend:
                                {{ $guru->pendidikan_terakhir ?? '-' }}</p>
                        </div>
                        @php
                            $badgeColor = match (strtoupper($guru->status_kepegawaian ?? '')) {
                                'PNS' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'PPPK' => 'bg-blue-50 text-blue-700 border-blue-200',
                                default => 'bg-amber-50 text-amber-700 border-amber-200',
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-semibold rounded border {{ $badgeColor }}">
                            {{ $guru->status_kepegawaian ?? 'GTT' }}
                        </span>
                    </div>

                    <div class="bg-gray-50 p-2.5 rounded-lg text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">NIP:</span>
                            <span class="font-semibold text-gray-800">{{ $guru->nip ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">NIK:</span>
                            <span class="font-semibold text-gray-800">{{ $guru->nik ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t">
                        <!-- Tombol Detail Mobile -->
                        <button type="button" onclick="showDetailGuru({{ json_encode($guru) }})"
                            class="px-3 py-1.5 bg-blue-50 border border-blue-300 text-blue-700 rounded-lg text-xs font-medium flex items-center gap-1">
                            <i class="fa-solid fa-eye"></i> Detail
                        </button>
                        <a href="{{ route('admin.guru.edit', $guru->id) }}"
                            class="px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-700 rounded-lg text-xs font-medium flex items-center gap-1">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form action="{{ route('admin.guru.destroy', $guru->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru {{ $guru->nama_lengkap }}?')"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-3 py-1.5 bg-red-50 border border-red-300 text-red-700 rounded-lg text-xs font-medium flex items-center gap-1">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fa-solid fa-user-slash text-3xl mb-2"></i>
                    <p class="text-sm">Belum ada data guru yang terdaftar.</p>
                </div>
            @endforelse
        </div>

        <!-- 💻 TAMPILAN DESKTOP (TABLE VIEW) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center w-12">
                            No</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Nama & Kontak
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Identitas (NIP /
                            NIK)</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Kepegawaian</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Tugas / Wali
                            Kelas</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center w-36">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($gurus as $guru)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-4 text-sm text-gray-500 text-center font-medium">
                                {{ method_exists($gurus, 'firstItem') ? $gurus->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <div class="font-bold text-gray-900">{{ $guru->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500 mt-0.5 space-x-1">
                                    <span>JK: {{ $guru->jenis_kelamin ?? '-' }}</span>
                                    <span>•</span>
                                    <span>Pend: {{ $guru->pendidikan_terakhir ?? '-' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <div class="font-semibold text-gray-800">NIP: {{ $guru->nip ?? '-' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">NIK: {{ $guru->nik ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center space-x-2">
                                    @php
                                        $badgeColor = match (strtoupper($guru->status_kepegawaian ?? '')) {
                                            'PNS' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'PPPK' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded border {{ $badgeColor }}">
                                        {{ $guru->status_kepegawaian ?? 'GTT/Honorer' }}
                                    </span>

                                    @if($guru->golongan)
                                        <span
                                            class="px-2 py-0.5 bg-gray-100 border border-gray-300 text-gray-700 text-xs font-bold rounded">
                                            {{ $guru->golongan }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                @if($guru->tipe_penugasan === 'guru_mapel')
                                    <span
                                        class="px-2.5 py-1 bg-blue-50 border border-blue-200 text-blue-700 font-semibold rounded-md text-xs inline-flex items-center gap-1">
                                        <i class="fa-solid fa-book-open text-blue-600"></i> Guru Mapel
                                    </span>
                                @elseif($guru->tipe_penugasan === 'wali_kelas')
                                    <div class="flex flex-wrap gap-1">
                                        @if(isset($guru->assigned_kelas) && count($guru->assigned_kelas) > 0)
                                            @foreach($guru->assigned_kelas as $k)
                                                <span
                                                    class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold rounded-md text-xs inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-chalkboard-user text-emerald-600"></i> Wali Kelas
                                                    {{ $k->nama_kelas }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span
                                                class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-700 font-semibold rounded-md text-xs">
                                                Wali Kelas
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 border border-red-200 text-red-600 font-semibold rounded-md text-xs">
                                        <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Belum Setting Kelas
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex justify-center items-center space-x-1.5">
                                    <!-- Tombol Lihat Detail Lengkap -->
                                    <button type="button" onclick="showDetailGuru({{ json_encode($guru) }})"
                                        class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-blue-600 hover:bg-blue-50 hover:border-blue-300 transition"
                                        title="Lihat Detail Lengkap">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.guru.edit', $guru->id) }}"
                                        class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-amber-600 hover:bg-amber-50 hover:border-amber-300 transition"
                                        title="Edit Data">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.guru.destroy', $guru->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru {{ $guru->nama_lengkap }}?')"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-red-600 hover:bg-red-50 hover:border-red-300 transition"
                                            title="Hapus Data">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i class="fa-solid fa-user-slash text-3xl text-gray-300"></i>
                                    <span class="text-sm">Belum ada data guru yang terdaftar.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if(method_exists($gurus, 'links') && $gurus->hasPages())
            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200">
                {{ $gurus->links() }}
            </div>
        @endif
    </div>

    <!-- 🔍 MODAL DETAIL DATA GURU LENGKAP -->
    <div id="modal-detail-guru"
        class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-blue-600"></i>
                    Detail Data Guru Lengkap
                </h3>
                <button type="button" onclick="closeDetailGuru()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Body Detail Data Guru -->
            <div class="p-6 overflow-y-auto space-y-6 text-sm">
                <!-- Section 1: Identitas Pribadi -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3 border-b pb-1">Identitas
                        Pribadi (Dukcapil)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-gray-50 p-3.5 rounded-xl border">
                        <div><span class="text-gray-500 text-xs block">Nama Lengkap:</span><span id="dt-nama"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">NIK:</span><span id="dt-nik"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Tempat, Tgl Lahir:</span><span id="dt-ttl"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Jenis Kelamin:</span><span id="dt-jk"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Nama Ibu Kandung:</span><span id="dt-ibu"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Pendidikan Terakhir:</span><span id="dt-pendidikan"
                                class="font-semibold text-gray-800"></span></div>
                    </div>
                </div>

                <!-- Section 2: Kepegawaian -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3 border-b pb-1">Status
                        Kepegawaian & Jabatan (BKN)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-gray-50 p-3.5 rounded-xl border">
                        <div><span class="text-gray-500 text-xs block">NIP:</span><span id="dt-nip"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Status Kepegawaian:</span><span id="dt-status"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Golongan:</span><span id="dt-golongan"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Jabatan:</span><span id="dt-jabatan"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Jenis Guru:</span><span id="dt-jenis-guru"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Mata Pelajaran:</span><span id="dt-mapel"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">TMT SK:</span><span id="dt-tmt"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">Masa Kerja (MKG):</span><span id="dt-mkg"
                                class="font-semibold text-gray-800"></span></div>
                    </div>
                </div>

                <!-- Section 3: Sertifikasi / Dapodik -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3 border-b pb-1">Sertifikasi &
                        Dapodik</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-gray-50 p-3.5 rounded-xl border">
                        <div><span class="text-gray-500 text-xs block">NUPTK:</span><span id="dt-nuptk"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">No. Serdik:</span><span id="dt-serdik"
                                class="font-semibold text-gray-800"></span></div>
                        <div><span class="text-gray-500 text-xs block">NRG:</span><span id="dt-nrg"
                                class="font-semibold text-gray-800"></span></div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
                <button type="button" onclick="closeDetailGuru()"
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>

        <div
            class="max-w-4xl mx-auto-center text-[10px] flex flex-col sm:flex-row items-center justify-between gap-1 text-slate-400">
            <div>
                &copy; {{ date('Y') }} Copyright: MahesAPP. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Paginasi -->
    @if(method_exists($gurus, 'links'))
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200">
            {{ $gurus->links() }}
        </div>
    @elseif(isset($gurus) && respond_to($gurus, 'render'))
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200">
            {{ $gurus->render() }}
        </div>
    @endif
    <!-- MODAL IMPORT DATA GURU -->
    <div id="modal-import-guru"
        class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    Import Data Guru
                </h3>
                <button type="button" onclick="document.getElementById('modal-import-guru').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.guru.import') }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-4">
                @csrf
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold">Format Header Excel:</p>
                        <a href="{{ route('admin.guru.template') }}"
                            class="inline-flex items-center gap-1 text-[11px] bg-blue-600 hover:bg-blue-700 text-white font-medium px-2 py-1 rounded transition">
                            <i class="fa-solid fa-download text-[10px]"></i>
                            <span>Download Template</span>
                        </a>
                    </div>
                    <p><code>nik</code>, <code>nip</code>, <code>nuptk</code>, <code>nama_lengkap</code>,
                        <code>tempat_lahir</code>, <code>tanggal_lahir</code>, <code>jenis_kelamin</code>,
                        <code>nama_ibu_kandung</code>, <code>status_kepegawaian</code>, dll.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pilih File (.xlsx / .xls / .csv)</label>
                    <input type="file" name="file" required accept=".xlsx, .xls, .csv"
                        class="block w-full text-xs text-gray-500 border border-gray-200 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-import-guru').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs sm:text-sm rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-upload text-xs"></i>
                        <span>Upload & Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript untuk Modal Detail -->
    <script>
        function showDetailGuru(guru) {
            document.getElementById('dt-nama').textContent = guru.nama_lengkap || '-';
            document.getElementById('dt-nik').textContent = guru.nik || '-';

            let ttl = (guru.tempat_lahir || '-') + ', ' + (guru.tanggal_lahir || '-');
            document.getElementById('dt-ttl').textContent = ttl;

            let jk = '-';
            if (guru.jenis_kelamin === 'L' || guru.jenis_kelamin === 'Laki-Laki') jk = 'Laki-Laki';
            if (guru.jenis_kelamin === 'P' || guru.jenis_kelamin === 'Perempuan') jk = 'Perempuan';
            document.getElementById('dt-jk').textContent = jk;

            document.getElementById('dt-ibu').textContent = guru.nama_ibu_kandung || '-';
            document.getElementById('dt-pendidikan').textContent = guru.pendidikan_terakhir || '-';

            document.getElementById('dt-nip').textContent = guru.nip || '-';
            document.getElementById('dt-status').textContent = guru.status_kepegawaian || '-';
            document.getElementById('dt-golongan').textContent = guru.golongan || '-';
            document.getElementById('dt-jabatan').textContent = guru.jabatan || '-';
            document.getElementById('dt-jenis-guru').textContent = guru.jenis_guru || '-';
            document.getElementById('dt-mapel').textContent = guru.mata_pelajaran || '-';
            document.getElementById('dt-tmt').textContent = guru.tmt_sk || '-';

            let mkg = (guru.mkg_tahun || 0) + ' Tahun ' + (guru.mkg_bulan || 0) + ' Bulan';
            document.getElementById('dt-mkg').textContent = mkg;

            document.getElementById('dt-nuptk').textContent = guru.nuptk || '-';
            document.getElementById('dt-serdik').textContent = guru.no_serdik || '-';
            document.getElementById('dt-nrg').textContent = guru.nrg || '-';

            document.getElementById('modal-detail-guru').classList.remove('hidden');
        }

        function closeDetailGuru() {
            document.getElementById('modal-detail-guru').classList.add('hidden');
        }
    </script>
@endsection