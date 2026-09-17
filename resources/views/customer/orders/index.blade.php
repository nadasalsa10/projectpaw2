@extends('layouts.app')

@section('title', 'Pesanan Saya - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-xl font-bold">Riwayat & Pesanan Saya</h1>
        <p class="text-xs text-blue-200">Pantau status tiket travel dan riwayat perjalanan Anda.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8" x-data="{ tab: 'active' }">
    <!-- Tab Selector -->
    <div class="flex space-x-2 bg-gray-200 p-1 rounded-xl mb-6 max-w-xs">
        <button @click="tab = 'active'" :class="tab === 'active' ? 'bg-white text-blue-900 shadow font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="flex-1 py-2 text-xs rounded-lg transition text-center">
            Pesanan Aktif ({{ $activeBookings->count() }})
        </button>
        <button @click="tab = 'history'" :class="tab === 'history' ? 'bg-white text-blue-900 shadow font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="flex-1 py-2 text-xs rounded-lg transition text-center">
            Riwayat Selesai ({{ $historyBookings->count() }})
        </button>
    </div>

    <!-- Active Orders Tab -->
    <div x-show="tab === 'active'" class="space-y-4">
        @forelse($activeBookings as $b)
            @php
                $firstTrip = $b->bookingTrips->first();
                $sch = $firstTrip?->schedule;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-3">
                    <div>
                        <span class="text-xs font-mono font-bold text-gray-500">{{ $b->booking_code }}</span>
                        <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-bold ml-2">{{ $b->trip_type }}</span>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full font-bold
                        {{ $b->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $b->status === 'PENDING_PAYMENT' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ in_array($b->status, ['IN_TRANSIT', 'BOARDING']) ? 'bg-indigo-100 text-indigo-800' : '' }}">
                        {{ $b->status }}
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">
                            {{ $sch?->route->origin }} <i class="fa-solid fa-arrow-right text-xs mx-1 text-blue-600"></i> {{ $sch?->route->destination }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fa-regular fa-clock mr-1"></i> Keberangkatan: {{ $sch?->departure_time->format('d M Y - H:i') }} WIB
                        </p>
                        <p class="text-xs font-bold text-emerald-600 mt-1">
                            Rp {{ number_format($b->total_amount, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="flex space-x-2 w-full sm:w-auto">
                        @if($b->status === 'PENDING_PAYMENT')
                            <a href="{{ route('customer.payment.show', $b->id) }}" class="flex-1 sm:flex-none bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow text-center">
                                Bayar Sekarang
                            </a>
                        @endif
                        <a href="{{ route('customer.orders.show', $b->id) }}" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow text-center">
                            Detail & E-Ticket
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-xs">
                <i class="fa-solid fa-ticket text-3xl text-gray-300 mb-2 block"></i>
                Tidak ada pesanan aktif saat ini.
            </div>
        @endforelse
    </div>

    <!-- History Tab -->
    <div x-show="tab === 'history'" class="space-y-4" style="display: none;">
        @forelse($historyBookings as $b)
            @php
                $firstTrip = $b->bookingTrips->first();
                $sch = $firstTrip?->schedule;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm opacity-80">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-3">
                    <span class="text-xs font-mono font-bold text-gray-500">{{ $b->booking_code }}</span>
                    <span class="text-xs px-3 py-1 rounded-full font-bold {{ $b->status === 'COMPLETED' ? 'bg-gray-200 text-gray-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $b->status }}
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">
                            {{ $sch?->route->origin }} → {{ $sch?->route->destination }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $sch?->departure_time->format('d M Y - H:i') }} WIB</p>
                    </div>
                    <a href="{{ route('customer.orders.show', $b->id) }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Lihat Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-xs">
                Belum ada riwayat perjalanan selesai.
            </div>
        @endforelse
    </div>
</div>
@endsection
