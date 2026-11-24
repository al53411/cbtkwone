@extends('layouts.korektor')

@section('title', 'Lembar Koreksi Esai')
@section('page_title', 'Lembar Koreksi Esai')

@section('content')
    @php
        $judulUjian = $ujian->judul ?? $ujian->nama_ujian ?? 'Paket Ujian #' . $ujian->id;
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('korektor.koreksi.index') }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800 mb-1">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Ujian
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">{{ $judulUjian }}</h1>
        </div>

        <form method="GET" action="{{ route('korektor.koreksi.show', $ujian->id) }}" class="flex items-center gap-2">
            @if($soalAktif)
                <input type="hidden" name="soal_id" value="{{ $soalAktif->id }}">
            @endif
            <select name="kelas_id" onchange="this.form.submit()" class="flat-input px-3 py-2 text-xs font-semibold">
                <option value="">-- Semua Kelas Diampu --</option>
                @foreach($daftarKelas as $kls)
                    <option value="{{ $kls->id }}" {{ request('kelas_id') == $kls->id ? 'selected' : '' }}>
                        {{ $kls->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($soals->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center">
            <p class="text-slate-500 text-sm">Tidak ditemukan soal uraian/esai pada ujian ini.</p>
        </div>
    @else
        <!-- Navigasi Nomor Urut Soal -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-6 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Nomor Urut Soal Esai</span>
                <div class="flex items-center gap-3 text-[11px] text-slate-500">
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Selesai</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> Belum Lengkap</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($soals as $itemSoal)
                    @php
                        $isActive = $soalAktif && $soalAktif->id == $itemSoal->id;
                        $isDone = $itemSoal->sisa_belum == 0;
                    @endphp
                    <a href="{{ route('korektor.koreksi.show', ['ujian' => $ujian->id, 'soal_id' => $itemSoal->id, 'kelas_id' => request('kelas_id')]) }}"
                        class="px-4 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-2
                        {{ $isActive ? 'bg-indigo-600 text-white border-indigo-600' : ($isDone ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                        <span>Soal No. {{ $itemSoal->no_urut_tampil }}</span>
                        @if($itemSoal->sisa_belum > 0)
                            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $itemSoal->sisa_belum }}</span>
                        @else
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        @if($soalAktif)
            @php
                $teksSoal = $soalAktif->pertanyaan ?? $soalAktif->soal ?? $soalAktif->teks_soal ?? '-';
                $kunciJawaban = $soalAktif->kunci_jawaban ?? $soalAktif->jawaban_benar ?? $soalAktif->pembahasan ?? null;
                $bobotMaks = $soalAktif->bobot ?? $soalAktif->skor_maksimal ?? 100;
            @endphp

            <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-lg">Soal Nomor {{ $soalAktif->no_urut_tampil }}</span>
                    <span class="text-xs font-semibold text-slate-500">Skor Maksimal: <strong class="text-slate-800">{{ $bobotMaks }}</strong></span>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed mb-4">{!! $teksSoal !!}</div>

                @if($kunciJawaban)
                    <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200">
                        <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-key mr-1"></i> Kunci Jawaban / Pedoman Penskoran
                        </p>
                        <div class="text-sm text-emerald-950">{!! $kunciJawaban !!}</div>
                    </div>
                @endif
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h2 class="text-sm font-bold text-slate-800">Daftar Jawaban Siswa — Soal No. {{ $soalAktif->no_urut_tampil }}</h2>
                    <span class="text-xs font-semibold text-slate-500">Total: {{ $jawabanSiswas->count() }} Siswa</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4 w-56">Identitas Siswa</th>
                                <th class="py-3.5 px-4">Jawaban Esai Siswa</th>
                                <th class="py-3.5 px-4 w-48 text-center">Input Skor</th>
                                <th class="py-3.5 px-4 w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse($jawabanSiswas as $index => $item)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 text-center font-semibold text-slate-500 align-top">{{ $index + 1 }}</td>
                                    <td class="py-4 px-4 align-top">
                                        <p class="font-bold text-slate-800">{{ $item->nama_siswa }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $item->nama_kelas ?? '-' }} &bull; <span class="font-mono">{{ $item->nomor_identitas }}</span></p>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 whitespace-pre-line leading-relaxed text-sm">{{ $item->jawaban_teks }}</div>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <input type="number" step="0.01" min="0" max="{{ $bobotMaks }}"
                                                id="input-skor-{{ $item->jawaban_id }}"
                                                value="{{ $item->skor_esai }}"
                                                placeholder="0 - {{ $bobotMaks }}"
                                                onkeydown="if(event.key === 'Enter'){ event.preventDefault(); simpanSkor({{ $item->jawaban_id }}, {{ $bobotMaks }}); }"
                                                class="flat-input w-24 px-2.5 py-2 text-center font-bold text-slate-800">
                                            <button type="button"
                                                onclick="simpanSkor({{ $item->jawaban_id }}, {{ $bobotMaks }})"
                                                id="btn-simpan-{{ $item->jawaban_id }}"
                                                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition cursor-pointer"
                                                title="Simpan Skor">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center align-top" id="status-jawaban-{{ $item->jawaban_id }}">
                                        @if(!is_null($item->skor_esai))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <i class="fa-solid fa-check mr-1"></i> Dinilai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 px-4 text-center text-slate-500 text-sm italic">Belum ada siswa yang menjawab nomor soal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
@endsection

@push('scripts')
<script>
    async function simpanSkor(jawabanId, maxSkor) {
        const input = document.getElementById(`input-skor-${jawabanId}`);
        const btn = document.getElementById(`btn-simpan-${jawabanId}`);
        const statusCell = document.getElementById(`status-jawaban-${jawabanId}`);
        const nilai = parseFloat(input.value);

        if (isNaN(nilai) || nilai < 0 || nilai > maxSkor) {
            Swal.fire({ icon: 'warning', title: 'Skor Tidak Valid', text: `Masukkan nilai antara 0 sampai ${maxSkor}.` });
            input.focus();
            return;
        }

        const originalBtn = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const response = await fetch(`{{ url('/korektor/koreksi') }}/${jawabanId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ skor_esai: nilai })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-check mr-1"></i> Dinilai</span>`;
                input.classList.add('border-emerald-500', 'bg-emerald-50');
                setTimeout(() => input.classList.remove('border-emerald-500', 'bg-emerald-50'), 1200);
            } else {
                throw new Error(data.message || 'Gagal menyimpan skor.');
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: err.message });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtn;
        }
    }
</script>
@endpush