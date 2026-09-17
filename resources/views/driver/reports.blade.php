@extends('layouts.driver')

@section('title', 'Laporan Pemesanan - SIPP Driver')

@section('content')
<div class="space-y-4">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-lg font-bold text-white">Laporan Pemesanan</h1>
            <p class="text-xs text-gray-400">Riwayat tiket & penumpang pada armada Anda</p>
        </div>
        <a href="{{ route('driver.scan') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center shadow transition">
            <i class="fa-solid fa-qrcode mr-1.5"></i> Scan QR
        </a>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-2 gap-2.5">
        <div class="bg-gray-800 border border-gray-700 p-3 rounded-xl flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-blue-900/60 border border-blue-500/30 text-blue-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-ticket text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Total Tiket</span>
                <span class="text-lg font-extrabold text-white">{{ $totalTicketsCount }}</span>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 p-3 rounded-xl flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-900/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-check text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Check-In</span>
                <span class="text-lg font-extrabold text-emerald-400">{{ $checkedInCount }}</span>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 p-3 rounded-xl flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-amber-900/60 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-clock text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Belum Naik</span>
                <span class="text-lg font-extrabold text-amber-400">{{ $pendingCount }}</span>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 p-3 rounded-xl flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-purple-900/60 border border-purple-500/30 text-purple-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-flag-checkered text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Trip Selesai</span>
                <span class="text-lg font-extrabold text-purple-300">{{ $completedSchedulesCount }}/{{ $schedulesCount }}</span>
            </div>
        </div>
    </div>

    <!-- Filter Form & Tabs -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 space-y-2">
        <form method="GET" action="{{ route('driver.reports') }}" class="space-y-2">
            <div class="flex items-center space-x-2">
                <input type="date" name="date" value="{{ request('date') }}" class="bg-gray-900 border border-gray-700 text-white text-xs rounded-lg px-3 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:outline-none flex-grow">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request('date') || request('status'))
                    <a href="{{ route('driver.reports') }}" class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-1.5 rounded-lg transition" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

            <!-- Status Tabs -->
            <div class="flex border-b border-gray-700 text-xs pt-1">
                <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => ''])) }}" 
                   class="pb-2 px-3 font-semibold border-b-2 {{ !request('status') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                    Semua ({{ $totalTicketsCount }})
                </a>
                <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'checked_in'])) }}" 
                   class="pb-2 px-3 font-semibold border-b-2 {{ request('status') == 'checked_in' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                    Sudah Check-In ({{ $checkedInCount }})
                </a>
                <a href="{{ route('driver.reports', array_merge(request()->query(), ['status' => 'pending'])) }}" 
                   class="pb-2 px-3 font-semibold border-b-2 {{ request('status') == 'pending' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                    Belum Check-In ({{ $pendingCount }})
                </a>
            </div>
        </form>
    </div>

    <!-- Booking / Ticket List -->
    <div class="space-y-3">
        @forelse($tickets as $ticket)
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow-md hover:border-gray-600 transition">
                <!-- Card Header -->
                <div class="flex justify-between items-start border-b border-gray-700 pb-2 mb-3">
                    <div>
                        <span class="text-[11px] font-mono text-emerald-400 font-bold tracking-wider block">
                            {{ $ticket->ticket_code }}
                        </span>
                        <span class="text-[10px] text-gray-400">
                            Booking: <strong class="text-gray-300">{{ $ticket->booking->booking_code }}</strong>
                        </span>
                    </div>
                    <div>
                        @if($ticket->is_checked_in)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-700/60">
                                <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> Check-In
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-950 text-amber-400 border border-amber-700/60">
                                <i class="fa-solid fa-clock mr-1 text-[9px]"></i> Belum Check-In
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Trip & Schedule Info -->
                <div class="bg-gray-900/60 border border-gray-700/50 rounded-xl p-3 mb-3 text-xs space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-white">
                            <i class="fa-solid fa-route text-emerald-400 mr-1"></i>
                            {{ $ticket->schedule->route->origin }} → {{ $ticket->schedule->route->destination }}
                        </span>
                        <span class="bg-gray-800 text-gray-300 px-2 py-0.5 rounded text-[10px] font-semibold border border-gray-700">
                            Kursi {{ $ticket->bookingSeat->vehicleSeat->seat_number ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-gray-400 text-[11px]">
                        <span>
                            <i class="fa-regular fa-calendar-check mr-1 text-gray-500"></i>
                            {{ $ticket->schedule->departure_time->format('d M Y, H:i') }} WIB
                        </span>
                        <span>
                            <i class="fa-solid fa-bus mr-1 text-gray-500"></i>
                            {{ $ticket->schedule->vehicle->name }}
                        </span>
                    </div>
                </div>

                <!-- Passenger Details -->
                <div class="flex justify-between items-center text-xs pt-1">
                    <div>
                        <p class="font-bold text-white text-sm">
                            <i class="fa-solid fa-user text-gray-400 mr-1"></i> {{ $ticket->passenger->name }}
                        </p>
                        <p class="text-[11px] text-gray-400">
                            Pemesan: {{ $ticket->booking->user->name ?? '-' }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        @php
                            $userPhone = $ticket->booking->user->phone ?? null;
                            $waPhone = $userPhone ? preg_replace('/[^0-9]/', '', $userPhone) : null;
                            if ($waPhone && str_starts_with($waPhone, '0')) {
                                $waPhone = '62' . substr($waPhone, 1);
                            }
                        @endphp

                        @if($waPhone)
                            <a href="https://wa.me/{{ $waPhone }}?text=Halo%20{{ urlencode($ticket->passenger->name) }},%20saya%20driver%20SIPP%20perjalanan%20{{ urlencode($ticket->schedule->route->origin) }}%20ke%20{{ urlencode($ticket->schedule->route->destination) }}." 
                               target="_blank" 
                               class="bg-emerald-950 hover:bg-emerald-900 border border-emerald-700/60 text-emerald-400 text-xs px-2.5 py-1.5 rounded-lg flex items-center transition"
                               title="Hubungi Penumpang via WhatsApp">
                                <i class="fa-brands fa-whatsapp text-sm mr-1 text-emerald-400"></i> WA
                            </a>
                        @endif

                        <a href="{{ route('driver.schedules.show', $ticket->schedule_id) }}" 
                           class="bg-gray-700 hover:bg-gray-600 text-gray-200 text-xs px-2.5 py-1.5 rounded-lg flex items-center transition">
                            <i class="fa-solid fa-eye mr-1 text-gray-400"></i> Detail
                        </a>
                    </div>
                </div>

                <!-- Check-in Timestamp Note -->
                @if($ticket->is_checked_in && $ticket->checked_in_at)
                    <div class="mt-3 pt-2 border-t border-gray-700/50 text-[10px] text-emerald-400 flex items-center justify-between">
                        <span><i class="fa-solid fa-circle-info mr-1"></i> Tervalidasi oleh pengemudi</span>
                        <span class="font-mono">{{ $ticket->checked_in_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 text-center text-gray-400 space-y-2">
                <i class="fa-solid fa-receipt text-3xl text-gray-600 mb-1"></i>
                <p class="text-sm font-semibold text-gray-300">Belum Ada Data Laporan Pemesanan</p>
                <p class="text-xs text-gray-400">Tidak ada data tiket penumpang yang sesuai dengan kriteria filter saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
