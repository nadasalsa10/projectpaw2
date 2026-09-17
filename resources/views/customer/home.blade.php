@extends('layouts.app')

@section('title', 'SIPP - Sistem Informasi Pulang Pergi')

@section('content')
<!-- Hero Section & Search Form -->
<div class="bg-gradient-to-br from-sipp-900 via-blue-900 to-indigo-900 text-white pt-8 pb-16 px-4 shadow-xl">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <span class="bg-blue-600/50 border border-blue-400/30 text-blue-200 text-xs px-3 py-1 rounded-full font-semibold uppercase tracking-wider">Layanan Travel Antar Kota Terpercaya</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-3">Pesan Tiket Travel Mobil Cepat & Safe</h1>
            <p class="text-sm text-blue-200 mt-2">Palembang <i class="fa-solid fa-arrow-right-long mx-1 text-xs"></i> Jambi dan Rute Populer Lainnya</p>
        </div>

        <!-- Search Form Component -->
        <div x-data="{ tripType: 'ONE_WAY' }" class="bg-white text-gray-800 rounded-2xl shadow-2xl p-5 md:p-6 border border-gray-100">
            <form action="{{ route('customer.search') }}" method="GET">
                <!-- Trip Type Tabs -->
                <div class="flex space-x-2 bg-gray-100 p-1.5 rounded-xl mb-5 max-w-xs">
                    <button type="button" @click="tripType = 'ONE_WAY'" :class="tripType === 'ONE_WAY' ? 'bg-blue-600 text-white shadow font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="flex-1 py-1.5 text-xs rounded-lg transition text-center">
                        Sekali Jalan
                    </button>
                    <button type="button" @click="tripType = 'ROUND_TRIP'" :class="tripType === 'ROUND_TRIP' ? 'bg-blue-600 text-white shadow font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="flex-1 py-1.5 text-xs rounded-lg transition text-center">
                        Pulang-Pergi
                    </button>
                </div>

                <input type="hidden" name="trip_type" :value="tripType">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Origin Select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5"><i class="fa-solid fa-location-dot text-blue-600 mr-1"></i> Asal</label>
                        <select name="origin" required class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Pilih Kota Asal</option>
                            @foreach($origins as $org)
                                <option value="{{ $org }}" {{ old('origin', 'Palembang') == $org ? 'selected' : '' }}>{{ $org }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Destination Select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5"><i class="fa-solid fa-flag-checkered text-blue-600 mr-1"></i> Tujuan</label>
                        <select name="destination" required class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Pilih Kota Tujuan</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}" {{ old('destination', 'Jambi') == $dest ? 'selected' : '' }}>{{ $dest }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Departure Date -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5"><i class="fa-solid fa-calendar-day text-blue-600 mr-1"></i> Tanggal Pergi</label>
                        <input type="date" name="departure_date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Return Date (If Round Trip) -->
                    <div x-show="tripType === 'ROUND_TRIP'" x-transition>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5"><i class="fa-solid fa-calendar-week text-blue-600 mr-1"></i> Tanggal Pulang</label>
                        <input type="date" name="return_date" value="{{ date('Y-m-d', strtotime('+2 days')) }}" min="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Passengers Count -->
                    <div :class="tripType === 'ROUND_TRIP' ? 'lg:col-span-4' : 'lg:col-span-1'">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5"><i class="fa-solid fa-users text-blue-600 mr-1"></i> Jumlah Penumpang</label>
                        <select name="passengers" required class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">{{ $i }} Penumpang</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg hover:shadow-blue-500/30 transition text-sm flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Cari Jadwal Perjalanan</span>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-8">
    <!-- Active Booking Banner (If logged in and has active booking) -->
    @if($activeBooking)
        @php
            $firstTrip = $activeBooking->bookingTrips->first();
            $schedule = $firstTrip?->schedule;
        @endphp
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white rounded-2xl p-5 shadow-xl border border-blue-700/50">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span class="bg-blue-500/30 text-blue-200 border border-blue-400/30 text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider">Perjalanan Aktif Anda</span>
                    <h3 class="text-xl font-bold mt-2">
                        {{ $schedule->route->origin }} <i class="fa-solid fa-arrow-right text-xs mx-1 text-blue-300"></i> {{ $schedule->route->destination }}
                    </h3>
                    <p class="text-xs text-blue-200 mt-1">
                        <i class="fa-regular fa-clock mr-1"></i> {{ $schedule->departure_time->format('d M Y - H:i') }} WIB | 
                        Armada: <span class="font-semibold text-white">{{ $schedule->vehicle->name }}</span>
                    </p>
                </div>
                <div class="flex items-center space-x-3 w-full md:w-auto">
                    <span class="text-xs bg-emerald-500 text-white px-3 py-1.5 rounded-full font-bold shadow">
                        {{ $activeBooking->status }}
                    </span>
                    <a href="{{ route('customer.orders.show', $activeBooking->id) }}" class="bg-white text-blue-900 hover:bg-blue-50 font-bold text-xs px-4 py-2.5 rounded-xl transition shadow">
                        Detail Tiket & Live Status
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Upcoming Schedules List -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-clock-rotate-left text-blue-600 mr-2"></i> Jadwal Terdekat Hari Ini</h2>
            <span class="text-xs text-gray-500 font-medium">Armada Toyota Hiace Executive</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($upcomingSchedules as $sch)
                <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start border-b border-gray-100 pb-3 mb-3">
                        <div>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md mb-1 inline-block">
                                {{ $sch->route->origin }} <i class="fa-solid fa-arrow-right text-[10px] mx-1"></i> {{ $sch->route->destination }}
                            </span>
                            <h4 class="font-bold text-gray-900 text-base mt-1">{{ $sch->departure_time->format('H:i') }} WIB</h4>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400 block">Harga / Kursi</span>
                            <span class="font-black text-emerald-600 text-base">Rp {{ number_format($sch->price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-600">
                        <div>
                            <p><i class="fa-solid fa-bus text-gray-400 mr-1"></i> {{ $sch->vehicle->name }}</p>
                            <p class="mt-0.5"><i class="fa-solid fa-user-gear text-gray-400 mr-1"></i> Driver: {{ $sch->driver?->user->name ?? 'TBA' }}</p>
                        </div>
                        <a href="{{ route('customer.search', ['origin' => $sch->route->origin, 'destination' => $sch->route->destination, 'trip_type' => 'ONE_WAY', 'departure_date' => $sch->departure_time->format('Y-m-d'), 'passengers' => 1]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow transition">
                            Pesan
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-xs">
                    <i class="fa-solid fa-calendar-xmark text-2xl text-gray-400 mb-2 block"></i>
                    Belum ada jadwal perjalanan terdaftar untuk hari ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
