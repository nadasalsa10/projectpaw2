@extends('layouts.driver')

@section('title', 'Scan QR Tiket - SIPP Driver')

@section('content')
<div class="space-y-4" x-data="{
    scanResult: null,
    errorMessage: '',
    loading: false,
    manualCode: '',
    validateCode(codeToValidate) {
        if (!codeToValidate) return;
        this.loading = true;
        this.errorMessage = '';
        this.scanResult = null;

        fetch('{{ route('driver.scan.validate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: codeToValidate })
        })
        .then(res => res.json())
        .then(data => {
            this.loading = false;
            if (data.valid) {
                this.scanResult = data;
            } else {
                this.errorMessage = data.message || 'Tiket Tidak Valid!';
            }
        })
        .catch(err => {
            this.loading = false;
            this.errorMessage = 'Terjadi kesalahan koneksi server.';
        });
    },
    doCheckIn(ticketId) {
        fetch('/driver/checkin/' + ticketId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('CHECK-IN BERHASIL!');
                this.scanResult = null;
                window.location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}" x-init="
    const html5QrCode = new Html5Qrcode('qr-reader');
    html5QrCode.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: 250 },
        (decodedText) => {
            validateCode(decodedText);
        },
        (error) => {}
    ).catch(err => {
        console.log('Kamera tidak aktif atau tidak diizinkan, gunakan input manual.');
    });
">
    <!-- Title -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 text-center">
        <h1 class="text-base font-bold text-white">Scanner QR Tiket Penumpang</h1>
        <p class="text-xs text-gray-400 mt-0.5">Arahkan kamera ke QR Code Tiket Customer atau ketik Kode Tiket</p>
    </div>

    <!-- Camera Reader Area -->
    <div class="bg-gray-900 border-2 border-emerald-600/50 rounded-2xl overflow-hidden relative shadow-2xl p-2 min-h-[260px]">
        <div id="qr-reader" class="w-full"></div>
    </div>

    <!-- Manual Code Input -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4">
        <label class="block text-xs font-bold text-gray-300 mb-1.5">Atau Masukkan Kode Tiket / Hash Manual</label>
        <div class="flex space-x-2">
            <input type="text" x-model="manualCode" placeholder="Contoh: TKT-20260917-ABC12" class="flex-1 bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2 text-xs text-white uppercase focus:outline-none focus:border-emerald-500">
            <button type="button" @click="validateCode(manualCode)" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow">
                Validasi
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div x-show="loading" class="text-center py-4 text-xs text-emerald-400 font-bold" style="display: none;">
        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Memvalidasi Ke Backend...
    </div>

    <!-- Error Result Alert -->
    <div x-show="errorMessage" class="bg-rose-950 border border-rose-800 text-rose-200 p-4 rounded-2xl text-xs flex items-center" style="display: none;">
        <i class="fa-solid fa-circle-xmark text-rose-400 text-xl mr-3"></i>
        <span x-text="errorMessage"></span>
    </div>

    <!-- Valid Ticket Modal Result -->
    <template x-if="scanResult">
        <div class="bg-gray-800 border-2 border-emerald-500 rounded-2xl p-5 shadow-2xl space-y-3">
            <div class="flex justify-between items-center border-b border-gray-700 pb-2">
                <span class="text-xs font-extrabold text-emerald-400 uppercase tracking-wider"><i class="fa-solid fa-circle-check"></i> TIKET VALID</span>
                <span class="text-xs font-mono text-gray-400" x-text="scanResult.ticket.ticket_code"></span>
            </div>

            <div class="space-y-1 text-xs">
                <p class="text-gray-400">Nama Penumpang: <strong class="text-white text-sm" x-text="scanResult.ticket.passenger_name"></strong></p>
                <p class="text-gray-400">Nomor Kursi: <strong class="text-emerald-400 text-base" x-text="scanResult.ticket.seat_number"></strong></p>
                <p class="text-gray-400">Rute: <strong class="text-white" x-text="scanResult.ticket.route"></strong></p>
            </div>

            <template x-if="scanResult.already_checked_in">
                <div class="bg-amber-950/80 border border-amber-700 text-amber-300 text-xs p-3 rounded-xl">
                    Penumpang ini sudah melakukan Check-In sebelumnya.
                </div>
            </template>

            <template x-if="!scanResult.already_checked_in">
                <button type="button" @click="doCheckIn(scanResult.ticket.id)" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-lg">
                    [ VERIFIKASI & CHECK-IN PENUMPANG ]
                </button>
            </template>
        </div>
    </template>
</div>
@endsection
