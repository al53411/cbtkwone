<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Korektor Ujian')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <style>
        .flat-input {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #1e293b;
            outline: none;
            transition: border-color 0.15s ease-in-out, background-color 0.15s ease-in-out;
        }

        .flat-input:focus {
            background-color: #ffffff;
            border-color: #4f46e5;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800">
    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800 z-30 transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out shrink-0">

            <div class="h-16 flex items-center justify-between bg-slate-900 px-6 border-b border-slate-800">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-7 h-7 object-contain shrink-0">
                    <span class="text-white font-bold text-base truncate">
                        {{ Auth::user()?->sekolah?->nama_sekolah ?? 'SDN Kawu 1' }}
                    </span>
                </div>
                <button type="button" onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
                <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Panel Korektor</p>

                <a href="{{ route('korektor.dashboard') }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('korektor.dashboard') ? 'bg-indigo-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span class="text-sm">Dashboard</span>
                </a>

                <div class="pt-2 space-y-1">
                    <p class="px-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tugas
                        Koreksi</p>

                    <a href="{{ route('korektor.koreksi.index') }}"
                        class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('korektor.koreksi.*') ? 'bg-indigo-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                        <i class="fa-solid fa-pen-to-square w-5 text-center text-blue-400"></i>
                        <span class="text-sm">Koreksi Jawaban</span>
                    </a>

                    <a href="{{ route('korektor.nilai.index') }}"
                        class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('korektor.nilai.*') ? 'bg-indigo-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                        <i class="fa-solid fa-square-poll-vertical w-5 text-center text-emerald-400"></i>
                        <span class="text-sm">Rekap &amp; Cetak Nilai</span>
                    </a>

                    <a href="{{ route('korektor.soal.index') }}"
                        class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('korektor.soal.*') ? 'bg-indigo-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                        <i class="fa-solid fa-book-open-reader w-5 text-center text-amber-400"></i>
                        <span class="text-sm">Kunci &amp; Pedoman</span>
                    </a>
                </div>
            </div>

            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'K', 0, 1)) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name ?? 'Korektor' }}
                            </p>
                            <p class="text-[10px] text-slate-400 font-mono truncate">NIP: {{ Auth::user()->nip ?? '-' }}
                            </p>
                        </div>
                    </div>

                    @if(Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                class="text-slate-400 hover:text-red-400 p-1.5 rounded transition cursor-pointer"
                                title="Logout">
                                <i class="fa-solid fa-right-from-bracket text-sm"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </aside>

        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 hidden md:hidden"></div>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-10 shrink-0">
                <div class="flex items-center space-x-3 min-w-0">
                    <button type="button" onclick="toggleSidebar()"
                        class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div class="flex items-center space-x-2 text-sm text-slate-500 truncate">
                        <span class="font-semibold text-indigo-600">Portal Korektor</span>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                        <span class="text-slate-600 font-medium truncate">@yield('page_title', 'Dashboard')</span>
                    </div>
                </div>

                <div class="flex items-center space-x-3 shrink-0">
                    <span
                        class="text-sm font-semibold text-slate-700 hidden sm:inline">{{ Auth::user()->name ?? 'Korektor' }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'K', 0, 1)) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), showConfirmButton: false, timer: 2000 });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Oops...', text: @json(session('error')) });
        @endif
    </script>
    @stack('scripts')
</body>

</html>