<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Ujian Siswa - CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
</head>

<body class="bg-slate-900 text-slate-100 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6">

        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/20 text-blue-500 rounded-2xl mb-2 border border-blue-500/30">
                <i class="fa-solid fa-graduation-cap text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Portal Ujian Siswa</h1>
            <p class="text-xs text-slate-400">Silakan masuk menggunakan NISN & Password Anda</p>
        </div>

        <!-- Alert Error Global (Menampilkan SEMUA jenis error) -->
        @if ($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs p-3 rounded-lg space-y-1">
                <div class="flex items-center space-x-2 font-semibold">
                    <i class="fa-solid fa-triangle-exclamation text-base flex-shrink-0"></i>
                    <span>Gagal Masuk:</span>
                </div>
                <ul class="list-disc list-inside pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Alert Session Error (Jika menggunakan with('error', '...')) -->
        @if (session('error'))
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs p-3 rounded-lg flex items-center space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-base flex-shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form Login (Action diarah ke 'siswa.login.post') -->
        <form action="{{ route('siswa.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Input NISN -->
            <div class="space-y-1">
                <label for="nisn" class="text-xs font-semibold text-slate-300">NISN (Nomor Induk Siswa Nasional)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}" required autofocus
                        placeholder="Masukkan NISN" autocomplete="username"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
            </div>

            <!-- Input Password -->
            <div class="space-y-1">
                <div class="flex justify-between items-center">
                    <label for="password" class="text-xs font-semibold text-slate-300">Password</label>
                    <span class="text-[10px] text-blue-400 font-medium">Contoh: 1234567890*</span>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        placeholder="NISN + *" autocomplete="current-password"
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                        <i id="eye-icon" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Petunjuk Informasi -->
            <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-700/50 text-[11px] text-slate-400 space-y-1">
                <div class="font-medium text-slate-300"><i class="fa-solid fa-circle-info text-blue-400 mr-1"></i> Informasi Login:</div>
                <p>Password bawaan adalah kombinasi dari <b>NISN</b> ditambah tanda bintang (<b>*</b>) di akhir.</p>
            </div>

            <!-- Tombol Submit -->
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 rounded-lg text-sm transition duration-150 flex items-center justify-center space-x-2 shadow-lg shadow-blue-600/20">
                <span>Masuk / Login Ujian</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-700/50">
            &copy; {{ date('Y') }} Computer Based Test System
        </div>

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
    </script>
</body>

</html>