<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - SIPP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 text-gray-800 min-h-screen flex items-center justify-center p-4 py-8">
    <div x-data="{ role: '{{ old('role', 'customer') }}', showPassword: false }" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden my-auto">
        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 p-6 text-center text-white relative">
            <div class="w-14 h-14 bg-white/10 backdrop-blur rounded-2xl mx-auto flex items-center justify-center text-2xl mb-2">
                <i class="fa-solid fa-user-plus text-white"></i>
            </div>
            <h1 class="text-xl font-extrabold tracking-wider">Registrasi Akun SIPP</h1>
            <p class="text-xs text-blue-200 mt-0.5">Pilih jenis akun dan nikmati kemudahan layanan SIPP</p>

            <!-- Role Selection Tabs -->
            <div class="mt-4 flex bg-blue-900/50 p-1 rounded-xl">
                <button type="button" @click="role = 'customer'" :class="role === 'customer' ? 'bg-white text-blue-900 shadow-md font-bold' : 'text-blue-200 hover:text-white font-medium'" class="flex-1 py-2 text-xs rounded-lg transition-all flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-user"></i>
                    <span>Customer (Penumpang)</span>
                </button>
                <button type="button" @click="role = 'driver'" :class="role === 'driver' ? 'bg-white text-blue-900 shadow-md font-bold' : 'text-blue-200 hover:text-white font-medium'" class="flex-1 py-2 text-xs rounded-lg transition-all flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-id-card-clip"></i>
                    <span>Driver (Pengemudi)</span>
                </button>
            </div>
        </div>

        <div class="p-6">
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

            <form action="{{ route('register.post') }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="role" :value="role">

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Fadhil Anhar" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@domain.com" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="081234567890" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Driver Specific Field -->
                <div x-show="role === 'driver'" x-transition>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor SIM Driver</label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" placeholder="SIM-B1-123456" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Customer Specific Fields -->
                <div x-show="role === 'customer'" x-transition class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat (Opsional)</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Jl. Merdeka No. 10" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Kontak Darurat (Opsional)</label>
                        <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" placeholder="081299998888" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                    <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="Minimal 8 karakter" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                    <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" required placeholder="Ulangi password" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" @click="showPassword = !showPassword" class="mr-2"> Tampilkan Password
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition text-sm mt-2 flex items-center justify-center space-x-2">
                    <span>Daftar Sebagai <span x-text="role === 'customer' ? 'Customer' : 'Driver'"></span></span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-4 pt-3 border-t border-gray-100 text-center text-xs text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline ml-1">Masuk Di Sini</a>
            </div>
        </div>
    </div>
</body>
</html>
