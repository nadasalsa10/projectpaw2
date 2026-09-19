@extends('layouts.driver')

@section('title', 'Profil Driver - SIPP Driver')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center text-xs font-bold text-gray-300 hover:text-white bg-gray-800 border border-gray-700 hover:bg-gray-700 px-3 py-1.5 rounded-xl transition shadow">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Dashboard
        </a>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 text-center shadow">
        <div class="w-16 h-16 bg-emerald-600 text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-3 shadow">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <h2 class="text-lg font-bold text-white">{{ Auth::user()->name }}</h2>
        <p class="text-xs text-emerald-400 font-mono mt-0.5">Pengemudi Resmi SIPP</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-5 shadow space-y-3 text-xs">
        <div class="flex justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400">Email:</span>
            <span class="font-semibold text-white">{{ Auth::user()->email }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400">Nomor Telepon:</span>
            <span class="font-semibold text-white">{{ Auth::user()->phone }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-gray-700">
            <span class="text-gray-400">Nomor SIM:</span>
            <span class="font-semibold text-emerald-400 font-mono">{{ $driver->driver?->license_number ?? 'B1-998877' }}</span>
        </div>
        <div class="flex justify-between py-2">
            <span class="text-gray-400">Status Operasional:</span>
            <span class="font-bold text-emerald-400 uppercase">{{ $driver->driver?->status ?? 'ACTIVE' }}</span>
        </div>
    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="w-full bg-rose-900/80 hover:bg-rose-800 text-rose-200 border border-rose-700 font-bold py-3 rounded-xl text-xs transition">
            Keluar Akun Driver
        </button>
    </form>
</div>
@endsection
