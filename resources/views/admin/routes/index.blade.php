@extends('layouts.admin')

@section('title', 'Kelola Rute - SIPP Admin')
@section('page_title', 'Manajemen Rute Perjalanan')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-base font-bold text-gray-800">Daftar Rute Travel</h2>
        <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Rute Baru</span>
        </button>
    </div>

    <!-- Routes Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Kota Asal</th>
                    <th class="p-4">Kota Tujuan</th>
                    <th class="p-4">Estimasi Durasi</th>
                    <th class="p-4">Harga Dasar</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($routes as $r)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-bold text-gray-500">#{{ $r->id }}</td>
                        <td class="p-4 font-bold text-gray-900 text-sm">{{ $r->origin }}</td>
                        <td class="p-4 font-bold text-gray-900 text-sm">{{ $r->destination }}</td>
                        <td class="p-4">{{ floor($r->duration_minutes / 60) }}j {{ $r->duration_minutes % 60 }}m ({{ $r->duration_minutes }} menit)</td>
                        <td class="p-4 font-black text-emerald-600 text-sm">Rp {{ number_format($r->base_price, 0, ',', '.') }}</td>
                        <td class="p-4">
                            @if($r->is_active)
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Aktif</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.routes.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus rute ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-400">Belum ada rute terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Route Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showModal = false">
            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Tambah Rute Travel Baru</h3>

            <form action="{{ route('admin.routes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kota Asal (Origin)</label>
                    <input type="text" name="origin" required placeholder="Palembang" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kota Tujuan (Destination)</label>
                    <input type="text" name="destination" required placeholder="Jambi" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Estimasi Durasi Perjalanan (Menit)</label>
                    <input type="number" name="duration_minutes" value="360" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Dasar (Rp)</label>
                    <input type="number" name="base_price" value="200000" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex space-x-3 pt-3">
                    <button type="button" @click="showModal = false" class="flex-1 bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl text-xs">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-xl text-xs shadow">Simpan Rute</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
