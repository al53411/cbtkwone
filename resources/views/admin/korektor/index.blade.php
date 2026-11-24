@extends('layouts.admin')

@section('title', 'Data Korektor')
@section('page_title', 'Korektor')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Data Korektor</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola guru yang berperan sebagai korektor ujian esai.</p>
        </div>
        <button type="button" onclick="openKorektorModal('{{ route('admin.korektor.create') }}', 'Tambah Korektor Baru')"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Tambah Korektor</span>
        </button>
    </div>

    <div id="korektorTableContainer" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center w-10">
                            No</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Lengkap</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">NIP / NUPTK</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Mata Pelajaran
                        </th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Kelas Diampu</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($korektors ?? [] as $guru)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-5 py-4 text-sm text-gray-500 text-center">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900">{{ $guru->nama_lengkap }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                <div class="font-mono text-xs">NIP: {{ $guru->nip ?? '-' }}</div>
                                <div class="font-mono text-[11px] text-gray-400">NUPTK: {{ $guru->nuptk ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $guru->mata_pelajaran ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ optional($guru->kelas)->pluck('nama_kelas')->join(', ') ?: '-' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        onclick="openKorektorModal('{{ route('admin.korektor.edit', $guru->id) }}', 'Edit Data Korektor')"
                                        class="px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-100 transition cursor-pointer">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <button type="button"
                                        onclick="hapusKorektorAjax('{{ route('admin.korektor.destroy', $guru->id) }}', {{ Js::from($guru->nama_lengkap) }})"
                                        class="px-3 py-1.5 bg-red-50 border border-red-300 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100 transition cursor-pointer">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-gray-400">
                                <i class="fa-solid fa-user-slash text-3xl mb-2 block"></i>
                                Belum ada data korektor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL AJAX CREATE / EDIT -->
    <div id="modalKorektor"
        class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/65 backdrop-blur-[2px] p-4">
        <div
            class="bg-white w-full max-w-2xl rounded-2xl border border-slate-200 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center">
                        <i class="fa-solid fa-user-pen text-sm"></i>
                    </span>
                    <h3 id="modalKorektorTitle" class="text-sm font-bold tracking-wide">Form Korektor</h3>
                </div>
                <button type="button" onclick="closeKorektorModal()"
                    class="text-slate-400 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Body Modal (Diisi dari create.blade.php / edit.blade.php via AJAX) -->
            <div id="modalKorektorBody" class="p-6 overflow-y-auto">
                <div class="py-10 flex flex-col items-center justify-center gap-3">
                    <div class="color-spinner"></div>
                    <p class="text-xs font-semibold text-slate-500">Memuat formulir...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalKorektor = document.getElementById('modalKorektor');
        const modalKorektorTitle = document.getElementById('modalKorektorTitle');
        const modalKorektorBody = document.getElementById('modalKorektorBody');

        function toggleModalKelas(state) {
            modalKorektorBody.querySelectorAll('.modal-kelas-cb').forEach(cb => cb.checked = state);
        }

        function closeKorektorModal() {
            modalKorektor.classList.add('hidden');
            modalKorektor.classList.remove('flex');
        }

        // Muat potongan HTML dari create.blade.php atau edit.blade.php tanpa pindah halaman
        async function openKorektorModal(url, title) {
            modalKorektorTitle.textContent = title;
            modalKorektorBody.innerHTML = `
                <div class="py-12 flex flex-col items-center justify-center gap-3">
                    <div class="color-spinner"></div>
                    <p class="text-xs font-semibold text-slate-500">Memuat formulir...</p>
                </div>
            `;
            modalKorektor.classList.remove('hidden');
            modalKorektor.classList.add('flex');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                if (!response.ok) throw new Error('Gagal memuat form (' + response.status + ')');

                modalKorektorBody.innerHTML = await response.text();
                bindModalFormSubmit();
            } catch (err) {
                modalKorektorBody.innerHTML = `
                    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">
                        ${err.message}
                    </div>
                `;
            }
        }

        // Kirim form Create / Edit secara AJAX lalu refresh tabel di belakang layar
        function bindModalFormSubmit() {
            const form = document.getElementById('formKorektorModal');
            const errorBox = document.getElementById('ajaxErrorBox');
            const submitBtn = document.getElementById('btnSubmitKorektor');
            if (!form) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                errorBox.classList.add('hidden');

                const originalBtnHtml = submitBtn.innerHTML;
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
                            throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data.');
                        }
                        return;
                    }

                    closeKorektorModal();
                    await reloadKorektorTable();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2200,
                        showConfirmButton: false
                    });
                } catch (err) {
                    errorBox.textContent = err.message;
                    errorBox.classList.remove('hidden');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        }

        // Hapus data tanpa reload halaman penuh
        async function hapusKorektorAjax(url, nama) {
            const confirmRes = await Swal.fire({
                icon: 'warning',
                title: 'Hapus Korektor?',
                text: `Data korektor "${nama}" beserta akun login-nya akan dihapus permanen.`,
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (!confirmRes.isConfirmed) return;

            if (typeof showPageLoader === 'function') showPageLoader('Menghapus data...');

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

                await reloadKorektorTable();

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

        // Ambil ulang isi tabel dari halaman index tanpa pindah/refresh halaman
        async function reloadKorektorTable() {
            const res = await fetch("{{ route('admin.korektor.index') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newTable = doc.getElementById('korektorTableContainer');
            if (newTable) {
                document.getElementById('korektorTableContainer').innerHTML = newTable.innerHTML;
            }
        }
    </script>
@endpush