@extends('layouts.app')

@section('title', 'Pilih Kursi - SIPP')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-4 px-4">
    <div class="max-w-4xl mx-auto flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Pilih Kursi Penumpang</h1>
            <p class="text-xs text-gray-500">Pilih {{ $validated['passengers'] }} kursi untuk setiap jadwal yang Anda pesan.</p>
        </div>
        <span class="text-xs font-semibold bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
            {{ $validated['passengers'] }} Penumpang
        </span>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    <form action="{{ route('customer.booking.passengers') }}" method="POST" x-data="{
        maxSeats: {{ $validated['passengers'] }},
        outboundSeats: [],
        returnSeats: [],
        toggleOutbound(seatId) {
            if (this.outboundSeats.includes(seatId)) {
                this.outboundSeats = this.outboundSeats.filter(id => id !== seatId);
            } else {
                if (this.outboundSeats.length < this.maxSeats) {
                    this.outboundSeats.push(seatId);
                } else {
                    alert('Anda hanya dapat memilih ' + this.maxSeats + ' kursi.');
                }
            }
        },
        toggleReturn(seatId) {
            if (this.returnSeats.includes(seatId)) {
                this.returnSeats = this.returnSeats.filter(id => id !== seatId);
            } else {
                if (this.returnSeats.length < this.maxSeats) {
                    this.returnSeats.push(seatId);
                } else {
                    alert('Anda hanya dapat memilih ' + this.maxSeats + ' kursi.');
                }
            }
        }
    }">
        @csrf
        <input type="hidden" name="outbound_schedule_id" value="{{ $outboundSchedule->id }}">
        <input type="hidden" name="return_schedule_id" value="{{ $returnSchedule?->id }}">
        <input type="hidden" name="trip_type" value="{{ $validated['trip_type'] }}">
        <input type="hidden" name="passengers" value="{{ $validated['passengers'] }}">

        <!-- Outbound Seats Section -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
            <h2 class="text-base font-bold text-gray-900 mb-1">
                Keberangkatan: {{ $outboundSchedule->route->origin }} → {{ $outboundSchedule->route->destination }}
            </h2>
            <p class="text-xs text-gray-500 mb-6">Armada: {{ $outboundSchedule->vehicle->name }} ({{ $outboundSchedule->vehicle->license_plate }})</p>

            <!-- Seat Status Legend -->
            <div class="flex flex-wrap gap-4 text-xs mb-6 p-3 bg-gray-50 rounded-xl border border-gray-200 justify-center">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded-lg bg-gray-200 border border-gray-300"></div>
                    <span class="text-gray-600 font-medium">Tersedia</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-[10px]">✓</div>
                    <span class="text-gray-900 font-bold">Dipilih</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center text-[10px] font-bold">X</div>
                    <span class="text-gray-400">Terisi / Booked</span>
                </div>
            </div>

            <!-- Vehicle Seat Layout Grid -->
            <div class="max-w-xs mx-auto bg-gray-50 border-2 border-gray-300 rounded-3xl p-5 shadow-inner relative">
                <!-- Driver Section -->
                <div class="flex justify-between items-center border-b border-gray-300 pb-4 mb-5">
                    <div class="bg-slate-800 text-slate-200 text-xs font-bold px-3 py-1.5 rounded-lg flex items-center shadow">
                        <i class="fa-solid fa-steering-wheel mr-1.5"></i> SUPIR / DRIVER
                    </div>
                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">DEPAN</span>
                </div>

                <!-- Seat Buttons Grid -->
                <div class="grid grid-cols-2 gap-4">
                    @foreach($outboundSchedule->vehicle->seats as $seat)
                        @php
                            $isTaken = in_array($seat->id, $takenOutboundSeatIds);
                        @endphp
                        <template x-for="item in [1]" :key="{{ $seat->id }}">
                            <button type="button" 
                                @if($isTaken) disabled @else @click="toggleOutbound({{ $seat->id }})" @endif
                                :class="{
                                    'bg-rose-500 text-white opacity-60 cursor-not-allowed': {{ $isTaken ? 'true' : 'false' }},
                                    'bg-blue-600 text-white border-blue-700 shadow-lg scale-105': outboundSeats.includes({{ $seat->id }}),
                                    'bg-white text-gray-800 border-gray-300 hover:border-blue-500 hover:bg-blue-50': !{{ $isTaken ? 'true' : 'false' }} && !outboundSeats.includes({{ $seat->id }})
                                }"
                                class="h-12 border-2 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-150 relative">
                                <span>Kursi {{ $seat->seat_number }}</span>
                                <template x-if="outboundSeats.includes({{ $seat->id }})">
                                    <input type="hidden" name="outbound_seats[]" value="{{ $seat->id }}">
                                </template>
                            </button>
                        </template>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-4 text-center text-xs font-semibold text-blue-600">
                Kursi Keberangkatan Terpilih: <span x-text="outboundSeats.length"></span> / {{ $validated['passengers'] }}
            </div>
        </div>

        <!-- Return Seats Section (If Round-Trip) -->
        @if($returnSchedule)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
                <h2 class="text-base font-bold text-gray-900 mb-1">
                    Kepulangan: {{ $returnSchedule->route->origin }} → {{ $returnSchedule->route->destination }}
                </h2>
                <p class="text-xs text-gray-500 mb-6">Armada: {{ $returnSchedule->vehicle->name }} ({{ $returnSchedule->vehicle->license_plate }})</p>

                <div class="max-w-xs mx-auto bg-gray-50 border-2 border-gray-300 rounded-3xl p-5 shadow-inner relative">
                    <div class="flex justify-between items-center border-b border-gray-300 pb-4 mb-5">
                        <div class="bg-slate-800 text-slate-200 text-xs font-bold px-3 py-1.5 rounded-lg flex items-center shadow">
                            <i class="fa-solid fa-steering-wheel mr-1.5"></i> SUPIR / DRIVER
                        </div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">DEPAN</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach($returnSchedule->vehicle->seats as $seat)
                            @php
                                $isTaken = in_array($seat->id, $takenReturnSeatIds);
                            @endphp
                            <template x-for="item in [1]" :key="{{ $seat->id }}">
                                <button type="button" 
                                    @if($isTaken) disabled @else @click="toggleReturn({{ $seat->id }})" @endif
                                    :class="{
                                        'bg-rose-500 text-white opacity-60 cursor-not-allowed': {{ $isTaken ? 'true' : 'false' }},
                                        'bg-indigo-600 text-white border-indigo-700 shadow-lg scale-105': returnSeats.includes({{ $seat->id }}),
                                        'bg-white text-gray-800 border-gray-300 hover:border-indigo-500 hover:bg-indigo-50': !{{ $isTaken ? 'true' : 'false' }} && !returnSeats.includes({{ $seat->id }})
                                    }"
                                    class="h-12 border-2 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-150 relative">
                                    <span>Kursi {{ $seat->seat_number }}</span>
                                    <template x-if="returnSeats.includes({{ $seat->id }})">
                                        <input type="hidden" name="return_seats[]" value="{{ $seat->id }}">
                                    </template>
                                </button>
                            </template>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 text-center text-xs font-semibold text-indigo-600">
                    Kursi Kepulangan Terpilih: <span x-text="returnSeats.length"></span> / {{ $validated['passengers'] }}
                </div>
            </div>
        @endif

        <button type="submit" 
            :disabled="outboundSeats.length !== maxSeats @if($returnSchedule) || returnSeats.length !== maxSeats @endif"
            :class="outboundSeats.length === maxSeats @if($returnSchedule) && returnSeats.length === maxSeats @endif ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
            class="w-full font-bold py-3.5 rounded-xl shadow-lg transition text-sm flex items-center justify-center space-x-2">
            <span>Isi Data Penumpang</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </form>
</div>
@endsection
