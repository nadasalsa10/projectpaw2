@extends('layouts.app')

@section('title', 'Profil Saya - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4">
    <div class="max-w-2xl mx-auto flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">Profil Akun Customer</h1>
            <p class="text-xs text-blue-200">Kelola informasi pribadi dan data kontak Anda.</p>
        </div>
        <a href="{{ route('customer.home') }}" class="bg-blue-800 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition border border-blue-700 flex items-center">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Beranda
        </a>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('customer.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-300 rounded-xl text-sm text-gray-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Utama</label>
                <textarea name="address" rows="3" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">{{ old('address', $user->customer?->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Kontak Darurat</label>
                <input type="text" name="emergency_phone" value="{{ old('emergency_phone', $user->customer?->emergency_phone) }}" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow transition text-sm">
                Simpan Perubahan Profil
            </button>
        </form>

        <hr class="my-6 border-gray-100">

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold py-3 rounded-xl text-sm border border-rose-200 transition flex items-center justify-center space-x-2">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar Dari Akun</span>
            </button>
        </form>
    </div>
</div>
@endsection
