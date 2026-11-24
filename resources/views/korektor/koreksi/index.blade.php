@extends('layouts.korektor')

@section('title', 'Koreksi Jawaban Ujian')
@section('page_title', 'Koreksi Jawaban')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Daftar Paket Ujian (Koreksi Esai)</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pilih paket ujian di bawah ini untuk menilai jawaban esai siswa sesuai nomor urut soal.
            </p>
        </div>

        <div class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 px-3.5 py-2 rounded-xl">
            Total Paket: <strong class="text-slate-800">{{ isset($ujians) ? $ujians->count() : 0 }} Ujian</strong>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($ujians ?? [] as $ujian)
            @php
                $judul = $ujian->judul ?? $ujian->nama_ujian ?? $ujian->judul_ujian ?? 'Paket Ujian #' . $ujian->id;
                $mapel = $ujian->nama_mapel ?? $ujian->mata_pelajaran ?? $ujian->mapel ?? ($profilGuru->mata_pelajaran ?? 'Semua Mapel');
                $kelas = $ujian->nama_kelas ?? null;

                $totalEsai = (int) ($ujian->total_esai ?? 0);
                $sudahDikoreksi = (int) ($ujian->sudah_dikoreksi ?? 0);
                $belumDikoreksi = (int) ($ujian->belum_dikoreksi ?? 0);

                $persen = $totalEsai > 0 ? round(($sudahDikoreksi / $totalEsai) * 100) : 0;
            @endphp

            <div
                class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between shadow-xs hover:border-indigo-300 transition">
                <div>
                    <!-- Header Kartu: Mapel & Badge Status -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 rounded-lg truncate">
                            <i class="fa-solid fa-book mr-1"></i> {{ $mapel }}
                        </span>

                        @if($totalEsai === 0)
                            <span class="px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-600 rounded-full shrink-0">
                                Belum Ada Jawaban
                            </span>
                        @elseif($belumDikoreksi > 0)
                            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full shrink-0">
                                {{ $belumDikoreksi }} Belum Dinilai
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full shrink-0">
                                <i class="fa-solid fa-check mr-1"></i> Selesai
                            </span>
                        @endif
                    </div>

                    <!-- Judul Ujian & Kelas -->
                    <h3 class="text-base font-bold text-slate-800 leading-snug mb-1">{{ $judul }}</h3>

                    @if($kelas)
                        <p class="text-xs text-slate-500 mb-3">
                            <i class="fa-solid fa-users mr-1 text-slate-400"></i> Rombel: {{ $kelas }}
                        </p>
                    @endif

                    <!-- Bar Progres Koreksi -->
                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                            <span>Progres Koreksi</span>
                            <span class="font-semibold text-slate-700">
                                {{ $sudahDikoreksi }} / {{ $totalEsai }} ({{ $persen }}%)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="{{ $belumDikoreksi === 0 && $totalEsai > 0 ? 'bg-emerald-500' : 'bg-indigo-600' }} h-2 rounded-full transition-all"
                                style="width: {{ $persen }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    @if(Route::has('korektor.koreksi.show'))
                        <a href="{{ route('korektor.koreksi.show', $ujian->id) }}"
                            class="block w-full text-center px-4 py-2.5 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">
                            <i class="fa-solid fa-pen-to-square mr-1.5"></i> Buka Lembar Koreksi
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border border-slate-200 rounded-2xl p-10 text-center">
                <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2"></i>
                <p class="text-slate-600 font-semibold text-sm">Belum ada paket ujian yang tersedia.</p>
                <p class="text-slate-400 text-xs mt-1">Paket ujian akan otomatis muncul di sini setelah dibuat oleh
                    Admin/Superadmin.</p>
            </div>
        @endforelse
    </div>
@endsection