<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPP Admin Portal')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            800: '#1e293b',
                            900: '#0f172a',
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
<body class="bg-gray-100 text-gray-800 font-sans flex h-screen overflow-hidden">

    <!-- Sidebar Admin -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 border-r border-slate-800">
        <!-- Logo -->
        <div class="p-4 border-b border-slate-800 flex items-center space-x-3">
            <div class="bg-blue-600 text-white p-2 rounded-lg text-lg font-bold">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <span class="font-extrabold text-white text-lg tracking-wider">SIPP ADMIN</span>
                <span class="text-[10px] block text-slate-400">Portal Manajemen Central</span>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-line w-6 text-center mr-2"></i> Dashboard
            </a>
            
            <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Master Data</div>
            
            <a href="{{ route('admin.routes.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.routes.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-route w-6 text-center mr-2"></i> Kelola Rute
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.vehicles.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-bus w-6 text-center mr-2"></i> Kelola Armada
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.drivers.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-id-card w-6 text-center mr-2"></i> Kelola Driver
            </a>
            <a href="{{ route('admin.customers.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.customers.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-users w-6 text-center mr-2"></i> Data Customer
            </a>

            <div class="pt-4 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Operasional</div>

            <a href="{{ route('admin.schedules.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.schedules.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-calendar-alt w-6 text-center mr-2"></i> Jadwal Perjalanan
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.bookings.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-book-bookmark w-6 text-center mr-2"></i> Data Booking
            </a>
            <a href="{{ route('admin.payments.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.payments.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-money-bill-wave w-6 text-center mr-2"></i> Pembayaran
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.reports.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-file-invoice-dollar w-6 text-center mr-2"></i> Laporan Keuangan
            </a>
        </nav>

        <!-- Admin Profile / Logout -->
        <div class="p-3 border-t border-slate-800 bg-slate-950 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <span class="text-xs font-semibold text-white block truncate w-32">{{ Auth::user()->name }}</span>
                    <span class="text-[10px] text-slate-400 block">Administrator</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-rose-400 p-2 text-sm" title="Keluar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center flex-shrink-0 shadow-sm">
            <h1 class="text-lg font-bold text-gray-800">@yield('page_title', 'Dashboard')</h1>
            <div class="flex items-center space-x-4">
                <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-medium">
                    <i class="fa-regular fa-clock mr-1"></i> {{ date('d M Y') }}
                </span>
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-500 mr-3 text-lg"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg flex items-center shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 mr-3 text-lg"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
