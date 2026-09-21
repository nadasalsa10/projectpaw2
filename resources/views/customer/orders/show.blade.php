@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $booking->booking_code . ' - Live Tracking SIPP')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-container {
        font-family: inherit;
        z-index: 10;
    }
    .custom-car-marker {
        transition: transform 0.8s ease-in-out;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.6); opacity: 0.9; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    .pulse-halo {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: rgba(37, 99, 235, 0.5);
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
</style>
@endpush

@section('content')
<div x-data="customerOrderLive()" x-init="initMapAndPolling()">
    <!-- Top Bar -->
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white py-6 px-4 shadow-md">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('customer.orders.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-3 py-2 rounded-xl transition border border-white/10 flex items-center backdrop-blur">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-lg sm:text-xl font-black tracking-wide">Detail Booking {{ $booking->booking_code }}</h1>
                    </div>
                    <p class="text-xs text-blue-200">Dibuat pada: {{ $booking->created_at->format('d M Y - H:i') }} WIB</p>
                </div>
            </div>
            <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto">
                <span class="text-xs font-extrabold px-3.5 py-1.5 rounded-full border shadow-sm"
                      :class="statusBadgeClass"
                      x-text="bookingStatus">
                    {{ $booking->status }}
                </span>
                <span class="flex items-center text-[11px] text-emerald-300 font-bold mt-1 bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-700/50">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping mr-1.5 inline-block"></span>
                    <span>Live GPS Active</span>
                </span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

        <!-- Trip Progress Stepper -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Perjalanan Real-Time</h2>
                <span class="text-xs text-blue-600 font-bold flex items-center">
                    <i class="fa-solid fa-satellite-dish mr-1 text-xs animate-pulse text-emerald-500"></i>
                    <span x-text="trackingStatusText">Pantauan Langsung Driver</span>
                </span>
            </div>
            
            <div class="flex justify-between items-center relative py-2">
                <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-1.5 bg-gray-200 rounded-full -z-0"></div>
                <div class="absolute left-4 top-1/2 -translate-y-1/2 h-1.5 bg-blue-600 rounded-full -z-0 transition-all duration-700" :style="'width: calc(' + (((currentStep - 1) / 3) * 100) + '% - 2rem)'"></div>

                <!-- Step 1 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs transition-all duration-300" :class="currentStep >= 1 ? 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500'">1</div>
                    <span class="text-[11px] font-bold mt-1.5 text-gray-700">Menunggu</span>
                </div>
                <!-- Step 2 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs transition-all duration-300" :class="currentStep >= 2 ? 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500'">2</div>
                    <span class="text-[11px] font-bold mt-1.5 text-gray-700">Boarding</span>
                </div>
                <!-- Step 3 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs transition-all duration-300" :class="currentStep >= 3 ? 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500'">3</div>
                    <span class="text-[11px] font-bold mt-1.5 text-gray-700">Dalam Jalan</span>
                </div>
                <!-- Step 4 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs transition-all duration-300" :class="currentStep >= 4 ? 'bg-emerald-600 text-white shadow-lg ring-4 ring-emerald-100' : 'bg-gray-200 text-gray-500'">4</div>
                    <span class="text-[11px] font-bold mt-1.5 text-gray-700">Tiba</span>
                </div>
            </div>
        </div>

        <!-- Real-Time Interactive Live Map Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg overflow-hidden relative">
            <!-- Map Header & Telemetry HUD -->
            <div class="bg-slate-900 text-white p-4 flex flex-wrap justify-between items-center gap-3 border-b border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                            Pantauan Peta Real-Time SIPP
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping inline-block"></span>
                        </h3>
                        <p class="text-[11px] text-gray-400">
                            Rute: <span class="text-blue-300 font-semibold" x-text="originName + ' → ' + destinationName">{{ $initialTracking['origin']['name'] ?? 'Palembang' }} → {{ $initialTracking['destination']['name'] ?? 'Tujuan' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Live Speed & Progress Badges -->
                <div class="flex items-center space-x-2">
                    <div class="bg-slate-800 border border-slate-700 px-3 py-1 rounded-xl text-right">
                        <span class="text-[9px] text-gray-400 block uppercase font-bold tracking-wider">Kecepatan</span>
                        <span class="text-xs font-black text-amber-400" x-text="currentSpeed + ' km/h'">{{ $initialTracking['current_speed'] ?? 0 }} km/h</span>
                    </div>
                    <div class="bg-slate-800 border border-slate-700 px-3 py-1 rounded-xl text-right">
                        <span class="text-[9px] text-gray-400 block uppercase font-bold tracking-wider">Progress</span>
                        <span class="text-xs font-black text-emerald-400" x-text="progressPercent + '%'">{{ $initialTracking['progress_percent'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- Responsive Map Container -->
            <div class="relative w-full h-[320px] sm:h-[400px] md:h-[460px] bg-slate-100">
                <div id="liveMap" class="w-full h-full"></div>

                <!-- Floating Map Controls Overlay -->
                <div class="absolute bottom-4 right-4 z-20 flex flex-col space-y-2">
                    <button type="button" @click="centerOnVehicle()" class="bg-white hover:bg-gray-100 text-gray-800 font-bold p-2.5 rounded-xl shadow-lg border border-gray-200 transition text-xs flex items-center space-x-1.5" title="Pusatkan ke Armada">
                        <i class="fa-solid fa-crosshairs text-blue-600 text-sm"></i>
                        <span class="hidden sm:inline text-[11px]">Pusatkan Armada</span>
                    </button>
                    <button type="button" @click="fitRouteBounds()" class="bg-white hover:bg-gray-100 text-gray-800 font-bold p-2.5 rounded-xl shadow-lg border border-gray-200 transition text-xs flex items-center space-x-1.5" title="Lihat Seluruh Rute">
                        <i class="fa-solid fa-route text-indigo-600 text-sm"></i>
                        <span class="hidden sm:inline text-[11px]">Lihat Rute</span>
                    </button>
                </div>

                <!-- Live Status Floating Ribbon -->
                <div class="absolute top-3 left-3 z-20 bg-slate-900/90 backdrop-blur border border-slate-700 text-white px-3 py-1.5 rounded-xl shadow-md text-xs flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium text-[11px]" x-text="trackingStatusText">{{ $initialTracking['status_text'] ?? 'Menunggu Keberangkatan' }}</span>
                </div>
            </div>

            <!-- Map Footer Telemetry & Titik Info -->
            <div class="bg-gray-50 border-t border-gray-200 p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-gray-600">
                <div class="flex items-start space-x-2">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">
                        <i class="fa-solid fa-location-dot"></i>
                    </span>
                    <div>
                        <strong class="text-gray-900 block font-bold">Titik Keberangkatan (Origin):</strong>
                        <span class="text-gray-600" x-text="originTerminal">{{ $initialTracking['origin']['terminal'] ?? 'Titik Asal' }}</span>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </span>
                    <div>
                        <strong class="text-gray-900 block font-bold">Titik Kedatangan (Tujuan):</strong>
                        <span class="text-gray-600" x-text="destinationTerminal">{{ $initialTracking['destination']['terminal'] ?? 'Titik Tujuan' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Trips & Assigned Driver Details -->
        @foreach($booking->bookingTrips as $bt)
            @php 
                $sch = $bt->schedule;
                $driverUser = $sch?->driver?->user;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                            {{ $bt->direction === 'OUTBOUND' ? 'Trip Keberangkatan' : 'Trip Kepulangan' }}
                        </span>
                        <h3 class="font-bold text-gray-900 text-base mt-1">
                            {{ $sch?->route?->origin ?? '-' }} → {{ $sch?->route?->destination ?? '-' }}
                        </h3>
                    </div>
                    <div class="text-right text-xs text-gray-600">
                        <p><i class="fa-regular fa-clock mr-1 text-blue-500"></i> {{ $sch?->departure_time ? $sch->departure_time->format('d M Y - H:i') : '-' }} WIB</p>
                        <p><i class="fa-solid fa-van-shuttle mr-1 text-indigo-500"></i> {{ $sch?->vehicle?->name ?? 'Armada Travel' }} ({{ $sch?->vehicle?->license_plate ?? '-' }})</p>
                    </div>
                </div>

                <!-- Assigned Driver Card -->
                <div class="bg-gradient-to-r from-slate-900 to-blue-950 text-white rounded-xl p-4 flex justify-between items-center shadow">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow ring-2 ring-emerald-400/30">
                            <i class="fa-solid fa-id-card-clip"></i>
                        </div>
                        <div>
                            <span class="text-[10px] text-emerald-400 font-bold uppercase block tracking-wider">Driver Penanggung Jawab</span>
                            <h4 class="font-bold text-sm text-white" x-text="driverName">{{ $driverUser?->name ?? 'TBA' }}</h4>
                            <p class="text-xs text-gray-300 font-mono"><i class="fa-solid fa-phone text-[10px] mr-1"></i> <span x-text="driverPhone || '{{ $driverUser?->phone ?? '-' }}'">{{ $driverUser?->phone ?? '-' }}</span></p>
                        </div>
                    </div>

                    <template x-if="driverPhone">
                        <a :href="'https://wa.me/' + driverPhone.replace(/[^0-9]/g, '')" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 shadow">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span class="hidden sm:inline">Hubungi Driver</span>
                        </a>
                    </template>
                    <template x-if="!driverPhone && '{{ $driverUser?->phone ?? '' }}'">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $driverUser->phone ?? '') }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 shadow">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span class="hidden sm:inline">Hubungi Driver</span>
                        </a>
                    </template>
                </div>

                <!-- E-Tickets List -->
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider pt-2">E-Ticket Penumpang</h4>
                <div class="space-y-3">
                    @php
                        $tripTickets = $booking->tickets->where('schedule_id', $bt->schedule_id);
                        if ($tripTickets->isEmpty()) {
                            $tripTickets = $booking->tickets;
                        }
                    @endphp

                    @foreach($tripTickets as $tkt)
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <span class="font-bold text-gray-900 text-sm block">{{ $tkt->passenger?->name ?? 'Penumpang' }}</span>
                                <span class="text-xs text-gray-500">Nomor Kursi: <strong class="text-emerald-600 font-bold text-sm">{{ $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-' }}</strong></span>
                            </div>
                            <div class="flex items-center space-x-3 w-full sm:w-auto">
                                @if($tkt->is_checked_in)
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-300 px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-circle-check"></i> Checked-In oleh Driver
                                    </span>
                                @else
                                    <span class="text-[11px] font-bold text-amber-700 bg-amber-100 border border-amber-300 px-2.5 py-1 rounded-full">
                                        Belum Check-In
                                    </span>
                                @endif
                                <a href="{{ route('customer.tickets.show', $tkt->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow">
                                    Lihat Tiket & QR
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Cancel Order Action (If allowed) -->
        @if(in_array($booking->status, ['PENDING_PAYMENT', 'PAID', 'CONFIRMED', 'WAITING_DEPARTURE']))
            <div class="pt-2 text-center">
                <form action="{{ route('customer.orders.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                    @csrf
                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold underline">
                        Batalkan Pemesanan Ini
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function customerOrderLive() {
    return {
        bookingStatus: @js($booking->status),
        currentStep: @js($currentStep ?? 1),
        driverName: @js($booking->bookingTrips->first()?->schedule?->driver?->user?->name ?? 'TBA'),
        driverPhone: @js($booking->bookingTrips->first()?->schedule?->driver?->user?->phone ?? null),
        
        // Tracking State
        tracking: @js($initialTracking),
        originName: @js($initialTracking['origin']['name'] ?? 'Palembang'),
        destinationName: @js($initialTracking['destination']['name'] ?? 'Tujuan'),
        originTerminal: @js($initialTracking['origin']['terminal'] ?? 'Titik Asal'),
        destinationTerminal: @js($initialTracking['destination']['terminal'] ?? 'Titik Tujuan'),
        trackingStatusText: @js($initialTracking['status_text'] ?? 'Menunggu di Terminal'),
        currentSpeed: @js($initialTracking['current_speed'] ?? 0),
        progressPercent: @js($initialTracking['progress_percent'] ?? 0),

        map: null,
        vehicleMarker: null,
        routePolyline: null,
        originMarker: null,
        destMarker: null,
        pollTimer: null,

        get statusBadgeClass() {
            switch(this.bookingStatus) {
                case 'COMPLETED':
                case 'ARRIVED':
                    return 'bg-emerald-700 text-white border-emerald-500';
                case 'IN_TRANSIT':
                case 'BOARDING':
                    return 'bg-amber-600 text-white border-amber-400';
                case 'CANCELLED':
                    return 'bg-rose-700 text-white border-rose-500';
                default:
                    return 'bg-blue-700 text-white border-blue-500';
            }
        },

        initMapAndPolling() {
            this.$nextTick(() => {
                this.setupLeafletMap();
                this.startPolling();
            });
        },

        setupLeafletMap() {
            if (!this.tracking) return;

            const mapEl = document.getElementById('liveMap');
            if (!mapEl) return;

            const originCoords = [this.tracking.origin.lat, this.tracking.origin.lng];
            const destCoords = [this.tracking.destination.lat, this.tracking.destination.lng];
            const vehicleCoords = [this.tracking.current_location.lat, this.tracking.current_location.lng];
            const waypoints = this.tracking.waypoints || [originCoords, destCoords];

            // Initialize map
            this.map = L.map('liveMap', {
                zoomControl: false,
                attributionControl: false
            }).setView(vehicleCoords, 10);

            // Add Zoom control to top-right
            L.control.zoom({ position: 'topright' }).addTo(this.map);

            // Add Clean CartoDB / OSM Tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
            }).addTo(this.map);

            // Custom SVG Icons
            const createCustomIcon = (htmlContent, size = [36, 36]) => {
                return L.divIcon({
                    className: 'custom-map-icon',
                    html: htmlContent,
                    iconSize: size,
                    iconAnchor: [size[0] / 2, size[1] / 2],
                    popupAnchor: [0, -size[1] / 2]
                });
            };

            // Origin Marker Icon
            const originIcon = createCustomIcon(`
                <div style="background-color: #2563eb; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(37,99,235,0.4); border: 2px solid white; font-size: 14px;">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            `);

            // Destination Marker Icon
            const destIcon = createCustomIcon(`
                <div style="background-color: #10b981; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(16,185,129,0.4); border: 2px solid white; font-size: 14px;">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
            `);

            // Vehicle Marker Icon with Animated Radar Halo
            const vehicleIcon = createCustomIcon(`
                <div style="position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                    <div class="pulse-halo"></div>
                    <div style="background: linear-gradient(135deg, #1d4ed8, #4338ca); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(30,58,138,0.5); border: 2px solid white; font-size: 15px; position: relative; z-index: 2;">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </div>
                </div>
            `, [44, 44]);

            // Draw Multi-layer Real Road Polyline (Road Glow + Core Line)
            this.routeGlowPolyline = L.polyline(waypoints, {
                color: '#3b82f6',
                weight: 9,
                opacity: 0.35,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(this.map);

            this.routePolyline = L.polyline(waypoints, {
                color: '#1d4ed8',
                weight: 5,
                opacity: 0.95,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(this.map);

            // Add Origin & Dest Markers
            this.originMarker = L.marker(originCoords, { icon: originIcon }).addTo(this.map)
                .bindPopup(`<strong>Titik Asal: ${this.tracking.origin.name}</strong><br><small>${this.tracking.origin.terminal}</small>`);

            this.destMarker = L.marker(destCoords, { icon: destIcon }).addTo(this.map)
                .bindPopup(`<strong>Titik Tujuan: ${this.tracking.destination.name}</strong><br><small>${this.tracking.destination.terminal}</small>`);

            // Add Vehicle Marker
            this.vehicleMarker = L.marker(vehicleCoords, { icon: vehicleIcon, zIndexOffset: 1000 }).addTo(this.map)
                .bindPopup(`<strong>${this.tracking.vehicle.name} (${this.tracking.vehicle.plate})</strong><br>Driver: ${this.driverName}`);

            // Fit initial map bounds
            this.fitRouteBounds();

            // Client-side high-resolution real road alignment
            this.fetchExactClientRoad(originCoords, destCoords);

            // Window resize responsive handler
            window.addEventListener('resize', () => {
                if (this.map) this.map.invalidateSize();
            });
        },

        async fetchExactClientRoad(origin, dest) {
            try {
                const url = `https://router.project-osrm.org/route/v1/driving/${origin[1]},${origin[0]};${dest[1]},${dest[0]}?overview=full&geometries=geojson`;
                const res = await fetch(url);
                if (res.ok) {
                    const data = await res.json();
                    if (data.routes && data.routes[0] && data.routes[0].geometry) {
                        const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                        if (coords.length > 0) {
                            this.routePolyline.setLatLngs(coords);
                            this.routeGlowPolyline.setLatLngs(coords);
                            this.tracking.waypoints = coords;
                        }
                    }
                }
            } catch (e) {
                // Fallback already drawn
            }
        },

        fitRouteBounds() {
            if (this.map && this.routePolyline) {
                this.map.fitBounds(this.routePolyline.getBounds(), { padding: [40, 40] });
            }
        },

        centerOnVehicle() {
            if (this.map && this.vehicleMarker) {
                this.map.setView(this.vehicleMarker.getLatLng(), 13, { animate: true });
            }
        },

        startPolling() {
            this.pollTimer = setInterval(() => {
                this.fetchStatus();
            }, 3000);
        },

        async fetchStatus() {
            try {
                const res = await fetch("{{ route('customer.orders.live_status', $booking->id) }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.status) {
                        this.bookingStatus = data.status;
                        this.currentStep = data.step;
                        if (data.driver_name) {
                            this.driverName = data.driver_name;
                        }
                        if (data.driver_phone) {
                            this.driverPhone = data.driver_phone;
                        }
                    }

                    if (data.tracking) {
                        this.tracking = data.tracking;
                        this.originName = data.tracking.origin.name;
                        this.destinationName = data.tracking.destination.name;
                        this.originTerminal = data.tracking.origin.terminal;
                        this.destinationTerminal = data.tracking.destination.terminal;
                        this.trackingStatusText = data.tracking.status_text;
                        this.currentSpeed = data.tracking.current_speed;
                        this.progressPercent = data.tracking.progress_percent;

                        // Update Vehicle Marker Position on Live Map smoothly
                        if (this.vehicleMarker && data.tracking.current_location) {
                            const newLatLng = L.latLng(data.tracking.current_location.lat, data.tracking.current_location.lng);
                            this.vehicleMarker.setLatLng(newLatLng);
                        }
                    }
                }
            } catch (err) {
                console.error("Order live sync error:", err);
            }
        }
    };
}
</script>
@endpush
@endsection

