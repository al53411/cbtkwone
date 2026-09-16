<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Guru Dashboard')</title>

    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        .flat-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
        }

        .flat-dropdown {
            background-color: #0f172a;
            border-left: 3px solid #3b82f6;
        }

        .flat-input {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #1e293b;
            outline: none;
            transition: border-color 0.15s ease-in-out;
        }

        .flat-input:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 0px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800">

    <div class="flex h-screen overflow-hidden relative">

        <!-- ================= SIDEBAR GURU ================= -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800 z-30 transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out">

            <!-- Brand Header -->
            <div class="h-16 flex items-center justify-between bg-slate-900 px-6 border-b border-slate-800">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-7 h-7 object-contain shrink-0">
                    <span class="text-white font-bold text-base truncate">
                        {{ $profilSekolah->nama_sekolah ?? 'SDN Kawu 1' }}
                    </span>
                </div>
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Menu Navigation -->
            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
                <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Panel Guru</p>

                <!-- Dashboard -->
                <a href="{{ route('guru.dashboard') }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('guru.dashboard') ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span class="text-sm">Dashboard</span>
                </a>

                <!-- Dropdown Data Akademik -->
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-akademik', 'arrow-akademik')"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition group focus:outline-none text-slate-300">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-server text-slate-400 group-hover:text-blue-500 transition w-5 text-center"></i>
                            <span class="font-medium text-sm">Data Akademik</span>
                        </div>
                        <i id="arrow-akademik"
                            class="fa-solid fa-chevron-down text-xs text-slate-500 group-hover:text-white transition-transform duration-200 {{ request()->routeIs('guru.siswa.*', 'guru.sekolah.*', 'guru.profil.*') ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-akademik" class="{{ request()->routeIs('guru.siswa.*', 'guru.sekolah.*', 'guru.profil.*') ? '' : 'hidden' }} pl-11 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-lg">
                        <a href="{{ route('guru.sekolah.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('guru.sekolah.*') ? 'text-blue-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-school text-xs mr-2"></i> Data Sekolah
                        </a>
                        <a href="{{ route('guru.profil.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('guru.profil.*') ? 'text-blue-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-user text-xs mr-2"></i> Profil Guru
                        </a>
                        <a href="{{ route('guru.siswa.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('guru.siswa.*') ? 'text-blue-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-user-graduate text-xs mr-2"></i> Data Siswa
                        </a>
                    </div>
                </div>

                <!-- Dropdown Administrasi Guru -->
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-administrasi', 'arrow-administrasi')"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition group focus:outline-none text-slate-300">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-folder-open text-slate-400 group-hover:text-blue-500 transition w-5 text-center"></i>
                            <span class="font-medium text-sm">Administrasi</span>
                        </div>
                        <i id="arrow-administrasi"
                            class="fa-solid fa-chevron-down text-xs text-slate-500 group-hover:text-white transition-transform duration-200 {{ request()->routeIs('guru.jurnal.*', 'guru.absensi.*', 'guru.modul_ajar.*') ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-administrasi" class="{{ request()->routeIs('guru.jurnal.*', 'guru.absensi.*', 'guru.modul_ajar.*') ? '' : 'hidden' }} pl-11 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-lg">
                        <a href="{{ route('guru.jurnal.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('guru.jurnal.*') ? 'text-blue-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-book-open text-xs mr-2"></i> Jurnal Mengajar
                        </a>
                        
                        <a href="{{ route('guru.modul_ajar.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('guru.modul_ajar.*') ? 'text-blue-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-file-lines text-xs mr-2"></i> Modul Ajar
                        </a>

                        <a href="{{ route('guru.absensi.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('guru.absensi.*') ? 'text-blue-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-clipboard-user text-xs mr-2"></i> Absen
                        </a>
                    </div>
                </div>

            </div>

            <!-- Profile & Logout Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Guru Pendidik' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? 'guru@sekolah.sch.id' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-400 p-1.5 rounded transition" title="Logout">
                            <i class="fa-solid fa-right-from-bracket text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Overlay Sidebar Mobile -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 hidden md:hidden"></div>

        <!-- ================= MAIN CONTENT WRAPPER ================= -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Navbar Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-10">
                <div class="flex items-center space-x-4">
                    <button onclick="toggleSidebar()"
                        class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-sm text-slate-500">
                        <span class="font-medium text-blue-600">Portal Guru</span>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                        <span class="text-slate-600 font-medium">@yield('page_title', 'Dashboard')</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    @php
                        $sekolahId = auth()->user()->sekolah_id ?? null;
                        $taAktif = $sekolahId ? \App\Models\TahunAjaran::getAktif($sekolahId) : null;
                    @endphp

                    @if($taAktif)
                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="fa-solid fa-calendar-days mr-1.5"></i> TP: {{ $taAktif->tahun }} ({{ $taAktif->semester }})
                        </span>
                    @elseif(!$sekolahId)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200" title="User belum terhubung ke sekolah_id">
                            <i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Sekolah Belum Set
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200" title="Belum ada Tahun Ajaran yang di-set Aktif">
                            <i class="fa-solid fa-calendar-xmark mr-1.5"></i> TP Belum Aktif
                        </span>
                    @endif

                    <span class="text-sm font-semibold text-slate-700 hidden sm:inline">
                        {{ Auth::user()->name ?? 'Guru' }}
                    </span>
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'G', 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Main Content Section -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- ================= JAVASCRIPT ================= -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

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

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Flash Notification via SweetAlert2
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