@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $booking->booking_code . ' - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4">
    <div class="max-w-3xl mx-auto flex justify-between items-center">
        <div>
            <h1 class="text-lg font-bold">Detail Booking {{ $booking->booking_code }}</h1>
            <p class="text-xs text-blue-200">Dibuat pada: {{ $booking->created_at->format('d M Y H:i') }}</p>
        </div>
        <span class="text-xs bg-blue-700 text-white font-bold px-3 py-1.5 rounded-full border border-blue-600">
            {{ $booking->status }}
        </span>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    <!-- Trip Progress Stepper -->
    @php
        $statusOrder = ['WAITING' => 1, 'BOARDING' => 2, 'IN_TRANSIT' => 3, 'COMPLETED' => 4];
        $currentStep = $statusOrder[$booking->status] ?? 1;
    @endphp
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Status Perjalanan Real-Time</h2>
        <div class="flex justify-between items-center relative">
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-gray-200 -z-0"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-600 -z-0 transition-all duration-500" style="width: {{ (($currentStep - 1) / 3) * 100 }}%"></div>

            <!-- Step 1 -->
            <div class="flex flex-col items-center relative z-10">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 1 ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-200 text-gray-500' }}">1</div>
                <span class="text-[10px] font-bold mt-1 text-gray-700">Menunggu</span>
            </div>
            <!-- Step 2 -->
            <div class="flex flex-col items-center relative z-10">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 2 ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-200 text-gray-500' }}">2</div>
                <span class="text-[10px] font-bold mt-1 text-gray-700">Boarding</span>
            </div>
            <!-- Step 3 -->
            <div class="flex flex-col items-center relative z-10">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 3 ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-200 text-gray-500' }}">3</div>
                <span class="text-[10px] font-bold mt-1 text-gray-700">Dalam Jalan</span>
            </div>
            <!-- Step 4 -->
            <div class="flex flex-col items-center relative z-10">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 4 ? 'bg-emerald-600 text-white shadow-lg' : 'bg-gray-200 text-gray-500' }}">4</div>
                <span class="text-[10px] font-bold mt-1 text-gray-700">Selesai</span>
            </div>
        </div>
    </div>

    <!-- Booking Trips & Assigned Driver Details -->
    @foreach($booking->bookingTrips as $bt)
        @php 
            $sch = $bt->schedule;
            $driverUser = $sch?->driver?->user;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                        {{ $bt->direction === 'OUTBOUND' ? 'Trip Keberangkatan' : 'Trip Kepulangan' }}
                    </span>
                    <h3 class="font-bold text-gray-900 text-base mt-1">
                        {{ $sch?->route?->origin ?? '-' }} → {{ $sch?->route?->destination ?? '-' }}
                    </h3>
                </div>
                <div class="text-right text-xs text-gray-600">
                    <p><i class="fa-regular fa-clock mr-1"></i> {{ $sch?->departure_time ? $sch->departure_time->format('d M Y - H:i') : '-' }} WIB</p>
                    <p><i class="fa-solid fa-van-shuttle mr-1"></i> {{ $sch?->vehicle?->name ?? 'Armada Travel' }} ({{ $sch?->vehicle?->license_plate ?? '-' }})</p>
                </div>
            </div>

            <!-- Assigned Driver Card -->
            <div class="bg-gradient-to-r from-slate-900 to-blue-950 text-white rounded-xl p-4 flex justify-between items-center shadow">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-emerald-400 font-bold uppercase block tracking-wider">Driver Penanggung Jawab</span>
                        <h4 class="font-bold text-sm text-white">{{ $driverUser?->name ?? 'TBA' }}</h4>
                        <p class="text-xs text-gray-300 font-mono"><i class="fa-solid fa-phone text-[10px] mr-1"></i> {{ $driverUser?->phone ?? '-' }}</p>
                    </div>
                </div>

                @if($driverUser?->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $driverUser->phone) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 shadow">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span class="hidden sm:inline">Hubungi Driver</span>
                    </a>
                @endif
            </div>

            <!-- E-Tickets List -->
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider pt-2">E-Ticket Penumpang</h4>
            <div class="space-y-3">
                @php
                    $tripTickets = $booking->tickets->where('schedule_id', $bt->schedule_id);
                    if ($tripTickets->isEmpty()) {
                        $tripTickets = $booking->tickets;
                    }
                @endphp

                @foreach($tripTickets as $tkt)
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <span class="font-bold text-gray-900 text-sm block">{{ $tkt->passenger?->name ?? 'Penumpang' }}</span>
                            <span class="text-xs text-gray-500">Nomor Kursi: <strong class="text-emerald-600 font-bold text-sm">{{ $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}</strong></span>
                        </div>
                        <div class="flex items-center space-x-3 w-full sm:w-auto">
                            @if($tkt->is_checked_in)
                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-300 px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-circle-check"></i> Checked-In oleh Driver
                                </span>
                            @else
                                <span class="text-[11px] font-bold text-amber-700 bg-amber-100 border border-amber-300 px-2.5 py-1 rounded-full">
                                    Belum Check-In
                                </span>
                            @endif
                            <a href="{{ route('customer.tickets.show', $tkt->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow">
                                Lihat Tiket & QR
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Cancel Order Action (If allowed) -->
    @if(in_array($booking->status, ['PENDING_PAYMENT', 'PAID', 'CONFIRMED', 'WAITING_DEPARTURE']))
        <div class="pt-4 text-center">
            <form action="{{ route('customer.orders.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                @csrf
                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold underline">
                    Batalkan Pemesanan Ini
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
