@extends('layouts.app')

@section('title', 'Hasil Pencarian Travel - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4 shadow-md">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-xl font-bold">
                {{ $searchParams['origin'] }} <i class="fa-solid fa-arrow-right mx-1 text-sm text-blue-300"></i> {{ $searchParams['destination'] }}
            </h1>
            <p class="text-xs text-blue-200 mt-0.5">
                {{ \Carbon\Carbon::parse($searchParams['departure_date'])->format('d M Y') }} | {{ $searchParams['passengers'] }} Penumpang | {{ $searchParams['trip_type'] === 'ROUND_TRIP' ? 'Pulang-Pergi' : 'Sekali Jalan' }}
            </p>
        </div>
        <a href="{{ route('customer.home') }}" class="bg-blue-800 hover:bg-blue-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-blue-700">
            <i class="fa-solid fa-pen-to-square mr-1"></i> Ubah Pencarian
        </a>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    <form action="{{ route('customer.booking.select_seat') }}" method="GET">
        <input type="hidden" name="trip_type" value="{{ $searchParams['trip_type'] }}">
        <input type="hidden" name="passengers" value="{{ $searchParams['passengers'] }}">

        <!-- Outbound Schedule Section -->
        <div class="mb-8">
            <h2 class="text-base font-bold text-gray-900 mb-3 flex items-center">
                <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">1</span>
                Pilih Jadwal Keberangkatan ({{ $searchParams['origin'] }} → {{ $searchParams['destination'] }})
            </h2>

            <div class="space-y-4">
                @forelse($outboundSchedules as $sch)
                    @php $isFull = ($sch->available_seats ?? $sch->available_seats_count) <= 0; @endphp
                    <div class="bg-white border-2 {{ $isFull ? 'border-gray-200 opacity-90' : 'border-gray-200 hover:border-blue-500' }} rounded-2xl p-5 shadow-sm transition flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden group">
                        
                        @if(!$isFull)
                            <label class="absolute inset-0 z-10 cursor-pointer">
                                <input type="radio" name="outbound_schedule_id" value="{{ $sch->id }}" required {{ $loop->first ? 'checked' : '' }} class="hidden peer">
                                <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50/50 absolute inset-0 border-2 rounded-2xl pointer-events-none transition"></div>
                            </label>
                        @endif

                        <div class="relative z-10">
                            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                <span class="text-lg font-black text-blue-900">{{ $sch->departure_time->format('H:i') }} WIB</span>
                                <span class="text-xs text-gray-400 font-medium">→ Durasi {{ floor($sch->route->duration_minutes / 60) }}j {{ $sch->route->duration_minutes % 60 }}m</span>
                                <span class="text-lg font-black text-gray-700 mr-2">{{ $sch->arrival_time->format('H:i') }} WIB</span>
                                
                                @if($sch->is_extra)
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded border border-amber-300">
                                        <i class="fa-solid fa-star text-[9px] mr-0.5"></i> EXTRA MUDIK
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-gray-600 mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                <span><i class="fa-solid fa-van-shuttle text-blue-600 mr-1"></i> {{ $sch->vehicle->name }} ({{ $sch->vehicle->license_plate }})</span>
                                @if($isFull)
                                    <span class="bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded text-[11px] border border-rose-200">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> KURSI FULL (0 Tersisa)
                                    </span>
                                @else
                                    <span><i class="fa-solid fa-chair text-emerald-600 mr-1"></i> Tersedia: <strong>{{ $sch->available_seats ?? $sch->available_seats_count }} Kursi</strong></span>
                                @endif
                                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-medium border border-slate-200"><i class="fa-solid fa-id-card text-emerald-600 mr-1"></i> Driver: <strong>{{ $sch->driver?->user->name ?? 'TBA' }}</strong></span>
                            </div>
                        </div>

                        <div class="text-left md:text-right relative z-20 w-full md:w-auto flex md:flex-col justify-between items-center border-t md:border-t-0 pt-3 md:pt-0 border-gray-100">
                            <div class="font-black text-xl text-emerald-600">Rp {{ number_format($sch->price, 0, ',', '.') }}<span class="text-xs text-gray-400 font-normal"> /org</span></div>
                            
                            @if($isFull)
                                <form action="{{ route('customer.waiting_list.join') }}" method="POST" class="mt-1">
                                    @csrf
                                    <input type="hidden" name="schedule_id" value="{{ $sch->id }}">
                                    <input type="hidden" name="passengers_count" value="{{ $searchParams['passengers'] }}">
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-1.5 rounded-xl shadow transition flex items-center space-x-1">
                                        <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                                        <span>Gabung Waiting List</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-xs font-bold text-blue-600 group-hover:underline">Pilih Jadwal <i class="fa-solid fa-circle-check ml-1"></i></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-2xl text-amber-500 mb-2 block"></i>
                        Tidak ada jadwal travel yang tersedia untuk rute dan tanggal ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Return Schedule Section (If Round-Trip) -->
        @if($searchParams['trip_type'] === 'ROUND_TRIP')
            <div class="mb-8">
                <h2 class="text-base font-bold text-gray-900 mb-3 flex items-center">
                    <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">2</span>
                    Pilih Jadwal Kepulangan ({{ $searchParams['destination'] }} → {{ $searchParams['origin'] }})
                </h2>

                <div class="space-y-4">
                    @forelse($returnSchedules as $sch)
                        @php $isFull = ($sch->available_seats ?? $sch->available_seats_count) <= 0; @endphp
                        <div class="bg-white border-2 {{ $isFull ? 'border-gray-200 opacity-90' : 'border-gray-200 hover:border-indigo-500' }} rounded-2xl p-5 shadow-sm transition flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden group">
                            
                            @if(!$isFull)
                                <label class="absolute inset-0 z-10 cursor-pointer">
                                    <input type="radio" name="return_schedule_id" value="{{ $sch->id }}" {{ $searchParams['trip_type'] === 'ROUND_TRIP' ? 'required' : '' }} {{ $loop->first ? 'checked' : '' }} class="hidden peer">
                                    <div class="peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 absolute inset-0 border-2 rounded-2xl pointer-events-none transition"></div>
                                </label>
                            @endif

                            <div class="relative z-10">
                                <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                    <span class="text-lg font-black text-indigo-900">{{ $sch->departure_time->format('H:i') }} WIB</span>
                                    <span class="text-xs text-gray-400 font-medium">→ Durasi {{ floor($sch->route->duration_minutes / 60) }}j {{ $sch->route->duration_minutes % 60 }}m</span>
                                    <span class="text-lg font-black text-gray-700 mr-2">{{ $sch->arrival_time->format('H:i') }} WIB</span>
                                    
                                    @if($sch->is_extra)
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded border border-amber-300">
                                            <i class="fa-solid fa-star text-[9px] mr-0.5"></i> EXTRA MUDIK
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs text-gray-600 mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                    <span><i class="fa-solid fa-van-shuttle text-indigo-600 mr-1"></i> {{ $sch->vehicle->name }}</span>
                                    @if($isFull)
                                        <span class="bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded text-[11px] border border-rose-200">
                                            <i class="fa-solid fa-circle-xmark mr-1"></i> KURSI FULL (0 Tersisa)
                                        </span>
                                    @else
                                        <span><i class="fa-solid fa-chair text-emerald-600 mr-1"></i> Tersedia: <strong>{{ $sch->available_seats ?? $sch->available_seats_count }} Kursi</strong></span>
                                    @endif
                                    <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-medium border border-slate-200"><i class="fa-solid fa-id-card text-indigo-600 mr-1"></i> Driver: <strong>{{ $sch->driver?->user->name ?? 'TBA' }}</strong></span>
                                </div>
                            </div>

                            <div class="text-left md:text-right relative z-20 w-full md:w-auto flex md:flex-col justify-between items-center border-t md:border-t-0 pt-3 md:pt-0 border-gray-100">
                                <div class="font-black text-xl text-emerald-600">Rp {{ number_format($sch->price, 0, ',', '.') }}<span class="text-xs text-gray-400 font-normal"> /org</span></div>
                                
                                @if($isFull)
                                    <form action="{{ route('customer.waiting_list.join') }}" method="POST" class="mt-1">
                                        @csrf
                                        <input type="hidden" name="schedule_id" value="{{ $sch->id }}">
                                        <input type="hidden" name="passengers_count" value="{{ $searchParams['passengers'] }}">
                                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-1.5 rounded-xl shadow transition flex items-center space-x-1">
                                            <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                                            <span>Gabung Waiting List</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs font-bold text-indigo-600 group-hover:underline">Pilih Jadwal <i class="fa-solid fa-circle-check ml-1"></i></span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-xs">
                            <i class="fa-solid fa-triangle-exclamation text-2xl text-amber-500 mb-2 block"></i>
                            Tidak ada jadwal kepulangan yang tersedia untuk tanggal ini.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if(count($outboundSchedules) > 0)
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition text-sm flex items-center justify-center space-x-2">
                <span>Lanjut Ke Pemilihan Kursi</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        @endif
    </form>
</div>
@endsection
