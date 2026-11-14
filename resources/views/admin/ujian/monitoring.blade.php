@extends('layouts.admin')

@section('title', 'Monitoring Peserta Ujian')
@section('header', 'Monitoring Peserta Live')

@section('content')
    <div class="w-full space-y-6">

        <!-- Header Informasi Sesi Ujian -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $ujian->mapel->nama_mapel ?? '-' }} &bull; Kelas {{ $ujian->kelas->nama_kelas ?? '-' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Token: {{ $ujian->token }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-slate-800">{{ $ujian->nama_ujian }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pantau status pengerjaan siswa, lepas kunci login perangkat, atau selesaikan ujian peserta secara langsung.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.ujian.monitoring', $ujian->id) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition gap-1.5">
                    <i class="fa-solid fa-rotate"></i> Refresh Data
                </a>
                <a href="{{ route('admin.ujian.index') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Filter Pencarian & Status -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.ujian.monitoring', $ujian->id) }}"
                class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN siswa..."
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <select name="status"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 p-2.5 text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Semua Status Pengerjaan --</option>
                        <option value="mengerjakan" @selected(request('status') === 'mengerjakan')>Sedang Mengerjakan</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
                    </button>
                    <a href="{{ route('admin.ujian.monitoring', $ujian->id) }}"
                        class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Peserta Live -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800">Daftar Peserta Aktif ({{ $pesertas->count() }} Siswa)</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">NISN & Nama Siswa</th>
                            <th class="py-3.5 px-4">Kelas</th>
                            <th class="py-3.5 px-4 text-center">Status Ujian</th>
                            <th class="py-3.5 px-4 text-center">Waktu Selesai</th>
                            <th class="py-3.5 px-4 text-center">Kontrol Proktor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($pesertas as $index => $item)
                        @php
                            $namaSiswa = $item->siswa->nama_siswa 
                                ?? $item->siswa->nama 
                                ?? $item->siswa->nama_lengkap 
                                ?? $item->siswa->name 
                                ?? '-';
                            $isSelesai = $item->status === 'selesai';
                        @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $namaSiswa }}</span>
                                    <span class="text-[11px] font-mono text-slate-400">{{ $item->siswa->nisn ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">
                                        {{ $item->siswa->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($isSelesai)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                            <i class="fa-solid fa-circle-check mr-1"></i> SELESAI
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold animate-pulse">
                                            <i class="fa-solid fa-spinner fa-spin mr-1"></i> MENGERJAKAN
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-500">
                                    {{ $item->waktu_selesai ? \Carbon\Carbon::parse($item->waktu_selesai)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">

                                        <!-- 1. Tombol Reset Login -->
                                        @if (!$isSelesai)
                                            <form id="form-reset-login-{{ $item->id }}"
                                                action="{{ route('admin.ujian.reset-login', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="button"
                                                    onclick="konfirmasiAksi('form-reset-login-{{ $item->id }}', 'Reset Login Peserta?', 'Sesi perangkat {{ $namaSiswa }} akan dilepas agar bisa login ulang tanpa menghapus jawaban.', '#0284c7', 'Ya, Reset Login')"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 rounded-lg text-[11px] font-semibold transition gap-1"
                                                    title="Lepas Kunci Perangkat">
                                                    <i class="fa-solid fa-key"></i> Reset Login
                                                </button>
                                            </form>

                                            <!-- 2. Tombol Paksa Selesai -->
                                            <form id="form-paksa-selesai-{{ $item->id }}"
                                                action="{{ route('admin.ujian.paksa-selesai', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="button"
                                                    onclick="konfirmasiAksi('form-paksa-selesai-{{ $item->id }}', 'Paksa Selesai Ujian?', 'Ujian {{ $namaSiswa }} akan ditutup sekarang dan nilai dihitung dari jawaban terakhir yang tersimpan.', '#d97706', 'Ya, Paksa Selesai')"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-200 rounded-lg text-[11px] font-semibold transition gap-1"
                                                    title="Hitung Skor & Selesaikan">
                                                    <i class="fa-solid fa-flag-checkered"></i> Paksa Selesai
                                                </button>
                                            </form>
                                        @endif

                                        <!-- 3. Tombol Reset Ujian (Susulan / Remedial) -->
                                        <form id="form-reset-ujian-{{ $item->id }}"
                                            action="{{ route('admin.ujian.reset-ujian', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="konfirmasiAksi('form-reset-ujian-{{ $item->id }}', 'Reset Total Ujian (Susulan)?', 'PERHATIAN: Seluruh jawaban dan nilai {{ $namaSiswa }} pada sesi ini akan DIHAPUS permanen agar dapat mengulang ujian dari awal!', '#e11d48', 'Ya, Hapus & Ulang')"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 rounded-lg text-[11px] font-semibold transition gap-1"
                                                title="Hapus Jawaban & Ulang Ujian">
                                                <i class="fa-solid fa-trash-can-arrow-up"></i> Reset Ujian
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-users-slash text-2xl mb-2 block text-slate-300"></i>
                                    Belum ada peserta yang masuk ke sesi ujian ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function konfirmasiAksi(formId, judul, pesan, warnaTombol, teksTombol) {
            Swal.fire({
                title: judul,
                text: pesan,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: warnaTombol,
                cancelButtonColor: '#64748b',
                confirmButtonText: teksTombol,
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endsection