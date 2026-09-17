@extends('layouts.admin')

@section('title', 'Admin Dashboard - SIPP')
@section('page_title', 'Dashboard Ringkasan Operasional')

@section('content')
<!-- Metrics Overview Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Total Bookings -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs text-gray-500 font-semibold block">Total Booking</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ number_format($totalBookings) }}</span>
            <span class="text-[11px] text-blue-600 font-bold mt-1 block">+{{ $todayBookings }} Hari Ini</span>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-book-bookmark"></i>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs text-gray-500 font-semibold block">Total Pendapatan (Paid)</span>
            <span class="text-xl font-black text-emerald-600 mt-1 block">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-wallet"></i>
        </div>
    </div>

    <!-- Active Schedules -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs text-gray-500 font-semibold block">Jadwal Aktif Mendatang</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ $activeSchedules }}</span>
        </div>
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-calendar-alt"></i>
        </div>
    </div>

    <!-- Active Fleet & Drivers -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs text-gray-500 font-semibold block">Driver & Armada</span>
            <span class="text-xl font-black text-gray-900 mt-1 block">{{ $totalDrivers }} Driver / {{ $activeVehicles }} Mobil</span>
        </div>
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-bus"></i>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
        <h2 class="font-bold text-gray-900 text-base"><i class="fa-solid fa-clock-rotate-left text-blue-600 mr-2"></i> Pemesanan Terbaru</h2>
        <a href="{{ route('admin.bookings.index') }}" class="text-xs text-blue-600 font-bold hover:underline">Lihat Semua Data Booking →</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Kode Booking</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Rute Perjalanan</th>
                    <th class="p-4">Total Bayar</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($recentBookings as $b)
                    @php $firstTrip = $b->bookingTrips->first(); @endphp
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-mono font-bold text-blue-900">{{ $b->booking_code }}</td>
                        <td class="p-4 font-semibold text-gray-900">{{ $b->user->name }}</td>
                        <td class="p-4">{{ $firstTrip?->schedule->route->origin }} → {{ $firstTrip?->schedule->route->destination }}</td>
                        <td class="p-4 font-bold text-emerald-600">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="text-blue-600 hover:text-blue-800 font-bold">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada transaksi booking.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
