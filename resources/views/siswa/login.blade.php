<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Ujian CAT - Sistem CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
</head>

<body class="bg-slate-100 text-slate-800 font-sans min-h-screen flex flex-col justify-between antialiased">

    <!-- Top Navigation Bar khas CAT BKN -->
    <header class="bg-slate-900 text-white border-b-4 border-amber-500 shadow-md py-3 px-4 sm:px-8">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-white/12 rounded-lg p-1.5 border border-white/20 flex items-center justify-center">
                    <img src="{{ asset('logo.png') }}" alt="Logo CAT" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-black tracking-wider uppercase text-white leading-tight">CAT
                        SYSTEM</h1>
                    <p class="text-[10px] text-amber-400 font-semibold tracking-wide">COMPUTER ASSISTED TEST PORTAL</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center space-x-2 text-xs text-slate-300">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                <span class="font-medium">Sistem Ujian Terenkripsi & Aman</span>
            </div>
        </div>
    </header>

    <!-- Main Container Login -->
    <main class="flex-1 flex items-center justify-center p-4 my-6">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden">

            <!-- Card Header -->
            <div class="bg-slate-900 text-white p-6 text-center relative overflow-hidden">
                <div
                    class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-600/20 rounded-full blur-xl pointer-events-none">
                </div>

                <div
                    class="inline-flex items-center justify-center w-14 h-14 bg-white/10 rounded-2xl mb-3 border border-white/20 p-2 shadow-inner">
                    <img src="{{ asset('logo.png') }}" alt="Logo Portal Ujian" class="w-full h-full object-contain">
                </div>
                <h2 class="text-xl font-extrabold tracking-wide text-white uppercase">PORTAL UJIAN SISWA</h2>
                <p class="text-xs text-slate-300 mt-1 font-medium">Autentikasi Peserta Ujian CAT</p>
            </div>

            <div class="p-6 sm:p-8 space-y-5">

                <!-- Alert Error Global -->
                @if ($errors->any())
                    <div
                        class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-xs p-3.5 rounded-r-lg space-y-1 shadow-2xs">
                        <div class="flex items-center space-x-2 font-bold">
                            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm flex-shrink-0"></i>
                            <span>Gagal Autentikasi:</span>
                        </div>
                        <ul class="list-disc list-inside pl-4 space-y-0.5 text-rose-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Alert Session Error -->
                @if (session('error'))
                    <div
                        class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-xs p-3.5 rounded-r-lg flex items-center space-x-2 shadow-2xs">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm flex-shrink-0"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('siswa.login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Input NISN -->
                    <div class="space-y-1.5">
                        <label for="nisn" class="text-xs font-bold uppercase tracking-wider text-slate-700">NISN (Nomor
                            Peserta)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-solid fa-id-card text-sm"></i>
                            </span>
                            <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}" required autofocus
                                placeholder="Masukkan Nomor NISN" autocomplete="username"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 font-medium transition">
                        </div>
                    </div>

                    <!-- Input Password -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label for="password"
                                class="text-xs font-bold uppercase tracking-wider text-slate-700">Password / PIN
                                Ujian</label>
                            <span class="text-[10px] text-blue-600 font-semibold">Contoh: 1234567890*</span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-solid fa-key text-sm"></i>
                            </span>
                            <input type="password" id="password" name="password" required placeholder="NISN + *"
                                autocomplete="current-password"
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 font-medium transition">
                            <button type="button" onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
                                <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Petunjuk Format Login CAT -->
                    <div class="bg-blue-50/80 p-3.5 rounded-xl border border-blue-200 text-xs text-slate-700 space-y-1">
                        <div class="font-bold text-blue-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-blue-600"></i>
                            <span>Petunjuk Login Peserta:</span>
                        </div>
                        <ul class="text-[11px] text-slate-600 list-disc list-inside space-y-0.5 font-medium pl-1">
                            <li>Masukkan <b>NISN resmi</b> yang terdaftar pada sistem.</li>
                            <li>Password standar: <b>NISN</b> diakhiri tanda bintang (<b>*</b>).</li>
                        </ul>
                    </div>

                    <!-- Tombol Login -->
                    <button type="submit"
                        class="w-full bg-slate-900 hover:bg-blue-900 text-white font-bold py-3 rounded-xl text-xs sm:text-sm transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-slate-900/20 uppercase tracking-wider cursor-pointer">
                        <span>MASUK UJIAN CAT</span>
                        <i class="fa-solid fa-right-to-bracket text-xs"></i>
                    </button>
                </form>

            </div>
        </div>
    </main>

    <!-- Footer Formal CAT BKN Style -->
    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-500 font-medium">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                &copy; {{ date('Y') }} Computer Based Test System. All rights reserved.
            </div>
            <div class="flex items-center space-x-3 text-[11px] text-slate-400">
                <span>Versi CAT 2.0</span>
                <span>•</span>
                <span>Status Server: <strong class="text-emerald-600">ONLINE</strong></span>
            </div>
        </div>
    </footer>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.add('fa-eye');
                icon.classList.remove('fa-eye-slash');
            }
        }
    </script>
</body>

</html>