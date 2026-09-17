@extends('layouts.app')

@section('title', 'Notifikasi - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold">Notifikasi Saya</h1>
        <p class="text-xs text-blue-200">Pemberitahuan terkini seputar booking dan status perjalanan Anda.</p>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-8 space-y-3">
    @forelse($notifications as $notif)
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm flex items-start space-x-3">
            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-gray-900 text-sm">{{ $notif->title }}</h4>
                <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">{{ $notif->message }}</p>
                <span class="text-[10px] text-gray-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
            </div>
        </div>
    @empty
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-xs">
            Belum ada notifikasi masuk.
        </div>
    @endforelse
</div>
@endsection
