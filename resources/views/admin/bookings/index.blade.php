@extends('layouts.admin')

@section('title', 'Data Booking - SIPP Admin')
@section('page_title', 'Manajemen Transaksi & Data Booking')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Kode Booking</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Tipe Trip</th>
                    <th class="p-4">Total Nominal</th>
                    <th class="p-4">Status Booking</th>
                    <th class="p-4">Tanggal Buat</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($bookings as $b)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-mono font-bold text-blue-900">{{ $b->booking_code }}</td>
                        <td class="p-4 font-semibold text-gray-900">{{ $b->user->name }}</td>
                        <td class="p-4"><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-bold">{{ $b->trip_type }}</span></td>
                        <td class="p-4 font-black text-emerald-600 text-sm">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-500">{{ $b->created_at->format('d M Y H:i') }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="text-blue-600 hover:text-blue-800 font-bold">Detail & Action</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-400">Belum ada data booking.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-gray-100">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
