@extends('layouts.driver')

@section('title', 'Jadwal Penugasan - SIPP Driver')

@section('content')
<div class="space-y-4">
    <h1 class="text-base font-bold text-white mb-2">Semua Penugasan Perjalanan</h1>

    <div class="space-y-3">
        @forelse($schedules as $sch)
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 shadow">
                <div class="flex justify-between items-start border-b border-gray-700 pb-2 mb-2">
                    <div>
                        <span class="text-xs font-bold text-emerald-400">
                            {{ $sch->route->origin }} → {{ $sch->route->destination }}
                        </span>
                        <h3 class="font-bold text-white text-sm mt-0.5">{{ $sch->departure_time->format('d M Y - H:i') }} WIB</h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-900 text-gray-300 border border-gray-700">
                        {{ $sch->status }}
                    </span>
                </div>

                <div class="text-xs text-gray-400 mb-3">
                    <p>Armada: {{ $sch->vehicle->name }} ({{ $sch->vehicle->license_plate }})</p>
                </div>

                <a href="{{ route('driver.schedules.show', $sch->id) }}" class="block w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-center text-xs py-2 rounded-xl shadow transition">
                    Buka Manifest Trip
                </a>
            </div>
        @empty
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 text-center text-gray-400 text-xs">
                Belum ada penugasan perjalanan.
            </div>
        @endforelse
    </div>
</div>
@endsection
