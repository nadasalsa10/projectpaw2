@extends('layouts.admin')

@section('title', 'Data Customer - SIPP Admin')
@section('page_title', 'Manajemen Data Customer')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="p-4">Nama Customer</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Nomor HP</th>
                    <th class="p-4">Alamat</th>
                    <th class="p-4">Kontak Darurat</th>
                    <th class="p-4">Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($customers as $c)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-bold text-gray-900 text-sm">{{ $c->user->name }}</td>
                        <td class="p-4 font-semibold text-blue-900">{{ $c->user->email }}</td>
                        <td class="p-4 font-mono">{{ $c->user->phone }}</td>
                        <td class="p-4 max-w-xs truncate text-gray-500">{{ $c->address ?? '-' }}</td>
                        <td class="p-4 font-mono">{{ $c->emergency_phone ?? '-' }}</td>
                        <td class="p-4 text-gray-500">{{ $c->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada data customer terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
