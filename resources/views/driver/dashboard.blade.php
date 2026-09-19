@extends('layouts.driver')

@section('title', 'Dashboard Driver - SIPP')

@section('content')
<div class="space-y-4">
    <!-- Driver Info Banner -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Pengemudi Resmi SIPP</span>
                <h2 class="text-base font-bold text-white">{{ Auth::user()->name }}</h2>
                <p class="text-xs text-emerald-400 font-mono mt-0.5"><i class="fa-solid fa-id-card text-[10px] mr-1"></i> SIM: {{ $driver->license_number }}</p>
            </div>
            <span class="text-xs bg-emerald-900/60 text-emerald-300 border border-emerald-600/40 px-3 py-1 rounded-full font-bold">
                {{ $driver->status }}
            </span>
        </div>
    </div>

    <!-- Live Customer Booking Alert (Real-Time Notification of New Customer Orders) -->
    @if($recentCustomerBookings->count() > 0)
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-gray-800 border-2 border-blue-500/60 rounded-2xl p-4 shadow-xl">
            <div class="flex justify-between items-center mb-3">
                <span class="text-[10px] bg-blue-500 text-white px-2.5 py-0.5 rounded-full font-black uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-bell animate-bounce mr-1.5 text-xs"></i> Pesanan Penumpang Terbaru
                </span>
                <span class="text-[10px] text-blue-200 font-mono">{{ $recentCustomerBookings->count() }} Penumpang Masuk</span>
            </div>

            <div class="space-y-2">
                @foreach($recentCustomerBookings as $tkt)
                    <div class="bg-gray-900/80 border border-blue-700/50 rounded-xl p-3 flex justify-between items-center text-xs">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="bg-emerald-600 text-white font-black px-2 py-0.5 rounded text-[10px]">
                                    Kursi {{ $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}
                                </span>
                                <span class="font-bold text-white">{{ $tkt->passenger?->name ?? 'Penumpang' }}</span>
                            </div>
                            <span class="text-[10px] text-blue-200 block mt-1">
                                {{ $tkt->schedule?->route?->origin ?? '-' }} → {{ $tkt->schedule?->route?->destination ?? '-' }} | {{ $tkt->schedule?->departure_time ? $tkt->schedule->departure_time->format('d M H:i') : '-' }} WIB
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] text-gray-400 font-mono block">{{ $tkt->booking?->booking_code ?? '-' }}</span>
                            @if($tkt->is_checked_in)
                                <span class="text-[10px] text-emerald-400 font-bold"><i class="fa-solid fa-check"></i> Checked-In</span>
                            @else
                                <span class="text-[10px] text-amber-400 font-bold">Siap Boarding</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Active Trip Banner (If any) -->
    @if($activeTrip)
        <div class="bg-gradient-to-r from-emerald-900 via-green-950 to-gray-800 border-2 border-emerald-500/60 rounded-2xl p-4 shadow-xl">
            <span class="text-[10px] bg-emerald-500 text-gray-950 px-2.5 py-0.5 rounded-full font-black uppercase tracking-wider mb-2 inline-block">
                Perjalanan Sedang Berjalan
            </span>
            <h3 class="text-lg font-black text-white">
                {{ $activeTrip->route->origin }} <i class="fa-solid fa-arrow-right text-xs mx-1 text-emerald-400"></i> {{ $activeTrip->route->destination }}
            </h3>
            <p class="text-xs text-gray-300 mt-1">
                Jam: {{ $activeTrip->departure_time->format('H:i') }} WIB | Armada: {{ $activeTrip->vehicle->name }} ({{ $activeTrip->vehicle->license_plate }})
            </p>

            <div class="mt-4 pt-3 border-t border-emerald-800/60 flex justify-between items-center">
                <a href="{{ route('driver.schedules.show', $activeTrip->id) }}" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow">
                    Buka Manifest & Status
                </a>
                <a href="{{ route('driver.scan') }}" class="bg-gray-800 text-emerald-400 border border-emerald-600/50 hover:bg-gray-700 font-bold text-xs px-3.5 py-2 rounded-xl transition">
                    <i class="fa-solid fa-qrcode mr-1"></i> Scan QR
                </a>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('driver.scan') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white p-4 rounded-2xl shadow-lg text-center transition flex flex-col items-center justify-center">
            <i class="fa-solid fa-qrcode text-2xl mb-1"></i>
            <span class="text-xs font-extrabold">Scan QR Tiket</span>
            <span class="text-[10px] text-emerald-100 font-medium">Validasi Check-In</span>
        </a>
        <a href="{{ route('driver.schedules') }}" class="bg-gray-800 hover:bg-gray-750 text-gray-200 border border-gray-700 p-4 rounded-2xl shadow text-center transition flex flex-col items-center justify-center">
            <i class="fa-solid fa-calendar-days text-2xl text-emerald-400 mb-1"></i>
            <span class="text-xs font-bold">Jadwal Tugas</span>
            <span class="text-[10px] text-gray-400">Daftar Trip Saya</span>
        </a>
    </div>

    <!-- Today's Schedule & Passenger Count Cards -->
    <div>
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-1">Jadwal Penugasan & Manifest Penumpang</h3>

        <div class="space-y-3">
            @forelse($todaySchedules as $sch)
                @php
                    $checkedInCount = $sch->tickets->where('is_checked_in', true)->count();
                    $totalBookedCount = $sch->tickets->count();
                @endphp
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow">
                    <div class="flex justify-between items-start border-b border-gray-700 pb-2.5 mb-2.5">
                        <div>
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">
                                {{ $sch->route->origin }} → {{ $sch->route->destination }}
                            </span>
                            <h4 class="font-extrabold text-white text-base mt-1">{{ $sch->departure_time->format('d M - H:i') }} WIB</h4>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-gray-900 border border-gray-600 text-gray-300">
                            {{ $sch->status }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-400 space-y-1 mb-3">
                        <p><i class="fa-solid fa-van-shuttle mr-1 text-emerald-400"></i> Armada: <strong class="text-white">{{ $sch->vehicle->name }}</strong> ({{ $sch->vehicle->license_plate }})</p>
                        <p><i class="fa-solid fa-users mr-1 text-blue-400"></i> Total Penumpang Memesan: <strong class="text-emerald-400 font-bold text-sm">{{ $totalBookedCount }}</strong> / {{ $sch->vehicle->capacity }} Kursi</p>
                        <p><i class="fa-solid fa-circle-check mr-1 text-emerald-400"></i> Sudah Check-In: <strong class="text-white">{{ $checkedInCount }} Penumpang</strong></p>
                    </div>

                    <!-- Passenger Manifest Quick Cards -->
                    @if($totalBookedCount > 0)
                        <div class="bg-gray-900/60 p-2.5 rounded-xl border border-gray-700/60 mb-3 space-y-1.5">
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block">Daftar Penumpang Mobil Ini:</span>
                            @foreach($sch->tickets as $tkt)
                                <div class="flex justify-between items-center text-xs py-1 border-b border-gray-800 last:border-0">
                                    <span class="text-gray-200 font-medium"><strong class="text-emerald-400">[{{ $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}]</strong> {{ $tkt->passenger?->name ?? 'Penumpang' }}</span>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ $tkt->passenger?->phone ?? 'No HP' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('driver.schedules.show', $sch->id) }}" class="block w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-center text-xs py-2.5 rounded-xl transition shadow">
                        Buka Detail Manifest & Update Status Trip
                    </a>
                </div>
            @empty
                <div class="bg-gray-800/50 border border-gray-700/60 rounded-2xl p-6 text-center text-gray-400 text-xs">
                    <i class="fa-solid fa-calendar-check text-2xl mb-2 text-gray-500 block"></i>
                    Tidak ada jadwal penugasan perjalanan untuk Anda saat ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
