@extends('layouts.app')

@section('title', 'Data Penumpang - SIPP')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-4 px-4">
    <div class="max-w-3xl mx-auto flex items-center space-x-3">
        <button type="button" onclick="window.history.back()" class="inline-flex items-center text-xs font-semibold text-gray-700 hover:text-gray-900 bg-white hover:bg-gray-50 border border-gray-300 px-3 py-1.5 rounded-xl transition shadow-xs">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
        </button>
        <div>
            <h1 class="text-lg font-bold text-gray-900">Isi Data Penumpang</h1>
            <p class="text-xs text-gray-500">Pastikan nama dan kontak sesuai dengan identitas resmi (KTP/SIM/Paspor).</p>
        </div>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8">
    <form action="{{ route('customer.booking.summary') }}" method="POST" class="space-y-6">
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

        @for($i = 0; $i < (int)$validated['passengers']; $i++)
            @php
                $outboundSeatObj = $outboundSeats->get($i);
                $returnSeatObj = $returnSeats->get($i);
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2">{{ $i + 1 }}</span>
                        Penumpang {{ $i + 1 }}
                    </h3>
                    <div class="flex space-x-2 text-xs font-semibold">
                        <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md border border-blue-200">
                            Pergi: Kursi {{ $outboundSeatObj?->seat_number }}
                        </span>
                        @if($returnSeatObj)
                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md border border-indigo-200">
                                Pulang: Kursi {{ $returnSeatObj?->seat_number }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap Penumpang</label>
                        <input type="text" name="passengers[{{ $i }}][name]" value="{{ old("passengers.$i.name", $i === 0 ? Auth::user()->name : '') }}" required placeholder="Sesuai KTP / SIM" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Telepon / WA (Opsional)</label>
                            <input type="text" name="passengers[{{ $i }}][phone]" value="{{ old("passengers.$i.phone", $i === 0 ? Auth::user()->phone : '') }}" placeholder="081234567890" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">NIK / No Identitas (Opsional)</label>
                            <input type="text" name="passengers[{{ $i }}][id_number]" value="{{ old("passengers.$i.id_number") }}" placeholder="167101xxxxxx" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>
        @endfor

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition text-sm flex items-center justify-center space-x-2">
            <span>Ringkasan & Metode Pembayaran</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </form>
</div>
@endsection
