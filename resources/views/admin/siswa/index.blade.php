@extends('layouts.admin')

@section('title', 'Data Siswa')
@section('page_title', 'Input Data Siswa')

@section('content')
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Data Siswa</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Kelola data peserta ujian CAT, kelas, foto kartu, dan akun
                login siswa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openImportSiswaModal()"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md transition cursor-pointer">
                <i class="fa-solid fa-file-excel"></i>
                <span>Import Excel / CSV</span>
            </button>
            <button type="button" onclick="openSiswaModal('{{ route('admin.siswa.create') }}', 'Tambah Data Siswa Baru')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Siswa</span>
            </button>
        </div>
    </div>

    <!-- Filter & Pencarian Tanpa Reload -->
    <div class="flat-card p-3.5 mb-4">
        <form id="filterSiswaForm" action="{{ route('admin.siswa.index') }}" method="GET" data-no-loader="true"
            class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4">
                <select name="kelas_id" id="filterKelasSiswa" class="flat-input w-full px-3 py-2">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($listKelas ?? [] as $k)
                        <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-6">
                <input type="text" name="search" id="filterSearchSiswa" value="{{ request('search') }}"
                    placeholder="Cari Nama Siswa atau NISN..." class="flat-input w-full px-3 py-2">
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit"
                    class="flex-1 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-md transition cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
                </button>
                @if(request()->filled('kelas_id') || request()->filled('search'))
                    <button type="button" onclick="resetFilterSiswa()"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition cursor-pointer"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Siswa (Di-refresh otomatis via AJAX) -->
    <div id="siswaTableContainer" class="flat-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th
                            class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 text-center w-10">
                            No</th>
                        <th
                            class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 w-14 text-center">
                            Foto</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">NISN</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Nama Lengkap
                        </th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 text-center">L/P
                        </th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Kelas</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 text-center">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($siswas as $siswa)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 text-slate-500 text-center">
                                {{ ($siswas->currentPage() - 1) * $siswas->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(!empty($siswa->foto))
                                    <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto"
                                        class="w-8 h-10 object-cover rounded border border-slate-200 mx-auto">
                                @else
                                    <div
                                        class="w-8 h-10 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mx-auto">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700 font-semibold">{{ $siswa->nisn }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $siswa->nama_siswa }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded text-[11px] font-bold {{ $siswa->jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                                    {{ $siswa->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <span class="px-2.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs font-semibold">
                                    {{ $siswa->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button"
                                        onclick="openSiswaModal('{{ route('admin.siswa.edit', $siswa->id) }}', 'Edit Data Siswa')"
                                        class="px-2.5 py-1 bg-amber-50 border border-amber-300 text-amber-700 rounded-md text-xs font-medium hover:bg-amber-100 transition cursor-pointer">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <button type="button"
                                        onclick="hapusSiswaAjax('{{ route('admin.siswa.destroy', $siswa->id) }}', {{ Js::from($siswa->nama_siswa) }})"
                                        class="px-2.5 py-1 bg-rose-50 border border-rose-300 text-rose-700 rounded-md text-xs font-medium hover:bg-rose-100 transition cursor-pointer">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-400">
                                <i class="fa-solid fa-users-slash text-3xl mb-2 block"></i>
                                Belum ada data siswa ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
            <div id="siswaPaginationLinks" class="px-4 py-3 bg-slate-50 border-t border-slate-200">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL INPUT / EDIT SISWA (BACKGROUND BLUR) -->
    <div id="modalSiswa"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/55 backdrop-blur-sm p-4 transition-all">
        <div
            class="bg-white w-full max-w-2xl rounded-lg border border-slate-300 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modal -->
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-md bg-blue-600/30 text-blue-400 flex items-center justify-center">
                        <i class="fa-solid fa-user-graduate text-xs"></i>
                    </span>
                    <h3 id="modalSiswaTitle" class="text-sm font-bold tracking-wide">Form Siswa</h3>
                </div>
                <button type="button" onclick="closeSiswaModal()"
                    class="text-slate-400 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Body Modal (Diisi AJAX dari create.blade.php / edit.blade.php) -->
            <div id="modalSiswaBody" class="p-5 overflow-y-auto">
                <div class="py-10 flex flex-col items-center justify-center gap-3">
                    <div class="color-spinner"></div>
                    <p class="text-xs font-semibold text-slate-500">Memuat formulir...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL IMPORT EXCEL / CSV (BACKGROUND BLUR) -->
    <div id="modalImportSiswa"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/55 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-md rounded-lg border border-slate-300 shadow-2xl overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-emerald-400"></i>
                    <h3 class="text-sm font-bold">Import Data Siswa (XLSX / CSV)</h3>
                </div>
                <button type="button" onclick="closeImportSiswaModal()"
                    class="text-slate-400 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="formImportSiswaModal" action="{{ route('admin.siswa.import') }}" method="POST"
                enctype="multipart/form-data" data-no-loader="true" class="p-5 space-y-4">
                @csrf
                <div id="importErrorBox"
                    class="hidden p-3 bg-rose-50 border border-rose-300 text-rose-800 rounded-md text-xs"></div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1.5">
                        Pilih File Excel (.xlsx) atau CSV (.csv)
                    </label>
                    <input type="file" name="file" accept=".xlsx,.csv,.txt" required
                        class="flat-input w-full px-3 py-2 text-xs">
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        Kolom wajib: <strong>nisn</strong>, <strong>nama_siswa</strong>, <strong>jenis_kelamin</strong>
                        (L/P), <strong>kelas</strong>.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                    <button type="button" onclick="closeImportSiswaModal()"
                        class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-md cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btnImportSiswa"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md cursor-pointer">
                        <i class="fa-solid fa-upload mr-1"></i> Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalSiswa = document.getElementById('modalSiswa');
        const modalSiswaTitle = document.getElementById('modalSiswaTitle');
        const modalSiswaBody = document.getElementById('modalSiswaBody');
        const modalImportSiswa = document.getElementById('modalImportSiswa');

        function closeSiswaModal() {
            modalSiswa.classList.add('hidden');
            modalSiswa.classList.remove('flex');
        }

        function openImportSiswaModal() {
            document.getElementById('importErrorBox').classList.add('hidden');
            modalImportSiswa.classList.remove('hidden');
            modalImportSiswa.classList.add('flex');
        }

        function closeImportSiswaModal() {
            modalImportSiswa.classList.add('hidden');
            modalImportSiswa.classList.remove('flex');
        }

        // Tutup modal jika klik area blur di luar kotak modal
        modalSiswa.addEventListener('click', function (e) {
            if (e.target === modalSiswa) closeSiswaModal();
        });
        modalImportSiswa.addEventListener('click', function (e) {
            if (e.target === modalImportSiswa) closeImportSiswaModal();
        });

        // Buka Form Create / Edit di dalam modal background blur
        async function openSiswaModal(url, title) {
            modalSiswaTitle.textContent = title;
            modalSiswaBody.innerHTML = `
                <div class="py-12 flex flex-col items-center justify-center gap-3">
                    <div class="color-spinner"></div>
                    <p class="text-xs font-semibold text-slate-500">Memuat formulir...</p>
                </div>
            `;
            modalSiswa.classList.remove('hidden');
            modalSiswa.classList.add('flex');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                if (!response.ok) throw new Error('Gagal memuat form (' + response.status + ')');

                modalSiswaBody.innerHTML = await response.text();
                bindSiswaFormSubmit();
            } catch (err) {
                modalSiswaBody.innerHTML = `
                    <div class="p-3 bg-rose-50 border border-rose-300 text-rose-700 rounded-md text-xs font-semibold">
                        ${err.message}
                    </div>
                `;
            }
        }

        // Submit Create / Edit tanpa pindah halaman
        function bindSiswaFormSubmit() {
            const form = document.getElementById('formSiswaModal');
            const errorBox = document.getElementById('ajaxErrorBox');
            const submitBtn = document.getElementById('btnSubmitSiswa');
            if (!form) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                errorBox.classList.add('hidden');

                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Menyimpan...</span>`;

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            const list = Object.values(data.errors).flat().map(msg => `<li>${msg}</li>`).join('');
                            errorBox.innerHTML = `<p class="font-bold mb-1">Periksa inputan berikut:</p><ul class="list-disc pl-5 space-y-0.5">${list}</ul>`;
                            errorBox.classList.remove('hidden');
                        } else {
                            throw new Error(data.message || 'Gagal menyimpan data siswa.');
                        }
                        return;
                    }

                    closeSiswaModal();
                    await reloadSiswaTable();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } catch (err) {
                    errorBox.textContent = err.message;
                    errorBox.classList.remove('hidden');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            });
        }

        // Submit Import Excel/CSV tanpa pindah halaman
        document.getElementById('formImportSiswaModal')?.addEventListener('submit', async function (e) {
            e.preventDefault();
            const errorBox = document.getElementById('importErrorBox');
            const btn = document.getElementById('btnImportSiswa');
            errorBox.classList.add('hidden');

            const ori = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Mengimport...`;

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: new FormData(this)
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Gagal mengimport file.');

                closeImportSiswaModal();
                this.reset();
                await reloadSiswaTable();

                Swal.fire({
                    icon: 'success',
                    title: 'Import Selesai!',
                    text: data.message,
                    timer: 2200,
                    showConfirmButton: false
                });
            } catch (err) {
                errorBox.textContent = err.message;
                errorBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = ori;
            }
        });

        // Hapus siswa tanpa pindah halaman
        async function hapusSiswaAjax(url, nama) {
            const confirmRes = await Swal.fire({
                icon: 'warning',
                title: 'Hapus Data Siswa?',
                text: `Data "${nama}" beserta akun login siswa akan dihapus permanen.`,
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (!confirmRes.isConfirmed) return;

            if (typeof showPageLoader === 'function') showPageLoader('Menghapus data siswa...');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: '_method=DELETE'
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Gagal menghapus data.');

                await reloadSiswaTable();

                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus!',
                    text: data.message,
                    timer: 1800,
                    showConfirmButton: false
                });
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
            } finally {
                if (typeof hidePageLoader === 'function') hidePageLoader();
            }
        }

        // Filter & Pencarian tanpa reload halaman
        document.getElementById('filterSiswaForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(this)).toString();
            const url = this.action + (params ? '?' + params : '');
            window.history.pushState({}, '', url);
            reloadSiswaTable(url);
        });

        document.getElementById('filterKelasSiswa')?.addEventListener('change', function () {
            document.getElementById('filterSiswaForm').dispatchEvent(new Event('submit'));
        });

        function resetFilterSiswa() {
            const baseUrl = "{{ route('admin.siswa.index') }}";
            document.getElementById('filterKelasSiswa').value = '';
            document.getElementById('filterSearchSiswa').value = '';
            window.history.pushState({}, '', baseUrl);
            reloadSiswaTable(baseUrl);
        }

        // Intercept klik tombol paginasi agar tidak reload halaman
        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#siswaPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                window.history.pushState({}, '', pageLink.href);
                reloadSiswaTable(pageLink.href);
            }
        });

        // Reload isi tabel secara diam-diam di belakang layar
        async function reloadSiswaTable(targetUrl = window.location.href) {
            const res = await fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newContainer = doc.getElementById('siswaTableContainer');
            const currentContainer = document.getElementById('siswaTableContainer');
            if (newContainer && currentContainer) {
                currentContainer.innerHTML = newContainer.innerHTML;
            }
        }
    </script>
@endpush