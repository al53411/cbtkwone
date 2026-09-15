@extends('layouts.guru')

@section('title', 'Modul Ajar')
@section('page_title', 'Modul Ajar')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header & Navigasi --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">{{ $modulAjar->judul }}</h1>
            <p class="text-muted mb-0">
                <span class="badge bg-primary me-2">{{ $modulAjar->mapel->nama_mapel }}</span>
                Fase {{ $modulAjar->fase }} | {{ $modulAjar->kelas }} | Semester {{ $modulAjar->semester }}
            </p>
        </div>
        <div>
            <a href="{{ route('guru.modul_ajar.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('guru.modul_ajar.edit', $modulAjar->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Modul
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kolom Kiri: Informasi Umum & Komponen Utama --}}
        <div class="col-lg-8">
            
            {{-- Informasi Umum --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-primary">Informasi Umum</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Alokasi Waktu:</strong>
                            <p class="text-muted mb-0">{{ $modulAjar->alokasi_waktu }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Target Peserta Didik:</strong>
                            <p class="text-muted mb-0">
                                @if(is_array($modulAjar->target_peserta_didik))
                                    {{ implode(', ', $modulAjar->target_peserta_didik) }}
                                @else
                                    {{ $modulAjar->target_peserta_didik }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Model Pembelajaran:</strong>
                            <p class="text-muted mb-0">{{ $modulAjar->model_pembelajaran ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Tahun Ajaran:</strong>
                            <p class="text-muted mb-0">{{ $modulAjar->tahun_ajaran }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Capaian & Tujuan Pembelajaran --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-primary">Capaian & Tujuan Pembelajaran</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6><strong>Capaian Pembelajaran (CP):</strong></h6>
                        <p class="text-muted bg-light p-3 rounded">
                            {{ $modulAjar->capaianPembelajaran->deskripsi_cp }}
                        </p>
                    </div>

                    <div>
                        <h6><strong>Tujuan Pembelajaran (TP):</strong></h6>
                        @if($modulAjar->tujuanPembelajarans->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($modulAjar->tujuanPembelajarans as $tp)
                                    <li class="list-group-item px-0">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        {{ $tp->deskripsi }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted fst-italic">Belum ada data Tujuan Pembelajaran.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Langkah Pembelajaran --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-primary">Langkah-Langkah Pembelajaran</h5>
                </div>
                <div class="card-body">
                    @if($modulAjar->langkahPembelajarans->count() > 0)
                        <div class="accordion" id="accordionLangkah">
                            @foreach($modulAjar->langkahPembelajarans as $index => $langkah)
                                <div class="accordion-item mb-2 border rounded">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                            Pertemuan Ke-{{ $langkah->pertemuan_ke }} ({{ $langkah->alokasi_waktu ?? '-' }})
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#accordionLangkah">
                                        <div class="accordion-body">
                                            <h6><strong>Kegiatan Pembelajaran:</strong></h6>
                                            <p class="text-muted mb-0">{!! nl2br(e($langkah->kegiatan)) !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted fst-italic mb-0">Belum ada rincian langkah pembelajaran.</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Komponen Inti & Pendukung --}}
        <div class="col-lg-4">
            
            {{-- Status & Metadata --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-primary">Status Modul</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Status Public:</span>
                        @if($modulAjar->status == 'published')
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </div>
                    <hr>
                    <small class="text-muted d-block mb-1">Penyusun: <strong>{{ $modulAjar->user->name }}</strong></small>
                    <small class="text-muted d-block">Dibuat pada: {{ $modulAjar->created_at->format('d M Y, H:i') }}</small>
                </div>
            </div>

            {{-- Kompetensi Awal & Pemantik --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-primary">Komponen Inti</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><strong>Kompetensi Awal:</strong></h6>
                        <p class="text-muted small mb-0">{{ $modulAjar->kompetensi_awal ?? '-' }}</p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6><strong>Pemahaman Bermakna:</strong></h6>
                        <p class="text-muted small mb-0">{{ $modulAjar->pemahaman_bermakna ?? '-' }}</p>
                    </div>
                    <hr>
                    <div>
                        <h6><strong>Pertanyaan Pemantik:</strong></h6>
                        <p class="text-muted small mb-0">{{ $modulAjar->pertanyaan_pemantik ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Profil Pelajar Pancasila --}}
            @if(!empty($modulAjar->profil_pelajar_pancasila))
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 font-weight-bold text-primary">Profil Pelajar Pancasila</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-1">
                            @foreach((array)$modulAjar->profil_pelajar_pancasila as $profil)
                                <span class="badge bg-info text-dark me-1 mb-1">{{ $profil }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection