@extends('layouts.driver')

@section('title', 'Laporan Pemesanan - SIPP Driver')

@section('content')
<div class="space-y-4">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center text-xs font-bold text-gray-300 hover:text-white bg-gray-800 border border-gray-700 hover:bg-gray-700 px-3 py-1.5 rounded-xl transition shadow">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Dashboard
            </a>
            <div>
                <h1 class="text-lg font-bold text-white">Laporan Pemesanan</h1>
                <p class="text-xs text-gray-400">Riwayat tiket, manifes & status perjalanan armada Anda</p>
            </div>
        </div>
        <a href="{{ route('driver.scan') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center shadow transition">
            <i class="fa-solid fa-qrcode mr-1.5"></i> Scan QR
        </a>
    </div>

    <!-- Summary Metric Cards (Responsive Grid: 2 cols on mobile, 3 on tablet, 5 on desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
        <!-- Total Tiket -->
        <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => ''])) }}" 
           class="bg-gray-800 border {{ !request('status') ? 'border-blue-500 shadow-blue-500/20 shadow-md' : 'border-gray-700' }} p-3 rounded-2xl flex items-center space-x-3 transition hover:border-gray-500">
            <div class="w-10 h-10 rounded-xl bg-blue-900/60 border border-blue-500/30 text-blue-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-ticket text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Total Tiket</span>
                <span class="text-lg font-extrabold text-white">{{ $totalTicketsCount }}</span>
            </div>
        </a>

        <!-- Sudah Naik / Check-In -->
        <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'checked_in'])) }}" 
           class="bg-gray-800 border {{ request('status') === 'checked_in' ? 'border-emerald-500 shadow-emerald-500/20 shadow-md' : 'border-gray-700' }} p-3 rounded-2xl flex items-center space-x-3 transition hover:border-gray-500">
            <div class="w-10 h-10 rounded-xl bg-emerald-900/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-check text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Sudah Naik</span>
                <span class="text-lg font-extrabold text-emerald-400">{{ $checkedInCount }}</span>
            </div>
        </a>

        <!-- Belum Naik -->
        <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'pending'])) }}" 
           class="bg-gray-800 border {{ request('status') === 'pending' ? 'border-amber-500 shadow-amber-500/20 shadow-md' : 'border-gray-700' }} p-3 rounded-2xl flex items-center space-x-3 transition hover:border-gray-500">
            <div class="w-10 h-10 rounded-xl bg-amber-900/60 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-clock text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Belum Naik</span>
                <span class="text-lg font-extrabold text-amber-400">{{ $pendingCount }}</span>
            </div>
        </a>

        <!-- Belum Selesai Trip (Aktif) -->
        <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'trip_active'])) }}" 
           class="bg-gray-800 border {{ request('status') === 'trip_active' ? 'border-indigo-500 shadow-indigo-500/20 shadow-md' : 'border-gray-700' }} p-3 rounded-2xl flex items-center space-x-3 transition hover:border-gray-500">
            <div class="w-10 h-10 rounded-xl bg-indigo-900/60 border border-indigo-500/30 text-indigo-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-van-shuttle text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Trip Berjalan</span>
                <span class="text-lg font-extrabold text-indigo-300">{{ $tripActiveCount }}</span>
            </div>
        </a>

        <!-- Sudah Selesai Trip -->
        <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'trip_completed'])) }}" 
           class="bg-gray-800 border {{ request('status') === 'trip_completed' ? 'border-purple-500 shadow-purple-500/20 shadow-md' : 'border-gray-700' }} p-3 rounded-2xl flex items-center space-x-3 col-span-2 sm:col-span-1 transition hover:border-gray-500">
            <div class="w-10 h-10 rounded-xl bg-purple-900/60 border border-purple-500/30 text-purple-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-flag-checkered text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Trip Selesai</span>
                <span class="text-lg font-extrabold text-purple-300">{{ $tripCompletedCount }}</span>
            </div>
        </a>
    </div>

    <!-- Filter Form & Responsive Category Tabs -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-3.5 space-y-3 shadow-md">
        <!-- Date Filter Controls -->
        <form method="GET" action="{{ route('driver.reports') }}" class="space-y-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs">
                        <i class="fa-regular fa-calendar"></i>
                    </span>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-gray-900 border border-gray-700 text-white text-xs rounded-xl pl-9 pr-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-filter text-[11px]"></i>
                        <span>Filter Tanggal</span>
                    </button>
                    @if(request('date') || request('status'))
                        <a href="{{ route('driver.reports') }}" class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-2 rounded-xl transition flex items-center justify-center" title="Reset Semua Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Responsive Horizontal Scrollable Category Filter Pills -->
            <div class="pt-1 overflow-x-auto no-scrollbar">
                <div class="flex items-center space-x-2 pb-1 text-xs whitespace-nowrap min-w-max">
                    <!-- Semua -->
                    <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => ''])) }}" 
                       class="px-3.5 py-1.5 rounded-xl font-bold transition flex items-center space-x-1.5 {{ !request('status') ? 'bg-emerald-600 text-white shadow' : 'bg-gray-900 text-gray-300 hover:bg-gray-700' }}">
                        <span>Semua</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ !request('status') ? 'bg-emerald-800 text-emerald-200' : 'bg-gray-800 text-gray-400' }}">{{ $totalTicketsCount }}</span>
                    </a>

                    <!-- Sudah Naik (Checked-in) -->
                    <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'checked_in'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl font-bold transition flex items-center space-x-1.5 {{ request('status') === 'checked_in' ? 'bg-emerald-600 text-white shadow' : 'bg-gray-900 text-emerald-400 hover:bg-gray-700' }}">
                        <i class="fa-solid fa-circle-check text-[11px]"></i>
                        <span>Sudah Naik</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ request('status') === 'checked_in' ? 'bg-emerald-800 text-emerald-200' : 'bg-gray-800 text-emerald-400' }}">{{ $checkedInCount }}</span>
                    </a>

                    <!-- Belum Naik (Pending) -->
                    <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'pending'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl font-bold transition flex items-center space-x-1.5 {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow' : 'bg-gray-900 text-amber-400 hover:bg-gray-700' }}">
                        <i class="fa-solid fa-clock text-[11px]"></i>
                        <span>Belum Naik</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ request('status') === 'pending' ? 'bg-amber-800 text-amber-200' : 'bg-gray-800 text-amber-400' }}">{{ $pendingCount }}</span>
                    </a>

                    <!-- Belum Selesai Trip (Aktif) -->
                    <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'trip_active'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl font-bold transition flex items-center space-x-1.5 {{ request('status') === 'trip_active' ? 'bg-indigo-600 text-white shadow' : 'bg-gray-900 text-indigo-400 hover:bg-gray-700' }}">
                        <i class="fa-solid fa-van-shuttle text-[11px]"></i>
                        <span>Belum Selesai Trip</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ request('status') === 'trip_active' ? 'bg-indigo-800 text-indigo-200' : 'bg-gray-800 text-indigo-400' }}">{{ $tripActiveCount }}</span>
                    </a>

                    <!-- Sudah Selesai Trip -->
                    <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'trip_completed'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl font-bold transition flex items-center space-x-1.5 {{ request('status') === 'trip_completed' ? 'bg-purple-600 text-white shadow' : 'bg-gray-900 text-purple-400 hover:bg-gray-700' }}">
                        <i class="fa-solid fa-flag-checkered text-[11px]"></i>
                        <span>Sudah Selesai Trip</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ request('status') === 'trip_completed' ? 'bg-purple-800 text-purple-200' : 'bg-gray-800 text-purple-400' }}">{{ $tripCompletedCount }}</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Booking / Ticket List -->
    <div class="space-y-3">
        @forelse($tickets as $ticket)
            @php
                $sch = $ticket->schedule;
                $isTripCompleted = ($sch?->status === 'COMPLETED');
            @endphp
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow-md hover:border-gray-600 transition">
                <!-- Card Header -->
                <div class="flex justify-between items-start border-b border-gray-700 pb-2.5 mb-3 flex-wrap gap-2">
                    <div>
                        <span class="text-[11px] font-mono text-emerald-400 font-bold tracking-wider block">
                            {{ $ticket->ticket_code }}
                        </span>
                        <span class="text-[10px] text-gray-400">
                            Booking: <strong class="text-gray-300">{{ $ticket->booking->booking_code }}</strong>
                        </span>
                    </div>

                    <div class="flex items-center space-x-1.5 flex-wrap gap-y-1">
                        <!-- Check-In / Naik Status Badge -->
                        @if($ticket->is_checked_in)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-700/60">
                                <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> Sudah Naik
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-950 text-amber-400 border border-amber-700/60">
                                <i class="fa-solid fa-clock mr-1 text-[9px]"></i> Belum Naik
                            </span>
                        @endif

                        <!-- Trip Status Badge -->
                        @if($isTripCompleted)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-950 text-purple-300 border border-purple-700/60">
                                <i class="fa-solid fa-flag-checkered mr-1 text-[9px]"></i> Trip Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-950 text-blue-300 border border-blue-700/60">
                                <i class="fa-solid fa-van-shuttle mr-1 text-[9px]"></i> {{ $sch?->status ?? 'AKTIF' }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Trip & Schedule Info -->
                <div class="bg-gray-900/60 border border-gray-700/50 rounded-xl p-3 mb-3 text-xs space-y-1.5">
                    <div class="flex justify-between items-center flex-wrap gap-1">
                        <span class="font-bold text-white">
                            <i class="fa-solid fa-route text-emerald-400 mr-1"></i>
                            {{ $sch?->route?->origin ?? '-' }} → {{ $sch?->route?->destination ?? '-' }}
                        </span>
                        <span class="bg-gray-800 text-gray-300 px-2 py-0.5 rounded text-[10px] font-semibold border border-gray-700">
                            Kursi {{ $ticket->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-gray-400 text-[11px] flex-wrap gap-1">
                        <span>
                            <i class="fa-regular fa-calendar-check mr-1 text-gray-500"></i>
                            {{ $sch?->departure_time ? $sch->departure_time->format('d M Y, H:i') : '-' }} WIB
                        </span>
                        <span>
                            <i class="fa-solid fa-van-shuttle mr-1 text-gray-500"></i>
                            {{ $sch?->vehicle?->name ?? 'Armada' }} ({{ $sch?->vehicle?->license_plate ?? '-' }})
                        </span>
                    </div>
                </div>

                <!-- Passenger Details & Quick Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs pt-1">
                    <div>
                        <p class="font-bold text-white text-sm">
                            <i class="fa-solid fa-user text-gray-400 mr-1"></i> {{ $ticket->passenger?->name ?? 'Penumpang' }}
                        </p>
                        <p class="text-[11px] text-gray-400">
                            Pemesan: <span class="text-gray-300">{{ $ticket->booking?->user?->name ?? '-' }}</span> | Tel: {{ $ticket->passenger?->phone ?? $ticket->booking?->user?->phone ?? '-' }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                        @php
                            $userPhone = $ticket->passenger?->phone ?? $ticket->booking?->user?->phone ?? null;
                            $waPhone = $userPhone ? preg_replace('/[^0-9]/', '', $userPhone) : null;
                            if ($waPhone && str_starts_with($waPhone, '0')) {
                                $waPhone = '62' . substr($waPhone, 1);
                            }
                        @endphp

                        @if($waPhone)
                            <a href="https://wa.me/{{ $waPhone }}?text=Halo%20{{ urlencode($ticket->passenger?->name ?? 'Penumpang') }},%20saya%20driver%20SIPP%20perjalanan%20{{ urlencode($sch?->route?->origin ?? '') }}%20ke%20{{ urlencode($sch?->route?->destination ?? '') }}." 
                               target="_blank" 
                               class="bg-emerald-950 hover:bg-emerald-900 border border-emerald-700/60 text-emerald-400 text-xs px-3 py-1.5 rounded-xl flex items-center transition shadow"
                               title="Hubungi Penumpang via WhatsApp">
                                <i class="fa-brands fa-whatsapp text-sm mr-1 text-emerald-400"></i> WA
                            </a>
                        @endif

                        @if(!$ticket->is_checked_in)
                            <form action="{{ route('driver.checkin', $ticket->id) }}" method="POST" onsubmit="return confirm('Validasi check-in untuk {{ $ticket->passenger?->name ?? 'Penumpang' }}?')">
                                @csrf
                                <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-3 py-1.5 rounded-xl transition shadow flex items-center">
                                    <i class="fa-solid fa-check mr-1 text-[10px]"></i> Check-In
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('driver.schedules.show', $ticket->schedule_id) }}" 
                           class="bg-gray-700 hover:bg-gray-600 text-gray-200 text-xs px-3 py-1.5 rounded-xl flex items-center transition">
                            <i class="fa-solid fa-eye mr-1 text-gray-400"></i> Detail
                        </a>
                    </div>
                </div>

                <!-- Check-in Timestamp Note -->
                @if($ticket->is_checked_in && $ticket->checked_in_at)
                    <div class="mt-3 pt-2 border-t border-gray-700/50 text-[10px] text-emerald-400 flex items-center justify-between">
                        <span><i class="fa-solid fa-circle-info mr-1"></i> Tervalidasi Naik oleh Pengemudi</span>
                        <span class="font-mono">{{ $ticket->checked_in_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 text-center text-gray-400 space-y-2">
                <i class="fa-solid fa-receipt text-3xl text-gray-600 mb-1"></i>
                <p class="text-sm font-semibold text-gray-300">Belum Ada Data Sesuai Filter</p>
                <p class="text-xs text-gray-400">Tidak ada tiket penumpang pada kategori filter yang dipilih.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
