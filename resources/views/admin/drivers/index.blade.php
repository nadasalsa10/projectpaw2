@extends('layouts.admin')

@section('title', 'Kelola Driver - SIPP Admin')
@section('page_title', 'Manajemen Pengemudi (Driver)')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-base font-bold text-gray-800">Daftar Pengemudi (Driver)</h2>
        <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Driver Baru</span>
        </button>
    </div>

    <!-- Drivers Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Nama Driver</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Nomor HP</th>
                    <th class="p-4">Nomor SIM</th>
                    <th class="p-4">Status Operasional</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($drivers as $d)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-bold text-gray-900 text-sm">{{ $d->user->name }}</td>
                        <td class="p-4 text-gray-600">{{ $d->user->email }}</td>
                        <td class="p-4 font-mono">{{ $d->user->phone }}</td>
                        <td class="p-4 font-mono font-bold text-blue-900">{{ $d->license_number }}</td>
                        <td class="p-4">
                            @if($d->status === 'ACTIVE')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Aktif</span>
                            @elseif($d->status === 'ON_TRIP')
                                <span class="bg-indigo-100 text-indigo-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Sedang Trip</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.drivers.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Hapus driver ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada driver terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Driver Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showModal = false">
            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Tambah Akun Driver Baru</h3>

            <form action="{{ route('admin.drivers.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap Driver</label>
                    <input type="text" name="name" required placeholder="Budi Santoso" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Driver</label>
                    <input type="email" name="email" required placeholder="driver2@example.com" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="phone" required placeholder="081234567890" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor SIM Driver</label>
                    <input type="text" name="license_number" required placeholder="SIM-B1-123456" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password Akun</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex space-x-3 pt-3">
                    <button type="button" @click="showModal = false" class="flex-1 bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl text-xs">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-xl text-xs shadow">Simpan Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
