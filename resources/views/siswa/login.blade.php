<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Ujian CAT - Sistem CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        @keyframes waveFloat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        .wave-animate {
            animation: waveFloat 4.5s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-slate-200/80 text-slate-800 font-sans min-h-screen flex items-center justify-center p-4 antialiased">

    <!-- Kartu Utama Gaya Modern Wave UI -->
    <div
        class="w-full max-w-[390px] bg-white rounded-[32px] shadow-2xl overflow-hidden relative flex flex-col justify-between min-h-[640px] border border-blue-100">

        <!-- Gelombang Biru Bagian Atas (2 Lapis) -->
        <div class="relative w-full h-44 select-none pointer-events-none">
            <svg viewBox="0 0 400 190" preserveAspectRatio="none" class="w-full h-full">
                <!-- Lapisan Biru Muda (Belakang) -->
                <path d="M0,0 L400,0 L400,145 C315,90 215,185 110,165 C55,155 20,175 0,185 Z" fill="#93C5FD"
                    class="wave-animate" />
                <!-- Lapisan Biru Utama (Depan) -->
                <path d="M0,0 L400,0 L400,110 C310,45 190,150 85,135 C40,128 15,142 0,150 Z" fill="#2563EB" />
            </svg>

            <!-- Identitas Ringkas di Atas Gelombang Biru -->
            <div class="absolute top-6 left-7 right-7 flex items-center justify-between text-white">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur-xs border border-white/25 p-1.5 flex items-center justify-center">
                        <img src="{{ asset('logo.png') }}" alt="Logo CAT" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <p class="text-xs font-extrabold tracking-wider uppercase leading-none">CAT SYSTEM</p>
                        <p class="text-[10px] text-blue-100 font-medium mt-0.5">Portal Ujian Siswa</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-white/15 text-[10px] font-semibold tracking-wide">
                    ONLINE
                </span>
            </div>
        </div>

        <!-- Area Form Login Tengah -->
        <div class="px-8 pt-2 pb-4 flex-1 flex flex-col justify-center relative z-10">
            <div class="mb-5">
                <h1 class="text-3xl font-extrabold text-blue-600 tracking-tight">Login</h1>
                <p class="text-xs text-slate-400 mt-1 font-medium">Masukkan NISN dan PIN untuk memulai ujian</p>
            </div>

            <!-- Alert Error Validasi -->
            @if ($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-600 text-[11px] p-3 rounded-xl space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-start gap-1.5 font-medium">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Alert Session Error -->
            @if (session('error'))
                <div
                    class="mb-4 bg-rose-50 border border-rose-200 text-rose-600 text-[11px] p-3 rounded-xl flex items-start gap-1.5 font-medium">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form id="formLoginSiswa" action="{{ route('siswa.login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Input NISN -->
                <div>
                    <label for="nisn" class="block text-[11px] font-semibold text-blue-600 mb-1">
                        NISN Peserta
                    </label>
                    <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}" required autofocus
                        placeholder="Contoh: 0146752372" autocomplete="username"
                        class="w-full px-3.5 py-2.5 bg-white border border-blue-400 rounded-lg text-xs sm:text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 transition font-medium">
                </div>

                <!-- Input Password -->
                <div>
                    <label for="password" class="block text-[11px] font-semibold text-blue-600 mb-1">
                        Password / PIN
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            placeholder="NISN diakhiri tanda *" autocomplete="current-password"
                            class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-blue-400 rounded-lg text-xs sm:text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 transition font-medium">
                        <button type="button" onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-blue-400 hover:text-blue-600 transition">
                            <i id="eye-icon" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                    <div class="flex justify-end mt-1.5">
                        <span class="text-[10px] font-semibold text-blue-600">
                            Format standar: NISN + *
                        </span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Gelombang Biru Bagian Bawah + Tombol Login Menyatu -->
        <div class="relative w-full h-36 mt-2 select-none">
            <svg viewBox="0 0 400 150" preserveAspectRatio="none" class="w-full h-full block">
                <!-- Lapisan Biru Muda (Belakang) -->
                <path d="M0,75 C95,125 215,15 320,35 C360,42 385,28 400,18 L400,150 L0,150 Z" fill="#93C5FD" />
                <!-- Lapisan Biru Utama (Depan) -->
                <path d="M0,98 C100,145 225,35 330,52 C365,58 388,45 400,36 L400,150 L0,150 Z" fill="#2563EB" />
            </svg>

            <!-- Konten di Atas Gelombang Bawah -->
            <div class="absolute inset-x-0 bottom-5 px-7 flex items-end justify-between">
                <div class="text-[10px] text-white/90 font-medium leading-tight">
                    <span>Butuh reset login?</span><br>
                    <strong class="font-bold text-white">Hubungi Proktor</strong>
                </div>

                <button type="submit" form="formLoginSiswa"
                    class="px-8 py-2.5 rounded-lg border border-white/85 bg-blue-600/40 hover:bg-white hover:text-blue-600 text-white font-bold text-xs sm:text-sm tracking-wide shadow-md transition duration-200 cursor-pointer">
                    Login
                </button>
            </div>
        </div>

    </div>

    <!-- Overlay Animasi Saat Form Login Dikirim -->
    <div id="loadingLoginOverlay"
        class="hidden fixed inset-0 bg-blue-950/75 backdrop-blur-xs z-50 flex flex-col items-center justify-center text-white px-4 text-center">
        <div
            class="w-16 h-16 rounded-2xl bg-white text-blue-600 flex items-center justify-center animate-bounce mb-4 shadow-2xl">
            <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
        <h3 class="text-sm font-extrabold tracking-wide uppercase">Memverifikasi Peserta...</h3>
        <p class="text-xs text-blue-100 mt-1">Menyiapkan ruang ujian CAT Anda.</p>
    </div>

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

        document.getElementById('formLoginSiswa').addEventListener('submit', function () {
            document.getElementById('loadingLoginOverlay').classList.remove('hidden');
        });
    </script>
</body>

</html>