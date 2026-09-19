@extends('layouts.driver')

@section('title', 'Manifest Trip ' . $schedule->route->origin . ' - SIPP Driver')

@section('content')
<div class="space-y-4">
    <!-- Header Trip Card -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow">
        <div class="flex justify-between items-start border-b border-gray-700 pb-3 mb-3">
            <div>
                <span class="text-xs font-bold text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">
                    {{ $schedule->route->origin }} → {{ $schedule->route->destination }}
                </span>
                <h2 class="text-lg font-black text-white mt-1">{{ $schedule->departure_time->format('d M Y - H:i') }} WIB</h2>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-900 text-emerald-300 border border-emerald-700">
                {{ $schedule->status }}
            </span>
        </div>

        <div class="text-xs text-gray-300 space-y-1">
            <p><i class="fa-solid fa-van-shuttle text-emerald-400 mr-1.5"></i> Armada: <strong class="text-white">{{ $schedule->vehicle->name }}</strong> ({{ $schedule->vehicle->license_plate }})</p>
            <p><i class="fa-solid fa-clock text-gray-400 mr-1.5"></i> Estimasi Tiba: {{ $schedule->arrival_time->format('H:i') }} WIB</p>
        </div>

        <!-- Trip Status Advance Controls -->
        <div class="mt-4 pt-3 border-t border-gray-700">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block mb-2">Kontrol Status Perjalanan</span>
            <form action="{{ route('driver.trip.status', $schedule->id) }}" method="POST" class="grid grid-cols-2 gap-2">
                @csrf
                <button type="submit" name="status" value="BOARDING" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2 rounded-xl transition shadow">
                    [ BOARDING ]
                </button>
                <button type="submit" name="status" value="IN_TRANSIT" class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs py-2 rounded-xl transition shadow">
                    [ IN TRANSIT ]
                </button>
                <button type="submit" name="status" value="ARRIVED" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs py-2 rounded-xl transition shadow">
                    [ TIBA DI TUJUAN ]
                </button>
                <button type="submit" name="status" value="COMPLETED" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs py-2 rounded-xl transition shadow">
                    [ SELESAI TRIP ]
                </button>
            </form>
        </div>
    </div>

    <!-- Passenger Manifest List -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow">
        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-700">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">
                <i class="fa-solid fa-users text-emerald-400 mr-1.5"></i> Daftar Penumpang ({{ $schedule->tickets->count() }})
            </h3>
            <a href="{{ route('driver.scan') }}" class="text-[11px] bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1 rounded-lg">
                + Scan QR Check-in
            </a>
        </div>

        <div class="space-y-2">
            @forelse($schedule->tickets as $tkt)
                <div class="bg-gray-900 border border-gray-700/80 rounded-xl p-3 flex justify-between items-center">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-900 text-emerald-300 font-extrabold text-xs flex items-center justify-center border border-emerald-700">
                                {{ $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}
                            </span>
                            <span class="font-bold text-white text-sm">{{ $tkt->passenger?->name ?? 'Penumpang' }}</span>
                        </div>
                        <span class="text-[10px] text-gray-400 block mt-0.5 ml-8">Booking: {{ $tkt->booking?->booking_code ?? '-' }} | Tel: {{ $tkt->passenger?->phone ?? '-' }}</span>
                    </div>

                    <div>
                        @if($tkt->is_checked_in)
                            <span class="text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-700 px-2.5 py-1 rounded-full">
                                Checked-In
                            </span>
                        @else
                            <form action="{{ route('driver.checkin', $tkt->id) }}" method="POST" onsubmit="return confirm('Validasi check-in untuk {{ $tkt->passenger?->name ?? 'Penumpang' }}?')">
                                @csrf
                                <button type="submit" class="text-[10px] bg-amber-600 hover:bg-amber-500 text-white font-bold px-2.5 py-1 rounded-full shadow">
                                    [ CHECK-IN ]
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-xs text-gray-500">
                    Belum ada penumpang terdaftar di trip ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
