@extends('layouts.app')

@section('title', 'Pembayaran Tiket - SIPP')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    @if($booking->status === 'EXPIRED')
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-6 rounded-2xl text-center">
            <i class="fa-solid fa-clock-rotate-left text-4xl text-rose-500 mb-3 block"></i>
            <h2 class="text-lg font-bold">Waktu Pembayaran Telah Berakhir (Expired)</h2>
            <p class="text-xs text-rose-600 mt-1">Sesi pemesanan Anda telah kedaluwarsa. Kursi yang direservasi telah dilepaskan kembali.</p>
            <a href="{{ route('customer.home') }}" class="inline-block mt-4 bg-rose-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow">Pesan Ulang Travel</a>
        </div>
    @else
        <div x-data="{
            expiresAt: new Date('{{ $booking->expires_at->toIso8601String() }}').getTime(),
            timeLeft: '',
            updateTimer() {
                const now = new Date().getTime();
                const diff = this.expiresAt - now;
                if (diff <= 0) {
                    this.timeLeft = 'EXPIRED';
                    window.location.reload();
                } else {
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    this.timeLeft = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                }
            }
        }" x-init="updateTimer(); setInterval(() => updateTimer(), 1000)" class="space-y-6">

            <!-- Countdown Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-2xl p-5 shadow-lg flex justify-between items-center">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-100 block">Selesaikan Pembayaran Dalam</span>
                    <h3 class="text-2xl font-black" x-text="timeLeft">15:00</h3>
                </div>
                <div class="text-right">
                    <span class="text-xs text-amber-100 block">Kode Booking</span>
                    <span class="font-extrabold text-lg text-white font-mono">{{ $booking->booking_code }}</span>
                </div>
            </div>

            <!-- Payment Instructions Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex justify-between items-center">
                    <span>Instruksi Pembayaran</span>
                    <span class="text-xs bg-blue-100 text-blue-800 border border-blue-200 px-3 py-1 rounded-full font-bold">
                        {{ $booking->payment->payment_method }}
                    </span>
                </h2>

                <!-- 1. QRIS Payment Interface -->
                @if($booking->payment->payment_method === 'QRIS')
                    <div class="bg-gray-50 rounded-2xl border-2 border-rose-500/30 p-6 text-center mb-6 shadow-inner flex flex-col items-center">
                        <!-- QRIS Header Logo Badge -->
                        <div class="flex items-center space-x-2 mb-3 bg-white px-4 py-1.5 rounded-full border border-gray-200 shadow-sm">
                            <span class="font-black text-rose-600 text-lg tracking-wider">QRIS</span>
                            <span class="text-[10px] text-gray-500 font-semibold border-l border-gray-300 pl-2">Standar Pembayaran Nasional</span>
                        </div>

                        <span class="text-xs font-bold text-gray-800 uppercase block tracking-wider">PT SIPP TRAVEL INDONESIA</span>
                        <span class="text-[10px] text-gray-400 block mb-3 font-mono">NMID: ID10202699887766</span>

                        <!-- QR Code Image -->
                        <div class="bg-white p-4 rounded-2xl shadow-md border-2 border-gray-200 inline-block my-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode('00020101021226680016ID.CO.SIPP.WWW01189360091400000000005204581253033605802ID5924PT SIPP TRAVEL INDONESIA6009PALEMBANG61053011162070703A016304' . sprintf('%04X', crc32($booking->booking_code))) }}" alt="QRIS Code Pembayaran SIPP" class="w-56 h-56 object-contain mx-auto">
                        </div>

                        <div class="mt-3 text-xs text-gray-600 max-w-sm leading-relaxed">
                            Scan QRIS di atas menggunakan <strong>GoPay, OVO, ShopeePay, DANA, BCA Mobile, Livin' by Mandiri, BRImo</strong>, atau m-Banking / E-Wallet pilihan Anda.
                        </div>
                    </div>

                <!-- 2. Virtual Account Payment Interface -->
                @elseif($booking->payment->payment_method === 'VIRTUAL_ACCOUNT')
                    <div class="bg-blue-50/70 rounded-2xl border border-blue-200 p-6 text-center mb-6">
                        <span class="text-xs text-blue-800 font-bold block mb-1">Nomor Virtual Account Simulasi</span>
                        <span class="text-2xl font-black text-blue-900 font-mono tracking-widest block select-all">{{ $booking->payment->payment_code }}</span>
                        <span class="text-[11px] text-blue-700 mt-1 block">Atas Nama: <strong>PT SIPP TRAVEL INDONESIA</strong></span>
                        <p class="text-xs text-gray-500 mt-3">Dapat dibayar via ATM / M-Banking BCA, Mandiri, BNI, BRI, Permata.</p>
                    </div>

                <!-- 3. Bank Transfer Payment Interface -->
                @elseif($booking->payment->payment_method === 'BANK_TRANSFER')
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 text-center mb-6">
                        <span class="text-xs text-gray-500 block mb-1">Nomor Rekening Bank Transfer</span>
                        <span class="text-2xl font-black text-gray-900 font-mono tracking-widest block select-all">{{ $booking->payment->payment_code }}</span>
                        <span class="text-xs text-gray-700 font-bold block mt-1">Bank BCA - 8830-123-999</span>
                        <span class="text-[11px] text-gray-500 mt-0.5 block">Atas Nama: <strong>PT SIPP TRAVEL INDONESIA</strong></span>
                    </div>

                <!-- 4. E-Wallet Payment Interface -->
                @else
                    <div class="bg-indigo-50/70 rounded-2xl border border-indigo-200 p-6 text-center mb-6">
                        <span class="text-xs text-indigo-800 font-bold block mb-1">Kode Pembayaran E-Wallet</span>
                        <span class="text-2xl font-black text-indigo-900 font-mono tracking-widest block select-all">{{ $booking->payment->payment_code }}</span>
                        <span class="text-[11px] text-indigo-700 mt-1 block">Mendukung: <strong>DANA, OVO, GoPay, LinkAja, ShopeePay</strong></span>
                    </div>
                @endif

                <div class="flex justify-between items-center text-sm py-3 border-y border-gray-100 font-medium">
                    <span class="text-gray-600">Total Nominal Pembayaran:</span>
                    <span class="font-black text-xl text-emerald-600">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </div>

                <!-- Simulation Button -->
                <form action="{{ route('customer.payment.pay', $booking->id) }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit" onclick="return confirm('Simulasikan pembayaran sukses untuk pesanan ini?')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg hover:shadow-emerald-500/30 transition text-sm flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Konfirmasi Pembayaran (Simulasi Bayar)</span>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <a href="{{ route('customer.orders.index') }}" class="text-xs text-gray-500 hover:text-gray-800 font-medium">Bayar Nanti (Lihat Di Pesanan Saya)</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
