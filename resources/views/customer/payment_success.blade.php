@extends('layouts.app')

@section('title', 'Pembayaran Berhasil - SIPP')

@section('content')
<div class="max-w-xl mx-auto px-4 py-12 text-center">
    <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl p-8">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 animate-bounce">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <span class="bg-emerald-100 text-emerald-800 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider">Pembayaran Lunas</span>
        <h1 class="text-2xl font-black text-gray-900 mt-2">Pemesanan Travel Berhasil!</h1>
        <p class="text-xs text-gray-500 mt-1">Kode Booking: <strong class="text-gray-900 font-mono text-sm">{{ $booking->booking_code }}</strong></p>

        <div class="my-6 p-4 bg-gray-50 rounded-2xl border border-gray-100 text-left space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-500">Jumlah Penumpang:</span>
                <span class="font-bold text-gray-800">{{ $booking->tickets->count() }} Penumpang</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total Pembayaran:</span>
                <span class="font-bold text-emerald-600">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Metode Bayar:</span>
                <span class="font-bold text-gray-800">{{ $booking->payment->payment_method }}</span>
            </div>
        </div>

        <!-- E-Tickets Access Cards -->
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 text-left">E-Ticket & QR Code Tiket Anda</h3>
        <div class="space-y-2 mb-6">
            @foreach($booking->tickets as $tkt)
                <a href="{{ route('customer.tickets.show', $tkt->id) }}" class="block bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl p-3 text-left transition flex justify-between items-center group">
                    <div>
                        <span class="font-bold text-blue-900 text-sm block">{{ $tkt->passenger->name }}</span>
                        <span class="text-[10px] text-blue-700">Rute: {{ $tkt->schedule->route->origin }} → {{ $tkt->schedule->route->destination }} | Kursi {{ $tkt->bookingSeat->vehicleSeat->seat_number }}</span>
                    </div>
                    <span class="text-xs font-bold text-blue-600 group-hover:underline">Buka E-Ticket <i class="fa-solid fa-arrow-right text-[10px]"></i></span>
                </a>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('customer.orders.show', $booking->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow text-xs">
                Lihat Detail Pesanan
            </a>
            <a href="{{ route('customer.home') }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 rounded-xl text-xs">
                Kembali Ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
