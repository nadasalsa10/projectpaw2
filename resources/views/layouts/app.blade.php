<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPP - Sistem Informasi Pulang Pergi')</title>
    <!-- Tailwind CSS CDN for instant styling resilience -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sipp: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome & Alpine.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen pb-20 md:pb-0">
    <!-- Navbar Top Desktop & Header -->
    <header class="bg-sipp-900 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('customer.home') }}" class="flex items-center space-x-2">
                <div class="bg-blue-500 text-white p-2 rounded-lg font-bold text-lg leading-none">
                    <i class="fa-solid fa-van-shuttle"></i>
                </div>
                <div>
                    <span class="font-extrabold text-xl tracking-wider text-white">SIPP</span>
                    <span class="text-xs block text-blue-200 leading-tight hidden sm:block">Pulang Pergi Travel</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('customer.home') }}" class="hover:text-blue-200 transition">Beranda</a>
                <a href="{{ route('customer.orders.index') }}" class="hover:text-blue-200 transition">Pesanan Saya</a>
                @auth
                    @if(Auth::user()->isCustomer())
                        <a href="{{ route('customer.waiting_list.index') }}" class="hover:text-blue-200 transition">Daftar Tunggu</a>
                    @endif
                @endauth
                <a href="{{ route('customer.help') }}" class="hover:text-blue-200 transition">Bantuan</a>
            </nav>

            <!-- User Auth Profile / Login Button -->
            <div class="flex items-center space-x-3">
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white shadow">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden md:inline font-medium text-sm">{{ Auth::user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-blue-200 hidden md:inline"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-52 bg-white text-gray-800 rounded-lg shadow-xl py-2 z-50 border border-gray-100">
                            @if(Auth::user()->isDriver())
                                <a href="{{ route('driver.dashboard') }}" class="block px-4 py-2 text-sm text-emerald-600 font-bold hover:bg-emerald-50"><i class="fa-solid fa-gauge mr-2"></i> Portal Driver</a>
                                <hr class="my-1 border-gray-100">
                            @elseif(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-purple-600 font-bold hover:bg-purple-50"><i class="fa-solid fa-shield mr-2"></i> Portal Admin</a>
                                <hr class="my-1 border-gray-100">
                            @endif
                            <a href="{{ route('customer.profile') }}" class="block px-4 py-2 text-sm hover:bg-gray-100"><i class="fa-solid fa-user mr-2 text-blue-600"></i> Profil Saya</a>
                            <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-100"><i class="fa-solid fa-ticket mr-2 text-blue-600"></i> Riwayat Pesanan</a>
                            <a href="{{ route('customer.waiting_list.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-100"><i class="fa-solid fa-clipboard-list mr-2 text-blue-600"></i> Daftar Tunggu</a>
                            <hr class="my-1 border-gray-100">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fa-solid fa-right-from-bracket mr-2"></i> Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2 rounded-lg transition shadow">Masuk</a>
                    <a href="{{ route('register') }}" class="bg-white text-sipp-900 hover:bg-blue-50 font-semibold text-sm px-4 py-2 rounded-lg transition shadow hidden sm:inline-block">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Alerts -->
    @if(session('success'))
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-500 mr-3 text-lg"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg flex items-center shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 mr-3 text-lg"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer Desktop -->
    <footer class="bg-gray-900 text-gray-400 py-8 mt-12 border-t border-gray-800 hidden md:block">
        <div class="max-w-6xl mx-auto px-4 grid grid-cols-3 gap-8">
            <div>
                <h3 class="text-white font-bold text-lg mb-2">SIPP Travel</h3>
                <p class="text-xs leading-relaxed">Sistem Informasi Pulang Pergi - Solusi pemesanan travel mobil antar kota cepat, aman, dan terpercaya.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Layanan</h4>
                <ul class="text-xs space-x-0 space-y-2">
                    <li><a href="{{ route('customer.home') }}" class="hover:text-white">Cari Travel</a></li>
                    <li><a href="{{ route('customer.orders.index') }}" class="hover:text-white">Cek E-Ticket</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Kontak & Bantuan</h4>
                <p class="text-xs">Email: support@sipp-travel.com</p>
                <p class="text-xs mt-1">WhatsApp: 0812-3456-7890</p>
            </div>
        </div>
        <div class="text-center text-xs mt-8 pt-4 border-t border-gray-800">
            &copy; {{ date('Y') }} SIPP (Sistem Informasi Pulang Pergi). All rights reserved.
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 px-4 md:hidden z-40 shadow-lg">
        <div class="max-w-md mx-auto flex justify-around items-center">
            <a href="{{ route('customer.home') }}" class="flex flex-col items-center {{ request()->routeIs('customer.home') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-blue-600' }}">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px] mt-1">Beranda</span>
            </a>
            <a href="{{ route('customer.orders.index') }}" class="flex flex-col items-center {{ request()->routeIs('customer.orders.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-blue-600' }}">
                <i class="fa-solid fa-ticket text-lg"></i>
                <span class="text-[10px] mt-1">Pesanan</span>
            </a>
            <a href="{{ route('customer.notifications.index') }}" class="flex flex-col items-center {{ request()->routeIs('customer.notifications.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-blue-600' }}">
                <i class="fa-solid fa-bell text-lg"></i>
                <span class="text-[10px] mt-1">Notifikasi</span>
            </a>
            <a href="{{ route('customer.profile') }}" class="flex flex-col items-center {{ request()->routeIs('customer.profile') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-blue-600' }}">
                <i class="fa-solid fa-user text-lg"></i>
                <span class="text-[10px] mt-1">Profil</span>
            </a>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
