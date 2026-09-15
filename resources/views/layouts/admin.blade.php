<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <style>
        /* Flat Card & Form Design */
        .flat-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
        }

        .flat-input {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #1e293b;
            outline: none;
            transition: all 0.15s ease-in-out;
        }

        .flat-input:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800">

    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800 z-30 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out h-full shrink-0">

            <!-- Header Sidebar -->
            <div class="h-16 flex items-center justify-between bg-slate-900 px-5 border-b border-slate-800 shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-7 h-7 object-contain shrink-0">
                    <span class="text-white font-bold text-sm tracking-wide truncate">
                        {{ Auth::user()->sekolah->nama_sekolah ?? $profilSekolah->nama_sekolah ?? 'Sistem Sekolah' }}
                    </span>
                </div>
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white focus:outline-none p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigasi Menu -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Menu Utama</p>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Dropdown Master Data -->
                @php
                $isAkademikActive = request()->routeIs(
                    'admin.sekolah.*', 
                    'admin.siswa.*', 
                    'admin.guru.*',
                    'admin.kelas.*', 
                    'admin.tahun-ajaran.*'
                );
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-akademik', 'arrow-akademik')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm hover:bg-slate-900 hover:text-white transition group focus:outline-none {{ $isAkademikActive ? 'text-white font-medium bg-slate-900/60' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-server w-5 text-center {{ $isAkademikActive ? 'text-blue-500' : 'text-slate-400 group-hover:text-blue-500' }} transition"></i>
                            <span>Master Data</span>
                        </div>
                        <i id="arrow-akademik"
                            class="fa-solid fa-chevron-down text-xs text-slate-500 group-hover:text-white transition-transform duration-200 {{ $isAkademikActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-akademik"
                        class="{{ $isAkademikActive ? '' : 'hidden' }} pl-9 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-lg">
                        <a href="{{ route('admin.sekolah.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.sekolah.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-school text-[10px] mr-2"></i> Profil Sekolah
                        </a>
                        <a href="{{ route('admin.tahun-ajaran.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.tahun-ajaran.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-calendar-days text-[10px] mr-2"></i> Tahun Ajaran
                        </a>
                        <a href="{{ route('admin.guru.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.guru.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-chalkboard-teacher text-[10px] mr-2"></i> Data Guru
                        </a>
                        <a href="{{ route('admin.kelas.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.kelas.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-layer-group text-[10px] mr-2"></i> Data Kelas
                        </a>
                        <a href="{{ route('admin.siswa.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.siswa.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-user-graduate text-[10px] mr-2"></i> Data Siswa
                        </a>
                    </div>
                </div>

                <!-- Dropdown Administrasi -->
                @php
                $isAdminActive = request()->routeIs(
                    'admin.mapel.*',
                    'admin.capaian-pembelajaran.*',
                    'admin.tujuan-pembelajaran.*',
                    'admin.atp.*',
                    'admin.modul_ajar.*',
                    'admin.absensi.*', 
                    'admin.kepala-sekolah.jurnal.*'
                );
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-administrasi', 'arrow-administrasi')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm hover:bg-slate-900 hover:text-white transition group focus:outline-none {{ $isAdminActive ? 'text-white font-medium bg-slate-900/60' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-folder-open w-5 text-center {{ $isAdminActive ? 'text-blue-500' : 'text-slate-400 group-hover:text-blue-500' }} transition"></i>
                            <span>Administrasi</span>
                        </div>
                        <i id="arrow-administrasi"
                            class="fa-solid fa-chevron-down text-xs text-slate-500 group-hover:text-white transition-transform duration-200 {{ $isAdminActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-administrasi"
                        class="{{ $isAdminActive ? '' : 'hidden' }} pl-9 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-lg">
                        
                        <!-- Mata Pelajaran -->
                        <a href="{{ route('admin.mapel.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.mapel.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-book-bookmark text-[10px] mr-2"></i> Mata Pelajaran
                        </a>

                        <!-- Capaian Pembelajaran -->
                        <a href="{{ route('admin.capaian-pembelajaran.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.capaian-pembelajaran.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-book-open-reader text-[10px] mr-2"></i> Capaian Pembelajaran
                        </a>

                        <a href="{{ route('admin.tujuan-pembelajaran.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.tujuan-pembelajaran.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-bullseye text-[10px] mr-2"></i> Tujuan Pembelajaran
                        </a>

                        <a href="{{ route('admin.atp.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.atp.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-route text-[10px] mr-2"></i> Alur Tujuan Pembelajaran
                        </a>

                        <!-- Modul Ajar -->
                        <a href="{{ route('admin.modul_ajar.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.modul_ajar.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-book-open text-[10px] mr-2"></i> Modul Ajar
                        </a>

                        <!-- Rekap Absensi -->
                        <a href="{{ route('admin.absensi.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.absensi.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-clipboard-user text-[10px] mr-2"></i> Rekap Absensi
                        </a>

                        <!-- Jurnal Harian -->
                        <a href="{{ route('admin.kepala-sekolah.jurnal.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.kepala-sekolah.jurnal.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-book text-[10px] mr-2"></i> Jurnal Harian
                        </a>

                    </div>
                </div>

                <!-- Dropdown Surat -->
                @php
                $isSuratActive = request()->routeIs('admin.surat-masuk.*', 'admin.surat-keluar.*');
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-surat', 'arrow-surat')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm hover:bg-slate-900 hover:text-white transition group focus:outline-none {{ $isSuratActive ? 'text-white font-medium bg-slate-900/60' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-envelope w-5 text-center {{ $isSuratActive ? 'text-blue-500' : 'text-slate-400 group-hover:text-blue-500' }} transition"></i>
                            <span>Arsip Surat</span>
                        </div>
                        <i id="arrow-surat"
                            class="fa-solid fa-chevron-down text-xs text-slate-500 group-hover:text-white transition-transform duration-200 {{ $isSuratActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-surat"
                        class="{{ $isSuratActive ? '' : 'hidden' }} pl-9 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-lg">
                        <a href="{{ Route::has('admin.surat-masuk.index') ? route('admin.surat-masuk.index') : '#' }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.surat-masuk.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-envelope-open-text text-[10px] mr-2"></i> Surat Masuk
                        </a>
                        <a href="{{ Route::has('admin.surat-keluar.index') ? route('admin.surat-keluar.index') : '#' }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('admin.surat-keluar.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-paper-plane text-[10px] mr-2"></i> Surat Keluar
                        </a>
                    </div>
                </div>

            </div>

            <!-- Footer Sidebar (Tombol Logout) -->
            <div class="p-3 border-t border-slate-800 bg-slate-900/50 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-400 bg-rose-500/10 hover:bg-rose-600 hover:text-white transition duration-150">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-20 hidden md:hidden transition-opacity"></div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Navbar Top -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-10 shrink-0 shadow-sm">
                <div class="flex items-center space-x-3">
                    <button onclick="toggleSidebar()"
                        class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500 truncate">
                        <span class="font-medium text-slate-700 hidden sm:inline">Admin</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 hidden sm:inline"></i>
                        <span class="text-slate-800 font-semibold truncate">@yield('page_title', 'Dashboard')</span>
                    </div>
                </div>

                <!-- User Info & Indikator Tahun Ajaran -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    @php
                        $taAktif = \App\Models\TahunAjaran::getAktif(auth()->user()->sekolah_id ?? null);
                    @endphp

                    @if($taAktif)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                            <i class="fa-solid fa-calendar-days mr-1.5 text-blue-500"></i>
                            <span class="hidden sm:inline">TP: </span>{{ $taAktif->tahun }} ({{ $taAktif->semester }})
                        </span>
                    @endif

                    <div class="flex items-center space-x-2 border-l border-slate-200 pl-2 sm:pl-3">
                        <span class="text-xs sm:text-sm font-bold text-slate-700 hidden md:inline truncate max-w-[120px]">
                            {{ Auth::user()->name ?? 'Administrator' }}
                        </span>
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100">
                @yield('content')
            </main>
        </div>

    </div>

    <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    // 1. Buka / Tutup Sidebar (Mobile)
    function toggleSidebar() {
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    // 2. Toggle Dropdown Menu Sidebar
    function toggleDropdown(id, arrowId) {
        const dropdown = document.getElementById(id);
        const arrow = document.getElementById(arrowId);

        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            dropdown.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    // 3. Resizing handler untuk responsivitas layar desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });

    // 4. Alert Flash Notification (SweetAlert2)
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: @json(session('success')),
        showConfirmButton: false,
        timer: 2000
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: @json(session('error'))
    });
    @endif
    </script>
</body>

</html>