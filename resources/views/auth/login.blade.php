<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIPP (Sistem Informasi Pulang Pergi)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 text-gray-800 min-h-screen flex items-center justify-center p-4">
    <div x-data="{ showPassword: false }" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Logo & Header Banner -->
        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 p-6 text-center text-white relative">
            <div class="w-16 h-16 bg-white/10 backdrop-blur rounded-2xl mx-auto flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fa-solid fa-van-shuttle text-white"></i>
            </div>
            <h1 class="text-2xl font-black tracking-wider">SIPP</h1>
            <p class="text-xs text-blue-200 mt-1 font-medium">Sistem Informasi Pulang Pergi Travel</p>
        </div>

        <!-- Form Section -->
        <div class="p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-900 text-center mb-1">Masuk Ke Akun</h2>
            <p class="text-xs text-gray-500 text-center mb-6">Masukkan email atau nomor HP terdaftar Anda.</p>

            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3 rounded-lg flex items-start">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 mr-2 text-base mt-0.5"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email / Nomor HP</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="text" name="login_identifier" value="{{ old('login_identifier') }}" required placeholder="contoh@email.com / 0812xxxx" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••" class="w-full pl-10 pr-10 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" class="fa-solid text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                        Ingat Saya
                    </label>
                    <a href="#" @click.prevent="alert('Silakan hubungi customer service SIPP di 0812-3456-7890 untuk reset password.')" class="text-blue-600 hover:underline font-medium">Lupa Password?</a>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-blue-500/25 transition duration-200 text-sm flex items-center justify-center space-x-2 mt-4">
                    <span>Masuk Ke Aplikasi</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline ml-1">Daftar Akun Baru</a>
            </div>
        </div>
    </div>
</body>
</html>
