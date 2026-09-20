@extends('layouts.driver')

@section('title', 'Manifest Trip ' . $schedule->route->origin . ' - SIPP Driver')

@section('content')
<div class="space-y-4" x-data="driverManifest()" x-init="startPolling()">
    <!-- Top Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('driver.reports') }}" class="inline-flex items-center text-xs font-bold text-gray-300 hover:text-white bg-gray-800 border border-gray-700 hover:bg-gray-700 px-3 py-1.5 rounded-xl transition shadow">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Laporan
        </a>
        <div class="flex items-center space-x-2">
            <span class="flex items-center text-[10px] text-emerald-400 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping mr-1.5"></span> Live Sync
            </span>
            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center text-xs font-bold text-emerald-400 hover:text-emerald-300">
                <i class="fa-solid fa-gauge mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Header Trip Card -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow">
        <div class="flex justify-between items-start border-b border-gray-700 pb-3 mb-3">
            <div>
                <span class="text-xs font-bold text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">
                    {{ $schedule->route->origin }} → {{ $schedule->route->destination }}
                </span>
                <h2 class="text-lg font-black text-white mt-1">{{ $schedule->departure_time->format('d M Y - H:i') }} WIB</h2>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-900 text-emerald-300 border border-emerald-700" x-text="tripStatus">
                {{ $schedule->status }}
            </span>
        </div>

        <div class="text-xs text-gray-300 space-y-1">
            <p><i class="fa-solid fa-van-shuttle text-emerald-400 mr-1.5"></i> Armada: <strong class="text-white">{{ $schedule->vehicle->name }}</strong> ({{ $schedule->vehicle->license_plate }})</p>
            <p><i class="fa-solid fa-clock text-gray-400 mr-1.5"></i> Estimasi Tiba: {{ $schedule->arrival_time->format('H:i') }} WIB</p>
        </div>

        <!-- Trip Status Advance Controls -->
        <div class="mt-4 pt-3 border-t border-gray-700">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block mb-2">Kontrol Status Perjalanan (Otomatis Sync ke Penumpang)</span>
            <form action="{{ route('driver.trip.status', $schedule->id) }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
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
                <i class="fa-solid fa-users text-emerald-400 mr-1.5"></i> Manifest Penumpang 
                <span class="text-emerald-400 font-mono" x-text="'(' + tickets.length + ' Orang)'">({{ $schedule->tickets->count() }})</span>
            </h3>
            <a href="{{ route('driver.scan') }}" class="text-[11px] bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1 rounded-lg">
                + Scan QR Check-in
            </a>
        </div>

        <template x-if="tickets.length > 0">
            <div class="space-y-2">
                <template x-for="tkt in tickets" :key="tkt.id">
                    <div class="bg-gray-900 border border-gray-700/80 rounded-xl p-3 flex justify-between items-center transition hover:border-gray-500">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-lg bg-emerald-900 text-emerald-300 font-extrabold text-xs flex items-center justify-center border border-emerald-700" x-text="tkt.seat_number"></span>
                                <span class="font-bold text-white text-sm" x-text="tkt.passenger_name"></span>
                            </div>
                            <span class="text-[10px] text-gray-400 block mt-0.5 ml-8" x-text="'Booking: ' + tkt.booking_code + ' | Tel: ' + tkt.passenger_phone"></span>
                        </div>

                        <div>
                            <template x-if="tkt.is_checked_in">
                                <span class="text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-700 px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Checked-In
                                </span>
                            </template>
                            <template x-if="!tkt.is_checked_in">
                                <form :action="'/driver/checkin/' + tkt.id" method="POST" @submit="return confirm('Validasi check-in untuk ' + tkt.passenger_name + '?')">
                                    @csrf
                                    <button type="submit" class="text-[10px] bg-amber-600 hover:bg-amber-500 text-white font-bold px-2.5 py-1 rounded-full shadow">
                                        [ CHECK-IN ]
                                    </button>
                                </form>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="tickets.length === 0">
            <div class="text-center py-6 text-xs text-gray-500">
                Belum ada penumpang terdaftar di trip ini.
            </div>
        </template>
    </div>
</div>

<script>
function driverManifest() {
    return {
        tripStatus: @js($schedule->status),
        tickets: @js($manifestArray),
        pollTimer: null,

        startPolling() {
            this.pollTimer = setInterval(() => {
                this.fetchManifest();
            }, 3000);
        },

        async fetchManifest() {
            try {
                const res = await fetch("{{ route('driver.api.live_manifest', $schedule->id) }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.tripStatus = data.status;
                        this.tickets = data.tickets;
                    }
                }
            } catch (err) {
                console.error("Manifest live sync error:", err);
            }
        }
    };
}
</script>
@endsection
