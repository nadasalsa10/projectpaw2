<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPP Driver Portal')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        driver: {
                            50: '#f0fdf4',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome & Alpine.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- HTML5 QRCode Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-gray-900 text-gray-100 flex flex-col min-h-screen pb-20">
    <!-- Header -->
    <header class="bg-gray-800 border-b border-gray-700 sticky top-0 z-40 px-4 py-3 shadow">
        <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto flex justify-between items-center">
            <a href="{{ route('driver.dashboard') }}" class="flex items-center space-x-2">
                <div class="bg-emerald-600 text-white p-2 rounded-lg text-sm font-bold">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <div>
                    <span class="font-bold text-lg text-white">SIPP Driver</span>
                    <span class="text-[10px] block text-emerald-400 font-semibold uppercase tracking-wider">Aplikasi Pengemudi</span>
                </div>
            </a>
            <div class="flex items-center space-x-2">
                <span class="text-xs bg-emerald-900/80 text-emerald-300 border border-emerald-600/50 px-2.5 py-1 rounded-full font-medium">
                    <i class="fa-solid fa-circle text-[8px] text-emerald-400 mr-1 animate-pulse"></i> {{ Auth::user()->name }}
                </span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-400 p-2 text-sm" title="Keluar">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Global Alerts -->
    @if(session('success'))
        <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-4 mt-3">
            <div class="bg-emerald-950 border border-emerald-700 text-emerald-200 px-4 py-2.5 rounded-lg text-xs flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-400 mr-2 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-4 mt-3">
            <div class="bg-rose-950 border border-rose-700 text-rose-200 px-4 py-2.5 rounded-lg text-xs flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 mr-2 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow max-w-md md:max-w-2xl lg:max-w-4xl mx-auto w-full px-4 pt-4">
        @yield('content')
    </main>

    <!-- Driver Bottom Navigation Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-gray-800 border-t border-gray-700 py-2 px-4 z-40 shadow-2xl">
        <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto flex justify-around items-center">
            <a href="{{ route('driver.dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('driver.dashboard') ? 'text-emerald-400 font-bold' : 'text-gray-400 hover:text-gray-200' }}">
                <i class="fa-solid fa-gauge text-lg"></i>
                <span class="text-[10px] mt-1">Dashboard</span>
            </a>
            <a href="{{ route('driver.schedules') }}" class="flex flex-col items-center {{ request()->routeIs('driver.schedules*') ? 'text-emerald-400 font-bold' : 'text-gray-400 hover:text-gray-200' }}">
                <i class="fa-solid fa-calendar-days text-lg"></i>
                <span class="text-[10px] mt-1">Jadwal</span>
            </a>
            <a href="{{ route('driver.scan') }}" class="flex flex-col items-center relative -top-3">
                <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center text-white shadow-lg border-4 border-gray-900 hover:scale-105 transition">
                    <i class="fa-solid fa-qrcode text-xl"></i>
                </div>
                <span class="text-[10px] mt-0.5 font-bold text-emerald-400">Scan QR</span>
            </a>
            <a href="{{ route('driver.reports') }}" class="flex flex-col items-center {{ request()->routeIs('driver.reports') ? 'text-emerald-400 font-bold' : 'text-gray-400 hover:text-gray-200' }}">
                <i class="fa-solid fa-file-invoice text-lg"></i>
                <span class="text-[10px] mt-1">Laporan</span>
            </a>
            <a href="{{ route('driver.profile') }}" class="flex flex-col items-center {{ request()->routeIs('driver.profile') ? 'text-emerald-400 font-bold' : 'text-gray-400 hover:text-gray-200' }}">
                <i class="fa-solid fa-user-gear text-lg"></i>
                <span class="text-[10px] mt-1">Profil</span>
            </a>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
