@extends('layouts.admin')

@section('title', 'Jadwal Perjalanan - SIPP Admin')
@section('page_title', 'Manajemen Jadwal Perjalanan (Schedules)')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-base font-bold text-gray-800">Daftar Jadwal Perjalanan Travel</h2>
            <p class="text-xs text-gray-500">Kelola rute, armada, driver, serta terbitkan jadwal tambahan extra mudik</p>
        </div>
        <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Terbitkan Jadwal Baru</span>
        </button>
    </div>

    <!-- Schedules Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Rute & Tipe</th>
                    <th class="p-4">Waktu Berangkat</th>
                    <th class="p-4">Estimasi Tiba</th>
                    <th class="p-4">Armada Mobil</th>
                    <th class="p-4">Driver (Pengemudi)</th>
                    <th class="p-4">Harga Tiket</th>
                    <th class="p-4">Status & Waiting List</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($schedules as $s)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4">
                            <span class="font-bold text-blue-900 text-sm block">{{ $s->route->origin }} → {{ $s->route->destination }}</span>
                            @if($s->is_extra)
                                <span class="inline-block mt-0.5 bg-amber-100 text-amber-800 font-extrabold text-[9px] px-2 py-0.5 rounded border border-amber-300">
                                    <i class="fa-solid fa-star mr-0.5 text-[8px]"></i> EXTRA MUDIK
                                </span>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-gray-900">{{ $s->departure_time->format('d M Y - H:i') }} WIB</td>
                        <td class="p-4 text-gray-600">{{ $s->arrival_time->format('H:i') }} WIB</td>
                        <td class="p-4">{{ $s->vehicle->name }} ({{ $s->vehicle->license_plate }})</td>
                        <td class="p-4 font-semibold text-gray-800">{{ $s->driver?->user->name ?? 'TBA' }}</td>
                        <td class="p-4 font-black text-emerald-600 text-sm">Rp {{ number_format($s->price, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <div class="space-y-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200 inline-block">
                                    {{ $s->status }}
                                </span>
                                @if(($s->waiting_lists_count ?? 0) > 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 inline-block">
                                        <i class="fa-solid fa-users mr-1"></i> Waiting List: {{ $s->waiting_lists_count }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-400">Belum ada jadwal terbit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Schedule Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showModal = false">
            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Terbitkan Jadwal Perjalanan Baru</h3>

            <form action="{{ route('admin.schedules.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Rute Perjalanan</label>
                    <select name="route_id" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                        @foreach($routes as $r)
                            <option value="{{ $r->id }}">{{ $r->origin }} → {{ $r->destination }} (Rp {{ number_format($r->base_price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Armada Mobil</label>
                    <select name="vehicle_id" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->license_plate }}) - Cap: {{ $v->capacity }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Driver (Tugaskan Pengemudi)</label>
                    <select name="driver_id" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->user->name }} (SIM: {{ $d->license_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal & Waktu Keberangkatan</label>
                    <input type="datetime-local" name="departure_time" value="{{ date('Y-m-d\TH:i', strtotime('+1 day 08:00')) }}" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Tiket per Kursi (Rp)</label>
                    <input type="number" name="price" value="200000" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_extra" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-bold text-amber-900">
                            <i class="fa-solid fa-star text-amber-600 mr-1"></i> Tandai Sebagai Jadwal Extra Mudik
                        </span>
                    </label>
                    <p class="text-[10px] text-amber-700 mt-1">Jadwal tambahan khusus periode lonjakan pemudik.</p>
                </div>

                <div class="flex space-x-3 pt-3">
                    <button type="button" @click="showModal = false" class="flex-1 bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl text-xs">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-xl text-xs shadow">Terbitkan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
