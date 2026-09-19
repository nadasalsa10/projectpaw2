@extends('layouts.app')

@section('title', 'Daftar Tunggu Saya - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4">
    <div class="max-w-4xl mx-auto flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">Daftar Tunggu (Waiting List)</h1>
            <p class="text-xs text-blue-200">Pantau status antrean jadwal travel yang sedang Anda tunggu</p>
        </div>
        <a href="{{ route('customer.home') }}" class="bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Beranda
        </a>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start space-x-3 text-xs text-blue-900">
        <i class="fa-solid fa-circle-info text-blue-600 text-lg mt-0.5"></i>
        <div>
            <p class="font-bold">Bagaimana Cara Kerja Waiting List SIPP?</p>
            <p class="text-blue-700 mt-0.5">Jika terjadi pembatalan tiket atau pesanan kedaluwarsa pada jadwal yang Anda tunggu, sistem SIPP akan secara otomatis mengirimkan notifikasi langsung ke akun Anda agar Anda dapat segera memesan kursi!</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($waitingLists as $wl)
            @php $sch = $wl->schedule; @endphp
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-100 pb-3 mb-3">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-md">
                                {{ $sch->route->origin }} <i class="fa-solid fa-arrow-right text-[10px] mx-1"></i> {{ $sch->route->destination }}
                            </span>
                            @if($sch->is_extra)
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded border border-amber-300">
                                    <i class="fa-solid fa-star mr-0.5"></i> EXTRA MUDIK
                                </span>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-900 text-base mt-1">
                            <i class="fa-regular fa-clock text-gray-400 mr-1"></i> {{ $sch->departure_time->format('d M Y - H:i') }} WIB
                        </h3>
                    </div>

                    <div>
                        @if($wl->status === 'WAITING')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fa-solid fa-hourglass-half mr-1 text-[10px]"></i> Menunggu Pembatalan Kursi
                            </span>
                        @elseif($wl->status === 'NOTIFIED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-300 animate-pulse">
                                <i class="fa-solid fa-bell mr-1 text-[10px]"></i> Kursi Tersedia!
                            </span>
                        @elseif($wl->status === 'CANCELLED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-300">
                                Dibatalkan
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-xs text-gray-600 gap-3">
                    <div class="space-y-1">
                        <p><i class="fa-solid fa-bus text-gray-400 mr-1"></i> {{ $sch->vehicle->name }} (Rp {{ number_format($sch->price, 0, ',', '.') }}/kursi)</p>
                        <p><i class="fa-solid fa-users text-gray-400 mr-1"></i> Jumlah Penumpang Dicari: <strong class="text-gray-900 font-bold">{{ $wl->passengers_count }} Kursi</strong></p>
                        <p class="text-[11px] text-gray-400">Bergabung pada: {{ $wl->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="flex items-center space-x-2 w-full sm:w-auto">
                        @if($wl->status === 'NOTIFIED' || $sch->available_seats_count > 0)
                            <a href="{{ route('customer.search', ['origin' => $sch->route->origin, 'destination' => $sch->route->destination, 'trip_type' => 'ONE_WAY', 'departure_date' => $sch->departure_time->format('Y-m-d'), 'passengers' => $wl->passengers_count]) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow transition">
                                Pesan Kursi Sekarang!
                            </a>
                        @endif

                        @if($wl->status === 'WAITING')
                            <form action="{{ route('customer.waiting_list.cancel', $wl->id) }}" method="POST" onsubmit="return confirm('Batalkan antrean daftar tunggu ini?')">
                                @csrf
                                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-rose-600 font-bold text-xs px-3 py-2 rounded-xl transition">
                                    Batalkan Antrean
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-500 space-y-3">
                <i class="fa-solid fa-clipboard-list text-4xl text-gray-300"></i>
                <h3 class="text-base font-bold text-gray-800">Tidak Ada Antrean Waiting List Active</h3>
                <p class="text-xs text-gray-500">Anda belum mendaftar di daftar tunggu jadwal manapun. Jika menemukan jadwal travel penuh saat pencarian, Anda dapat menekan tombol <strong>Gabung Daftar Tunggu</strong>.</p>
                <a href="{{ route('customer.home') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">
                    Cari Jadwal Travel
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
