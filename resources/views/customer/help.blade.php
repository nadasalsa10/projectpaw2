@extends('layouts.app')

@section('title', 'Bantuan & FAQ - SIPP')

@section('content')
<div class="bg-blue-900 text-white py-6 px-4">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-xl font-bold">Pusat Bantuan & FAQ</h1>
        <p class="text-xs text-blue-200">Informasi seputar cara pesan tiket travel dan aturan sistem SIPP.</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8 space-y-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
        <h3 class="font-bold text-gray-900 text-sm mb-2"><i class="fa-solid fa-circle-question text-blue-600 mr-2"></i> Bagaimana cara melakukan pemesanan tiket travel?</h3>
        <p class="text-xs text-gray-600 leading-relaxed">Pilih rute asal dan tujuan di halaman utama, tentukan jenis perjalanan (Sekali Jalan atau Pulang-Pergi), tanggal pergi, dan jumlah penumpang. Pilih jadwal yang sesuai, pilih kursi favorit Anda, isi data penumpang, dan lakukan pembayaran.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
        <h3 class="font-bold text-gray-900 text-sm mb-2"><i class="fa-solid fa-qrcode text-blue-600 mr-2"></i> Bagaimana cara menggunakan E-Ticket dan QR Code saat keberangkatan?</h3>
        <p class="text-xs text-gray-600 leading-relaxed">Setelah pembayaran berhasil, buka menu <strong>Pesanan Saya</strong> dan pilih tiket Anda. Tunjukkan QR Code pada E-Ticket kepada Driver travel saat boarding untuk dilakukan verifikasi scan QR.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
        <h3 class="font-bold text-gray-900 text-sm mb-2"><i class="fa-solid fa-headset text-blue-600 mr-2"></i> Butuh bantuan lebih lanjut?</h3>
        <p class="text-xs text-gray-600 leading-relaxed">Hubungi Customer Support SIPP melalui WhatsApp <strong>0812-3456-7890</strong> atau email ke <strong>support@sipp-travel.com</strong> (Layanan 24/7).</p>
    </div>
</div>
@endsection
