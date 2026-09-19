@extends('layouts.admin')

@section('title', 'Laporan Operasional & Keuangan - SIPP Admin')
@section('page_title', 'Laporan Ringkasan Keuangan & Perjalanan')

@section('content')
<div class="space-y-6">
    <!-- Reports Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-6 rounded-2xl shadow-lg">
            <span class="text-xs uppercase font-bold tracking-wider text-emerald-200 block">Total Pendapatan Terkonfirmasi</span>
            <span class="text-3xl font-black mt-2 block">Rp {{ number_format($totalPaidRevenue, 0, ',', '.') }}</span>
            <span class="text-xs text-emerald-100 mt-1 block">Dari transaksi lunas (PAID)</span>
        </div>

        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-6 rounded-2xl shadow-lg">
            <span class="text-xs uppercase font-bold tracking-wider text-blue-200 block">Total Pemesanan Selesai</span>
            <span class="text-3xl font-black mt-2 block">{{ number_format($totalBookingsCompleted) }}</span>
            <span class="text-xs text-blue-100 mt-1 block">Transaksi COMPLETED</span>
        </div>

        <div class="bg-gradient-to-r from-purple-700 to-indigo-900 text-white p-6 rounded-2xl shadow-lg">
            <span class="text-xs uppercase font-bold tracking-wider text-purple-200 block">Total Perjalanan Selesai</span>
            <span class="text-3xl font-black mt-2 block">{{ number_format($totalSchedulesCompleted) }}</span>
            <span class="text-xs text-purple-100 mt-1 block">Trip armada telah sukses tiba</span>
        </div>
    </div>

    <!-- Recent Paid Transactions Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-x-auto">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">Rincian Transaksi Masuk Terakhir</h3>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Tanggal Lunas</th>
                    <th class="p-4">Kode Pembayaran</th>
                    <th class="p-4">Kode Booking</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Metode Bayar</th>
                    <th class="p-4">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($recentPayments as $p)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 text-gray-500">{{ $p->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td class="p-4 font-mono font-bold text-gray-900">{{ $p->payment_code }}</td>
                        <td class="p-4 font-mono font-bold text-blue-900">{{ $p->booking->booking_code }}</td>
                        <td class="p-4 font-semibold text-gray-900">{{ $p->booking->user->name }}</td>
                        <td class="p-4 font-bold text-gray-700">{{ $p->payment_method }}</td>
                        <td class="p-4 font-black text-emerald-600 text-sm">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada transaksi pembayaran lunas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
