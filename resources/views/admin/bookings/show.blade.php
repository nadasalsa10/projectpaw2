@extends('layouts.admin')

@section('title', 'Detail Booking ' . $booking->booking_code . ' - SIPP Admin')
@section('page_title', 'Detail & Manajemen Booking ' . $booking->booking_code)

@section('content')
<div class="space-y-6">
    <!-- Status Override Header -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-xs text-gray-400 font-mono block">Master Booking Code: {{ $booking->booking_code }}</span>
            <h2 class="text-xl font-bold text-gray-900 mt-1">Pemesan: {{ $booking->user->name }} ({{ $booking->user->email }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Telepon: {{ $booking->user->phone }}</p>
        </div>

        <form action="{{ route('admin.bookings.status', $booking->id) }}" method="POST" class="flex items-center space-x-2">
            @csrf
            @method('PUT')
            <select name="status" class="bg-gray-50 border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-gray-800">
                @foreach(['PENDING_PAYMENT', 'PAYMENT_PROCESSING', 'PAID', 'CONFIRMED', 'WAITING_DEPARTURE', 'BOARDING', 'IN_TRANSIT', 'COMPLETED', 'CANCELLED', 'EXPIRED'] as $st)
                    <option value="{{ $st }}" {{ $booking->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow">
                Update Status
            </button>
        </form>
    </div>

    <!-- Trips & Seats Detail -->
    @foreach($booking->bookingTrips as $bt)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-3">
            <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2">
                {{ $bt->direction === 'OUTBOUND' ? 'Trip Keberangkatan' : 'Trip Kepulangan' }}: {{ $bt->schedule->route->origin }} → {{ $bt->schedule->route->destination }}
            </h3>
            <div class="text-xs text-gray-600 space-y-1">
                <p>Jadwal: {{ $bt->schedule->departure_time->format('d M Y - H:i') }} WIB</p>
                <p>Armada: {{ $bt->schedule->vehicle->name }} ({{ $bt->schedule->vehicle->license_plate }})</p>
                <p>Driver: {{ $bt->schedule->driver?->user->name ?? 'TBA' }}</p>
            </div>

            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider pt-2">Manifest Penumpang & E-Tickets</h4>
            <div class="space-y-2">
                @foreach($booking->tickets->where('schedule_id', $bt->schedule_id) as $tkt)
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs flex justify-between items-center">
                        <div>
                            <span class="font-bold text-gray-900">{{ $tkt->passenger->name }}</span>
                            <span class="text-gray-500 block text-[10px]">Tiket Code: {{ $tkt->ticket_code }} | Kursi {{ $tkt->bookingSeat->vehicleSeat->seat_number }}</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold {{ $tkt->is_checked_in ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $tkt->is_checked_in ? 'Checked-In' : 'Belum Check-In' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
