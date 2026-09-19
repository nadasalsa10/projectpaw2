@extends('layouts.app')

@section('title', 'E-Ticket ' . $ticket->ticket_code . ' - SIPP')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl border-2 border-blue-900 shadow-2xl overflow-hidden relative">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white p-6 relative">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] bg-blue-500/40 border border-blue-300/30 px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-blue-200">Official E-Ticket SIPP</span>
                    <h1 class="text-2xl font-black mt-2 tracking-wider">SIPP TRAVEL</h1>
                    <p class="text-xs text-blue-200">Sistem Informasi Pulang Pergi</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-blue-200 block">Kode Tiket</span>
                    <span class="font-mono font-black text-sm text-yellow-300">{{ $ticket->ticket_code }}</span>
                </div>
            </div>
        </div>

        <!-- Ticket Main Body -->
        <div class="p-6">
            <!-- QR Code Section -->
            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-4 text-center mb-6 flex flex-col items-center">
                <span class="text-xs font-bold text-gray-700 mb-2">Scan QR Ini Kepada Driver Saat Boarding</span>
                <div class="bg-white p-3 rounded-2xl shadow-md border border-gray-200">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($ticket->qr_code_hash ?? $ticket->ticket_code) }}" alt="QR Code Ticket" class="w-40 h-40 object-contain mx-auto">
                </div>
                <span class="text-[10px] text-gray-400 font-mono mt-2 truncate w-full px-4">Hash Verification: {{ substr($ticket->qr_code_hash ?? $ticket->ticket_code, 0, 24) }}...</span>
            </div>

            <!-- Route Banner -->
            <div class="border-y border-dashed border-gray-300 py-4 mb-6">
                <div class="flex justify-between items-center text-center">
                    <div class="text-left">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">KOTA ASAL</span>
                        <span class="text-lg font-black text-blue-900">{{ $ticket->schedule?->route?->origin ?? 'Palembang' }}</span>
                    </div>
                    <div>
                        <i class="fa-solid fa-van-shuttle text-blue-600 text-xl"></i>
                        @if($ticket->schedule?->route?->duration_minutes)
                            <span class="text-[10px] block text-gray-400 font-semibold">{{ floor(($ticket->schedule?->route?->duration_minutes ?? 0) / 60) }}j {{ ($ticket->schedule?->route?->duration_minutes ?? 0) % 60 }}m</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block">KOTA TUJUAN</span>
                        <span class="text-lg font-black text-blue-900">{{ $ticket->schedule?->route?->destination ?? 'Tujuan' }}</span>
                    </div>
                </div>
            </div>

            <!-- Ticket Information Details -->
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block font-semibold">Nama Penumpang:</span>
                    <span class="font-bold text-gray-900 text-sm block">{{ $ticket->passenger?->name ?? 'Penumpang' }}</span>
                    <span class="text-gray-500 block text-[10px]">{{ $ticket->passenger?->phone ?? '-' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-gray-400 block font-semibold">Nomor Kursi:</span>
                    <span class="font-black text-lg text-emerald-600 bg-emerald-50 border border-emerald-200 px-3 py-0.5 rounded-lg inline-block">
                        {{ $ticket->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-400 block font-semibold">Waktu Keberangkatan:</span>
                    <span class="font-bold text-gray-900 block">{{ $ticket->schedule?->departure_time ? $ticket->schedule->departure_time->format('d M Y') : '-' }}</span>
                    <span class="font-black text-blue-700 text-sm block">{{ $ticket->schedule?->departure_time ? $ticket->schedule->departure_time->format('H:i') : '-' }} WIB</span>
                </div>
                <div class="text-right">
                    <span class="text-gray-400 block font-semibold">Armada Mobil:</span>
                    <span class="font-bold text-gray-900 block">{{ $ticket->schedule?->vehicle?->name ?? 'Armada Travel' }}</span>
                    <span class="text-gray-500 block text-[10px]">{{ $ticket->schedule?->vehicle?->license_plate ?? '-' }}</span>
                </div>

                <div>
                    <span class="text-gray-400 block font-semibold">Driver / Supir:</span>
                    <span class="font-bold text-gray-900 block">{{ $ticket->schedule?->driver?->user?->name ?? 'TBA' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-gray-400 block font-semibold">Status Check-In:</span>
                    @if($ticket->is_checked_in)
                        <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[10px] inline-block"><i class="fa-solid fa-check"></i> Checked-In</span>
                    @else
                        <span class="font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-[10px] inline-block">Belum Check-In</span>
                    @endif
                </div>
            </div>

            <!-- Print Action -->
            <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center">
                <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs px-4 py-2 rounded-xl transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Tiket</span>
                </button>
                @if($ticket->booking_id)
                    <a href="{{ route('customer.orders.show', $ticket->booking_id) }}" class="text-xs text-blue-600 font-bold hover:underline">
                        Kembali Ke Detail Pesanan
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
