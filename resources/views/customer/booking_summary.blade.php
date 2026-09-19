@extends('layouts.app')

@section('title', 'Ringkasan Pemesanan - SIPP')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-4 px-4">
    <div class="max-w-3xl mx-auto flex items-center space-x-3">
        <button type="button" onclick="window.history.back()" class="inline-flex items-center text-xs font-semibold text-gray-700 hover:text-gray-900 bg-white hover:bg-gray-50 border border-gray-300 px-3 py-1.5 rounded-xl transition shadow-xs">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
        </button>
        <div>
            <h1 class="text-lg font-bold text-gray-900">Ringkasan & Metode Pembayaran</h1>
            <p class="text-xs text-gray-500">Periksa rincian pesanan Anda sebelum melanjutkan ke pembayaran.</p>
        </div>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8">
    <form action="{{ route('customer.booking.store') }}" method="POST">
        @csrf
        <input type="hidden" name="outbound_schedule_id" value="{{ $validated['outbound_schedule_id'] }}">
        <input type="hidden" name="return_schedule_id" value="{{ $validated['return_schedule_id'] ?? '' }}">
        <input type="hidden" name="trip_type" value="{{ $validated['trip_type'] }}">

        @foreach($validated['outbound_seats'] as $sId)
            <input type="hidden" name="outbound_seats[]" value="{{ $sId }}">
        @endforeach

        @if(!empty($validated['return_seats']))
            @foreach($validated['return_seats'] as $rId)
                <input type="hidden" name="return_seats[]" value="{{ $rId }}">
            @endforeach
        @endif

        @foreach($validated['passengers'] as $i => $p)
            <input type="hidden" name="passengers[{{ $i }}][name]" value="{{ $p['name'] }}">
            <input type="hidden" name="passengers[{{ $i }}][phone]" value="{{ $p['phone'] ?? '' }}">
            <input type="hidden" name="passengers[{{ $i }}][id_number]" value="{{ $p['id_number'] ?? '' }}">
        @endforeach

        <!-- Trip Details Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-route text-blue-600 mr-2"></i> Rincian Perjalanan
            </h2>

            <!-- Outbound Trip -->
            <div class="bg-blue-50/60 rounded-xl p-4 border border-blue-100 mb-4">
                <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded uppercase tracking-wider">Perjalanan Keberangkatan</span>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">
                            {{ $outboundSchedule->route->origin }} <i class="fa-solid fa-arrow-right text-xs mx-1 text-blue-600"></i> {{ $outboundSchedule->route->destination }}
                        </h3>
                        <p class="text-xs text-gray-600 mt-0.5"><i class="fa-regular fa-clock mr-1"></i> {{ $outboundSchedule->departure_time->format('d M Y - H:i') }} WIB</p>
                        <p class="text-xs text-gray-500 mt-0.5">Armada: {{ $outboundSchedule->vehicle->name }} ({{ $outboundSchedule->vehicle->license_plate }})</p>
                    </div>
                    <div class="text-right text-xs font-bold text-gray-700">
                        Kursi: {{ $outboundSeats->pluck('seat_number')->implode(', ') }}
                    </div>
                </div>
            </div>

            <!-- Return Trip (If Round Trip) -->
            @if($returnSchedule)
                <div class="bg-indigo-50/60 rounded-xl p-4 border border-indigo-100">
                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded uppercase tracking-wider">Perjalanan Kepulangan</span>
                    <div class="flex justify-between items-center mt-2">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">
                                {{ $returnSchedule->route->origin }} <i class="fa-solid fa-arrow-right text-xs mx-1 text-indigo-600"></i> {{ $returnSchedule->route->destination }}
                            </h3>
                            <p class="text-xs text-gray-600 mt-0.5"><i class="fa-regular fa-clock mr-1"></i> {{ $returnSchedule->departure_time->format('d M Y - H:i') }} WIB</p>
                            <p class="text-xs text-gray-500 mt-0.5">Armada: {{ $returnSchedule->vehicle->name }} ({{ $returnSchedule->vehicle->license_plate }})</p>
                        </div>
                        <div class="text-right text-xs font-bold text-gray-700">
                            Kursi: {{ $returnSeats->pluck('seat_number')->implode(', ') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Passenger Manifest List -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-900 mb-3 pb-2 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-users text-blue-600 mr-2"></i> Daftar Penumpang ({{ count($validated['passengers']) }} Orang)
            </h2>
            <div class="space-y-2">
                @foreach($validated['passengers'] as $idx => $p)
                    <div class="flex justify-between items-center text-xs py-1.5 border-b border-gray-50 last:border-0">
                        <span class="font-medium text-gray-800">{{ $idx + 1 }}. {{ $p['name'] }}</span>
                        <span class="text-gray-500">{{ $p['phone'] ?? 'Tidak ada HP' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-wallet text-blue-600 mr-2"></i> Pilih Metode Pembayaran
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="border-2 border-gray-200 rounded-xl p-3.5 flex items-center space-x-3 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="payment_method" value="BANK_TRANSFER" checked class="text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-gray-900">Transfer Bank</span>
                        <span class="block text-[10px] text-gray-500">BCA / Mandiri / BNI</span>
                    </div>
                </label>

                <label class="border-2 border-gray-200 rounded-xl p-3.5 flex items-center space-x-3 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="payment_method" value="VIRTUAL_ACCOUNT" class="text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-gray-900">Virtual Account</span>
                        <span class="block text-[10px] text-gray-500">Bayar Otomatis 24 Jam</span>
                    </div>
                </label>

                <label class="border-2 border-gray-200 rounded-xl p-3.5 flex items-center space-x-3 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="payment_method" value="QRIS" class="text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-gray-900">QRIS</span>
                        <span class="block text-[10px] text-gray-500">Scan QR via Gopay, OVO, ShopeePay</span>
                    </div>
                </label>

                <label class="border-2 border-gray-200 rounded-xl p-3.5 flex items-center space-x-3 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="payment_method" value="E_WALLET" class="text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-gray-900">E-Wallet</span>
                        <span class="block text-[10px] text-gray-500">Dana, LinkAja, DOKU</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Total Breakdown & Action -->
        <div class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs text-gray-400 block font-medium">Total Pembayaran Total</span>
                <span class="text-2xl font-black text-emerald-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                <span class="text-[10px] text-gray-400 block mt-0.5">*Sudah termasuk asuransi & biaya layanan</span>
            </div>

            <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-3.5 rounded-xl shadow-lg transition text-sm flex items-center justify-center space-x-2">
                <span>Buat Pemesanan Sekarang</span>
                <i class="fa-solid fa-lock text-xs"></i>
            </button>
        </div>
    </form>
</div>
@endsection
