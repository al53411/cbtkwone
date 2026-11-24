@php $isAjax = request()->ajax(); @endphp

@if(!$isAjax)
@section('title', 'Edit Korektor')
@section('page_title', 'Edit Korektor')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Edit Data Korektor</h1>
</div>
<div class="bg-white border border-slate-200 rounded-xl shadow-xs p-5 sm:p-7">
    @endif

    <div id="ajaxErrorBox" class="hidden mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs">
    </div>

    <form id="formKorektorModal" action="{{ route('admin.korektor.update', $korektor->id) }}" method="POST"
        data-no-loader="true" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $korektor->nama_lengkap) }}"
                class="flat-input w-full px-3.5 py-2.5" required>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    NIP <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nip" value="{{ old('nip', $korektor->nip) }}"
                    class="flat-input w-full px-3.5 py-2.5 font-mono" maxlength="18" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    NUPTK <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <input type="text" name="nuptk" value="{{ old('nuptk', $korektor->nuptk) }}"
                    class="flat-input w-full px-3.5 py-2.5 font-mono" maxlength="16">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Mata Pelajaran <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="mata_pelajaran" value="{{ old('mata_pelajaran', $korektor->mata_pelajaran) }}"
                class="flat-input w-full px-3.5 py-2.5" required>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-sm font-semibold text-slate-700">
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
                class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl max-h-48 overflow-y-auto">
                @foreach($kelas ?? [] as $k)
                    @php
                        $checkedIds = old('kelas_id', $selected ?? []);
                    @endphp
                    <label
                        class="flex items-center gap-2 p-2 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-400 transition select-none">
                        <input type="checkbox" name="kelas_id[]" value="{{ $k->id }}"
                            class="modal-kelas-cb rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            @checked(in_array($k->id, (array) $checkedIds))>
                        <span class="text-xs font-semibold text-slate-700 truncate">{{ $k->nama_kelas }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" name="reset_password" value="1"
                    class="rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                <span class="text-xs font-semibold text-amber-800">
                    Reset Password Akun ke NIP Baru (wajib ganti sandi saat login berikutnya)
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
            @if($isAjax)
                <button type="button" onclick="closeKorektorModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                    Batal
                </button>
            @else
                <a href="{{ route('admin.korektor.index') }}"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                    Batal
                </a>
            @endif

            <button type="submit" id="btnSubmitKorektor"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-xs transition cursor-pointer">
                <i class="fa-solid fa-save"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>

    @if(!$isAjax)
        </div>
        @endsection
    @endif