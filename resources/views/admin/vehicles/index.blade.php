@extends('layouts.admin')

@section('title', 'Kelola Armada - SIPP Admin')
@section('page_title', 'Manajemen Armada & Konfigurasi Kursi')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-base font-bold text-gray-800">Daftar Kendaraan Travel</h2>
        <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Armada Baru</span>
        </button>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Nama Kendaraan</th>
                    <th class="p-4">Plat Nomor</th>
                    <th class="p-4">Kapasitas</th>
                    <th class="p-4">Jumlah Kursi Aktif</th>
                    <th class="p-4">Fasilitas</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($vehicles as $v)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-bold text-gray-900 text-sm">{{ $v->name }}</td>
                        <td class="p-4 font-mono font-bold text-blue-900">{{ $v->license_plate }}</td>
                        <td class="p-4 font-bold">{{ $v->capacity }} Penumpang</td>
                        <td class="p-4">{{ $v->seats_count }} Kursi (A1..{{ chr(64 + ceil($v->capacity/2)) }}2)</td>
                        <td class="p-4 text-gray-500 max-w-xs truncate">{{ $v->facilities ?? '-' }}</td>
                        <td class="p-4">
                            @if($v->status === 'ACTIVE')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Aktif</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Maintenance</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.vehicles.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Hapus kendaraan ini beserta kursinya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-400">Belum ada armada terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Vehicle Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showModal = false">
            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Tambah Armada Kendaraan Baru</h3>

            <form action="{{ route('admin.vehicles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Tipe Kendaraan</label>
                    <input type="text" name="name" required placeholder="Toyota Hiace Executive" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Plat Nomor (Nomor Polisi)</label>
                    <input type="text" name="license_plate" required placeholder="BG 1234 XX" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs uppercase focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Tempat Duduk (Kursi)</label>
                    <input type="number" name="capacity" value="12" min="4" max="24" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                    <span class="text-[10px] text-gray-400">Konfigurasi tata letak kursi akan dibuat otomatis oleh sistem.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Fasilitas (Opsional)</label>
                    <input type="text" name="facilities" placeholder="AC, Reclining Seat, USB Charger" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex space-x-3 pt-3">
                    <button type="button" @click="showModal = false" class="flex-1 bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl text-xs">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-xl text-xs shadow">Simpan & Generate Kursi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
