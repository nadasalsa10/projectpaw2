@extends('layouts.admin')

@section('title', 'Data Pembayaran - SIPP Admin')
@section('page_title', 'Manajemen Pembayaran Transaksi')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Kode Pembayaran</th>
                    <th class="p-4">Kode Booking</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Metode Bayar</th>
                    <th class="p-4">Nominal</th>
                    <th class="p-4">Status Bayar</th>
                    <th class="p-4">Waktu Bayar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($payments as $p)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-mono font-bold text-gray-900">{{ $p->payment_code }}</td>
                        <td class="p-4 font-mono font-bold text-blue-900">{{ $p->booking->booking_code }}</td>
                        <td class="p-4 font-semibold text-gray-900">{{ $p->booking->user->name }}</td>
                        <td class="p-4 font-bold text-gray-700">{{ $p->payment_method }}</td>
                        <td class="p-4 font-black text-emerald-600 text-sm">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $p->payment_status }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-500">{{ $p->paid_at ? $p->paid_at->format('d M Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-400">Belum ada transaksi pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-gray-100">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
