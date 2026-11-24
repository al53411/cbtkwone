@extends('layouts.admin')

@section('title', 'Profil Sekolah')
@section('page_title', 'Profil Sekolah')

@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Identitas &amp; Profil Sekolah</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola data identitas, kontak resmi, proktor, dan pengawas ujian CAT unit sekolah.
            </p>
        </div>
        <span
            class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300 self-start sm:self-auto">
            <span class="w-2 h-2 mr-1.5 bg-emerald-600 rounded-full"></span> Status: Aktif
        </span>
    </div>

    @if(session('success'))
        <div
            class="mb-5 p-3.5 bg-emerald-50 border-l-4 border-l-emerald-600 border border-emerald-200 text-emerald-800 text-xs sm:text-sm rounded-r-md flex items-center">
            <i class="fa-solid fa-circle-check mr-2 text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            class="mb-5 p-3.5 bg-rose-50 border-l-4 border-l-rose-600 border border-rose-200 text-rose-800 text-xs sm:text-sm rounded-r-md flex items-center">
            <i class="fa-solid fa-triangle-exclamation mr-2 text-rose-600"></i> {{ session('error') }}
        </div>
    @endif

    @if(isset($warning))
        <div
            class="mb-5 p-3.5 bg-amber-50 border-l-4 border-l-amber-500 border border-amber-200 text-amber-800 text-xs sm:text-sm rounded-r-md flex items-center">
            <i class="fa-solid fa-circle-exclamation mr-2 text-amber-600"></i> {{ $warning }}
        </div>
    @endif

    @php
        $isNotSuperadmin = auth()->user()->role !== 'superadmin';
    @endphp

    <div class="flat-card overflow-hidden">
        <form action="{{ route('admin.sekolah.update', $sekolah->id ?? 0) }}" method="POST" enctype="multipart/form-data"
            class="p-5 sm:p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- SECTION 1: IDENTITAS UTAMA -->
            <div>
                <div
                    class="flex items-center text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 mb-4 border-b border-slate-200">
                    <span class="w-1.5 h-4 bg-emerald-600 mr-2 inline-block"></span>
                    <span>Identitas Utama Sekolah</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">NPSN</label>
                        <input type="text" name="npsn" value="{{ old('npsn', $sekolah->npsn ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                        @if($isNotSuperadmin)
                            <small class="text-[11px] text-slate-400 mt-1 block">* NPSN diatur oleh Superadmin</small>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Nama
                            Sekolah</label>
                        <input type="text" name="nama_sekolah"
                            value="{{ old('nama_sekolah', $sekolah->nama_sekolah ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Jenjang
                            Sekolah</label>
                        <input type="text" name="jenjang" value="{{ old('jenjang', $sekolah->jenjang ?? '-') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Status
                            Sekolah</label>
                        <input type="text" name="status" value="{{ old('status', $sekolah->status ?? '-') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ALAMAT & LOKASI -->
            <div>
                <div
                    class="flex items-center text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 mb-4 border-b border-slate-200">
                    <span class="w-1.5 h-4 bg-emerald-600 mr-2 inline-block"></span>
                    <span>Alamat &amp; Lokasi</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Alamat
                            Lengkap</label>
                        <textarea name="alamat" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>{{ old('alamat', $sekolah->alamat ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Desa /
                            Kelurahan</label>
                        <input type="text" name="desa_kelurahan"
                            value="{{ old('desa_kelurahan', $sekolah->desa_kelurahan ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Kecamatan</label>
                        <input type="text" name="kecamatan" value="{{ old('kecamatan', $sekolah->kecamatan ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Kabupaten /
                            Kota</label>
                        <input type="text" name="kabupaten_kota"
                            value="{{ old('kabupaten_kota', $sekolah->kabupaten_kota ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Provinsi</label>
                        <input type="text" name="provinsi" value="{{ old('provinsi', $sekolah->provinsi ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: PENANGGUNG JAWAB & KONTAK -->
            <div>
                <div
                    class="flex items-center text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 mb-4 border-b border-slate-200">
                    <span class="w-1.5 h-4 bg-emerald-600 mr-2 inline-block"></span>
                    <span>Penanggung Jawab &amp; Kontak</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                            Nama Kepala Sekolah &amp; Gelar
                        </label>
                        <input type="text" name="nama_kepsek" value="{{ old('nama_kepsek', $sekolah->nama_kepsek ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">NIP Kepala
                            Sekolah</label>
                        <input type="text" name="nip_kepsek" value="{{ old('nip_kepsek', $sekolah->nip_kepsek ?? '') }}"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 outline-none {{ $isNotSuperadmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white focus:border-emerald-600' }}"
                            {{ $isNotSuperadmin ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center">
                            No. Telepon / WhatsApp
                            <span
                                class="ml-1.5 text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-300 px-1.5 py-0.5 rounded-xs font-semibold normal-case">
                                Dapat Diubah
                            </span>
                        </label>
                        <input type="text" name="telepon" value="{{ old('telepon', $sekolah->telepon ?? '') }}"
                            placeholder="Contoh: 08123456789"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none transition">
                        @error('telepon')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center">
                            Email Resmi
                            <span
                                class="ml-1.5 text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-300 px-1.5 py-0.5 rounded-xs font-semibold normal-case">
                                Dapat Diubah
                            </span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $sekolah->email ?? '') }}"
                            placeholder="sekolah@email.com"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none transition">
                        @error('email')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 4: DATA PROKTOR UJIAN CAT -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 pb-2 mb-4 border-b border-slate-200">
                    <div class="flex items-center text-xs font-bold text-slate-800 uppercase tracking-wider">
                        <span class="w-1.5 h-4 bg-emerald-600 mr-2 inline-block"></span>
                        <span>Data Proktor / Teknisi Ujian CAT</span>
                    </div>
                    @if(!empty($sekolah->nama_proktor))
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold">
                            <i class="fa-solid fa-check"></i> Proktor Aktif: {{ $sekolah->nama_proktor }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-md border border-slate-200">
                    <div>
                        <label for="nama_proktor"
                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center">
                            Nama Lengkap Proktor &amp; Gelar
                            <span
                                class="ml-1.5 text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-300 px-1.5 py-0.5 rounded-xs font-semibold normal-case">
                                Dapat Diubah
                            </span>
                        </label>
                        <input type="text" name="nama_proktor" id="nama_proktor"
                            value="{{ old('nama_proktor', $sekolah->nama_proktor ?? '') }}"
                            placeholder="Contoh: Alif Sairoji, S.Pd.I"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none transition @error('nama_proktor') border-rose-500 @enderror">
                        <small class="text-[11px] text-slate-500 mt-1 block">
                            * Otomatis tampil pada tanda tangan cetak hasil &amp; berita acara CAT.
                        </small>
                        @error('nama_proktor')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nip_proktor"
                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center">
                            NIP / NUPTK Proktor
                            <span
                                class="ml-1.5 text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-300 px-1.5 py-0.5 rounded-xs font-semibold normal-case">
                                Dapat Diubah
                            </span>
                        </label>
                        <input type="text" name="nip_proktor" id="nip_proktor"
                            value="{{ old('nip_proktor', $sekolah->nip_proktor ?? '') }}"
                            placeholder="Kosongkan atau isi tanda - jika non-ASN"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none font-mono transition @error('nip_proktor') border-rose-500 @enderror">
                        <small class="text-[11px] text-slate-500 mt-1 block">
                            * Isi tanda <code>-</code> apabila proktor berstatus non-ASN / belum memiliki NIP.
                        </small>
                        @error('nip_proktor')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 5: DATA PENGAWAS RUANG UJIAN CAT -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 pb-2 mb-4 border-b border-slate-200">
                    <div class="flex items-center text-xs font-bold text-slate-800 uppercase tracking-wider">
                        <span class="w-1.5 h-4 bg-emerald-600 mr-2 inline-block"></span>
                        <span>Data Pengawas Ruang Ujian CAT</span>
                    </div>
                    @if(!empty($sekolah->nama_pengawas))
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold">
                            <i class="fa-solid fa-check"></i> Pengawas Default: {{ $sekolah->nama_pengawas }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-md border border-slate-200">
                    <div>
                        <label for="nama_pengawas"
                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center">
                            Nama Lengkap Pengawas &amp; Gelar
                            <span
                                class="ml-1.5 text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-300 px-1.5 py-0.5 rounded-xs font-semibold normal-case">
                                Dapat Diubah
                            </span>
                        </label>
                        <input type="text" name="nama_pengawas" id="nama_pengawas"
                            value="{{ old('nama_pengawas', $sekolah->nama_pengawas ?? '') }}"
                            placeholder="Contoh: Siti Kalimatus Sa'diyah, S.Pd"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none transition @error('nama_pengawas') border-rose-500 @enderror">
                        <small class="text-[11px] text-slate-500 mt-1 block">
                            * Otomatis mengisi kolom Pengawas Ruang pada Berita Acara Ujian CAT.
                        </small>
                        @error('nama_pengawas')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nip_pengawas"
                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1 flex items-center">
                            NIP / NUPTK Pengawas
                            <span
                                class="ml-1.5 text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-300 px-1.5 py-0.5 rounded-xs font-semibold normal-case">
                                Dapat Diubah
                            </span>
                        </label>
                        <input type="text" name="nip_pengawas" id="nip_pengawas"
                            value="{{ old('nip_pengawas', $sekolah->nip_pengawas ?? '') }}"
                            placeholder="Kosongkan atau isi tanda - jika non-ASN"
                            class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none font-mono transition @error('nip_pengawas') border-rose-500 @enderror">
                        <small class="text-[11px] text-slate-500 mt-1 block">
                            * Isi tanda <code>-</code> apabila pengawas berstatus non-ASN / belum memiliki NIP.
                        </small>
                        @error('nip_pengawas')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 6: LOGO RESMI LEMBAGA / SEKOLAH -->
            <div>
                <div
                    class="flex items-center text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 mb-4 border-b border-slate-200">
                    <span class="w-1.5 h-4 bg-emerald-600 mr-2 inline-block"></span>
                    <span>Logo Resmi Lembaga / Sekolah</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-md border border-slate-200">
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-20 h-20 bg-white border border-slate-300 rounded-md p-2 flex items-center justify-center shrink-0 overflow-hidden"
                            id="preview-logo-box">
                            @if(!empty($sekolah->logo))
                                <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Lembaga"
                                    class="w-full h-full object-contain">
                            @else
                                <img src="{{ asset('logo.png') }}" alt="Logo Bawaan"
                                    class="w-full h-full object-contain opacity-75">
                            @endif
                        </div>
                        <div class="flex-1 w-full">
                            <label for="logo"
                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">
                                Upload Logo Lembaga
                            </label>
                            <input type="file" name="logo" id="logo"
                                accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                onchange="previewLogo(this)"
                                class="w-full px-3 py-1.5 text-xs sm:text-sm rounded-md border border-slate-300 bg-white focus:border-emerald-600 outline-none transition @error('logo') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                                Format didukung: PNG, JPG, JPEG, WEBP, atau SVG (Maks. 2MB). Disarankan berlatar
                                transparan.<br>
                                <span class="font-semibold text-emerald-700">
                                    * Jika sekolah belum upload logo, sistem dan kartu ujian otomatis menggunakan logo
                                    bawaan (default).
                                </span>
                            </p>
                            @error('logo')
                                <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN -->
            <div class="pt-4 border-t border-slate-200 flex justify-end">
                <button type="submit" {{ isset($warning) ? 'disabled' : '' }}
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-semibold rounded-md transition flex items-center cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Perbarui Data Sekolah
                </button>
            </div>

        </form>
    </div>

    <script>
        function previewLogo(input) {
            const box = document.getElementById('preview-logo-box');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    box.innerHTML = `<img src="${e.target.result}" alt="Preview Logo" class="w-full h-full object-contain">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection