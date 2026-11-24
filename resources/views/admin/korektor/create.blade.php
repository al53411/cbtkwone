@php $isAjax = request()->ajax(); @endphp

@if(!$isAjax)

@section('title', 'Tambah Korektor')
@section('page_title', 'Tambah Korektor')
@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-800">Korektor</h1>
    <p class="text-slate-500 text-xs mt-1">
        Data korektor akan otomatis dibuatkan akun login dengan NIP sebagai username &amp; password.
    </p>
</div>
<div class="flat-card p-5 sm:p-6">
    @endif

    <div id="ajaxErrorBox" class="hidden mb-4 p-3 bg-rose-50 border border-rose-300 text-rose-800  text-xs">
    </div>

    <form id="formKorektorModal" action="{{ route('admin.korektor.store') }}" method="POST" data-no-loader="true"
        class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" class="flat-input w-full px-3 py-2"
                placeholder="Contoh: Siti Rahayu, S.Pd" required>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    NIP <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nip" value="{{ old('nip') }}" class="flat-input w-full px-3 py-2 font-mono"
                    placeholder="Nomor Induk Pegawai" maxlength="18" required>
                <p class="text-[11px] text-slate-500 mt-1">
                    <i class="fa-solid fa-circle-info text-blue-500 mr-1"></i> NIP menjadi username &amp; password awal.
                </p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                    NUPTK <span class="text-slate-400 font-normal normal-case">(opsional)</span>
                </label>
                <input type="text" name="nuptk" value="{{ old('nuptk') }}" class="flat-input w-full px-3 py-2 font-mono"
                    placeholder="Nomor Unik PTK" maxlength="16">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                Mata Pelajaran <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="mata_pelajaran" value="{{ old('mata_pelajaran') }}"
                class="flat-input w-full px-3 py-2" placeholder="Contoh: Bahasa Indonesia" required>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">
                    Kelas yang Dikoreksi <span class="text-rose-500">*</span>
                </label>
                @if(isset($kelas) && $kelas->isNotEmpty())
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" onclick="toggleModalKelas(true)"
                            class="text-blue-600 hover:underline font-semibold cursor-pointer">Pilih Semua</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="toggleModalKelas(false)"
                            class="text-slate-500 hover:underline cursor-pointer">Reset</button>
                    </div>
                @endif
            </div>

            <div
                class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-2.5 bg-slate-50 border border-slate-300 rounded-md max-h-44 overflow-y-auto">
                @forelse($kelas ?? [] as $k)
                    <label
                        class="flex items-center gap-2 px-2.5 py-1.5 bg-white border border-slate-200 rounded-md cursor-pointer hover:border-blue-500 transition select-none">
                        <input type="checkbox" name="kelas_id[]" value="{{ $k->id }}"
                            class="modal-kelas-cb rounded-xs border-slate-300 text-blue-600 focus:ring-0"
                            @checked(in_array($k->id, (array) old('kelas_id', [])))>
                        <span class="text-xs font-medium text-slate-700 truncate">{{ $k->nama_kelas }}</span>
                    </label>
                @empty
                    <p class="col-span-full text-xs text-amber-700">Belum ada data kelas.</p>
                @endforelse
            </div>
        </div>

        <div
            class="bg-slate-50 border-l-4 border-l-blue-600 border border-slate-200 rounded-r-md px-3.5 py-2.5 text-xs text-slate-700 flex items-start gap-2.5">
            <i class="fa-solid fa-key mt-0.5 text-blue-600 shrink-0"></i>
            <div>
                <span class="font-semibold text-slate-800">Akun Login Otomatis:</span>
                Username &amp; password menggunakan <strong>NIP</strong> dengan hak akses <strong>Korektor</strong>.
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
            @if($isAjax)
                <button type="button" onclick="closeKorektorModal()"
                    class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-md transition cursor-pointer">
                    Batal
                </button>
            @else
                <a href="{{ route('admin.korektor.index') }}"
                    class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-md transition">
                    Batal
                </a>
            @endif

            <button type="submit" id="btnSubmitKorektor"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition cursor-pointer">
                <i class="fa-solid fa-save"></i>
                <span>Simpan &amp; Buat Akun</span>
            </button>
        </div>
    </form>

    @if(!$isAjax)
        </div>
        @endsection
    @endif