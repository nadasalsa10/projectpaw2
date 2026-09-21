@extends('layouts.driver')

@section('title', 'Manifest & Pantauan Trip ' . $schedule->route->origin . ' - SIPP Driver')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-container {
        font-family: inherit;
        z-index: 10;
    }
    @keyframes driver-pulse {
        0% { transform: scale(0.6); opacity: 0.9; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    .driver-halo {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: rgba(34, 197, 94, 0.5);
        animation: driver-pulse 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
</style>
@endpush

@section('content')
<div class="space-y-4" x-data="driverManifest()" x-init="initMapAndPolling()">
    <!-- Top Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('driver.reports') }}" class="inline-flex items-center text-xs font-bold text-gray-300 hover:text-white bg-gray-800 border border-gray-700 hover:bg-gray-700 px-3 py-1.5 rounded-xl transition shadow">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Laporan
        </a>
        <div class="flex items-center space-x-2">
            <span class="flex items-center text-[10px] text-emerald-400 font-semibold bg-emerald-950 px-2 py-0.5 rounded-full border border-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping mr-1.5"></span> Live GPS Sync
            </span>
            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center text-xs font-bold text-emerald-400 hover:text-emerald-300">
                <i class="fa-solid fa-gauge mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Header Trip Card -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 sm:p-5 shadow">
        <div class="flex justify-between items-start border-b border-gray-700 pb-3 mb-3">
            <div>
                <span class="text-xs font-bold text-emerald-400 bg-emerald-950 px-2.5 py-0.5 rounded-lg border border-emerald-800">
                    {{ $schedule->route->origin }} → {{ $schedule->route->destination }}
                </span>
                <h2 class="text-lg font-black text-white mt-1.5">{{ $schedule->departure_time->format('d M Y - H:i') }} WIB</h2>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-900 text-emerald-300 border border-emerald-700" x-text="tripStatus">
                {{ $schedule->status }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-300">
            <p><i class="fa-solid fa-van-shuttle text-emerald-400 mr-1.5"></i> Armada: <strong class="text-white">{{ $schedule->vehicle->name }}</strong> ({{ $schedule->vehicle->license_plate }})</p>
            <p><i class="fa-solid fa-clock text-gray-400 mr-1.5"></i> Estimasi Tiba: <strong class="text-white">{{ $schedule->arrival_time->format('H:i') }} WIB</strong></p>
        </div>

        <!-- Trip Status Advance Controls -->
        <div class="mt-4 pt-3 border-t border-gray-700">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block mb-2">Kontrol Status Perjalanan (Sinkron Realtime ke Penumpang)</span>
            <form action="{{ route('driver.trip.status', $schedule->id) }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @csrf
                <button type="submit" name="status" value="BOARDING" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2.5 rounded-xl transition shadow flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-users"></i>
                    <span>BOARDING</span>
                </button>
                <button type="submit" name="status" value="IN_TRANSIT" class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs py-2.5 rounded-xl transition shadow flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>IN TRANSIT</span>
                </button>
                <button type="submit" name="status" value="ARRIVED" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs py-2.5 rounded-xl transition shadow flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-flag-checkered"></i>
                    <span>TIBA DI TUJUAN</span>
                </button>
                <button type="submit" name="status" value="COMPLETED" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs py-2.5 rounded-xl transition shadow flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>SELESAI TRIP</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Live Interactive Map & GPS Broadcaster Card -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden shadow">
        <!-- Map Header & GPS Broadcast Toggle -->
        <div class="bg-gray-900 p-4 border-b border-gray-700 flex flex-wrap justify-between items-center gap-3">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-location-crosshairs text-emerald-400"></i>
                    Peta Pantauan GPS Armada Real-Time
                </h3>
                <p class="text-[11px] text-gray-400 mt-0.5">
                    Rute: <span class="text-emerald-300 font-semibold">{{ $schedule->route->origin }} → {{ $schedule->route->destination }}</span>
                </p>
            </div>

            <!-- GPS Live Toggle Button -->
            <div class="flex items-center space-x-2">
                <button type="button" @click="toggleGpsBroadcasting()" 
                        :class="isGpsBroadcasting ? 'bg-emerald-600 hover:bg-emerald-500 ring-2 ring-emerald-400 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-200'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow">
                    <span class="w-2 h-2 rounded-full" :class="isGpsBroadcasting ? 'bg-white animate-ping' : 'bg-gray-400'"></span>
                    <span x-text="isGpsBroadcasting ? '📡 GPS Realtime Aktif' : '📡 Aktifkan GPS Perangkat'"></span>
                </button>
            </div>
        </div>

        <!-- Responsive Map Canvas -->
        <div class="relative w-full h-[300px] sm:h-[380px] bg-gray-950">
            <div id="driverLiveMap" class="w-full h-full"></div>

            <!-- Floating Controls -->
            <div class="absolute bottom-4 right-4 z-20 flex flex-col space-y-2">
                <button type="button" @click="centerOnDriverVehicle()" class="bg-gray-800/90 hover:bg-gray-700 text-white font-bold p-2.5 rounded-xl shadow-lg border border-gray-600 transition text-xs flex items-center space-x-1.5">
                    <i class="fa-solid fa-crosshairs text-emerald-400 text-sm"></i>
                    <span class="hidden sm:inline text-[11px]">Pusatkan Armada</span>
                </button>
                <button type="button" @click="fitDriverRouteBounds()" class="bg-gray-800/90 hover:bg-gray-700 text-white font-bold p-2.5 rounded-xl shadow-lg border border-gray-600 transition text-xs flex items-center space-x-1.5">
                    <i class="fa-solid fa-route text-blue-400 text-sm"></i>
                    <span class="hidden sm:inline text-[11px]">Lihat Rute</span>
                </button>
            </div>

            <!-- Live Status Floating Ribbon -->
            <div class="absolute top-3 left-3 z-20 bg-gray-900/90 backdrop-blur border border-gray-700 text-white px-3 py-1.5 rounded-xl shadow text-xs flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-medium text-[11px]" x-text="trackingStatusText">{{ $initialTracking['status_text'] ?? 'Standby' }}</span>
                <span class="text-amber-400 font-bold ml-1" x-text="'(' + currentSpeed + ' km/h)'"></span>
            </div>
        </div>

        <!-- GPS Telemetry Details Bar -->
        <div class="bg-gray-900/80 p-3 text-xs text-gray-300 grid grid-cols-2 sm:grid-cols-4 gap-2 border-t border-gray-700">
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold">Titik Keberangkatan</span>
                <span class="text-white font-semibold truncate block">{{ $initialTracking['origin']['name'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold">Titik Kedatangan</span>
                <span class="text-white font-semibold truncate block">{{ $initialTracking['destination']['name'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold">Status GPS</span>
                <span class="font-bold" :class="isGpsBroadcasting ? 'text-emerald-400' : 'text-gray-400'" x-text="gpsStatusMessage">Standby</span>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold">Terakhir Update</span>
                <span class="text-white font-mono" x-text="lastGpsUpdate || '{{ now()->format('H:i:s') }} WIB'">-</span>
            </div>
        </div>
    </div>

    <!-- Passenger Manifest List -->
    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-4 sm:p-5 shadow">
        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-700">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">
                <i class="fa-solid fa-users text-emerald-400 mr-1.5"></i> Manifest Penumpang 
                <span class="text-emerald-400 font-mono" x-text="'(' + tickets.length + ' Orang)'">({{ $schedule->tickets->count() }})</span>
            </h3>
            <a href="{{ route('driver.scan') }}" class="text-[11px] bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 rounded-xl shadow transition">
                + Scan QR Check-in
            </a>
        </div>

        <template x-if="tickets.length > 0">
            <div class="space-y-2">
                <template x-for="tkt in tickets" :key="tkt.id">
                    <div class="bg-gray-900 border border-gray-700/80 rounded-xl p-3 flex justify-between items-center transition hover:border-gray-500">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-7 h-7 rounded-lg bg-emerald-900 text-emerald-300 font-extrabold text-xs flex items-center justify-center border border-emerald-700 shadow" x-text="tkt.seat_number"></span>
                                <span class="font-bold text-white text-sm" x-text="tkt.passenger_name"></span>
                            </div>
                            <span class="text-[10px] text-gray-400 block mt-0.5 ml-9" x-text="'Booking: ' + tkt.booking_code + ' | Tel: ' + tkt.passenger_phone"></span>
                        </div>

                        <div>
                            <template x-if="tkt.is_checked_in">
                                <span class="text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-700 px-3 py-1.5 rounded-full flex items-center">
                                    <i class="fa-solid fa-circle-check mr-1.5"></i> Checked-In
                                </span>
                            </template>
                            <template x-if="!tkt.is_checked_in">
                                <form :action="'/driver/checkin/' + tkt.id" method="POST" @submit="return confirm('Validasi check-in untuk ' + tkt.passenger_name + '?')">
                                    @csrf
                                    <button type="submit" class="text-[10px] bg-amber-600 hover:bg-amber-500 text-white font-bold px-3 py-1.5 rounded-full shadow transition">
                                        [ CHECK-IN ]
                                    </button>
                                </form>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="tickets.length === 0">
            <div class="text-center py-6 text-xs text-gray-500">
                Belum ada penumpang terdaftar di trip ini.
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function driverManifest() {
    return {
        tripStatus: @js($schedule->status),
        tickets: @js($manifestArray),
        tracking: @js($initialTracking),
        trackingStatusText: @js($initialTracking['status_text'] ?? 'Standby'),
        currentSpeed: @js($initialTracking['current_speed'] ?? 0),
        
        map: null,
        vehicleMarker: null,
        routePolyline: null,
        originMarker: null,
        destMarker: null,
        pollTimer: null,
        
        // GPS Broadcaster state
        isGpsBroadcasting: false,
        gpsWatchId: null,
        gpsStatusMessage: 'GPS Standby (Simulasi Otomatis)',
        lastGpsUpdate: null,

        initMapAndPolling() {
            this.$nextTick(() => {
                this.setupDriverMap();
                this.startPolling();
            });
        },

        setupDriverMap() {
            if (!this.tracking) return;

            const mapEl = document.getElementById('driverLiveMap');
            if (!mapEl) return;

            const originCoords = [this.tracking.origin.lat, this.tracking.origin.lng];
            const destCoords = [this.tracking.destination.lat, this.tracking.destination.lng];
            const vehicleCoords = [this.tracking.current_location.lat, this.tracking.current_location.lng];
            const waypoints = this.tracking.waypoints || [originCoords, destCoords];

            this.map = L.map('driverLiveMap', {
                zoomControl: false,
                attributionControl: false
            }).setView(vehicleCoords, 10);

            L.control.zoom({ position: 'topright' }).addTo(this.map);

            // Dark Basemap tile layer for Driver Portal
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
            }).addTo(this.map);

            const createCustomIcon = (htmlContent, size = [36, 36]) => {
                return L.divIcon({
                    className: 'custom-driver-icon',
                    html: htmlContent,
                    iconSize: size,
                    iconAnchor: [size[0] / 2, size[1] / 2],
                    popupAnchor: [0, -size[1] / 2]
                });
            };

            const originIcon = createCustomIcon(`
                <div style="background-color: #2563eb; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(37,99,235,0.4); border: 2px solid white; font-size: 14px;">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            `);

            const destIcon = createCustomIcon(`
                <div style="background-color: #10b981; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(16,185,129,0.4); border: 2px solid white; font-size: 14px;">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
            `);

            const vehicleIcon = createCustomIcon(`
                <div style="position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                    <div class="driver-halo"></div>
                    <div style="background: linear-gradient(135deg, #16a34a, #047857); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(4,120,87,0.5); border: 2px solid white; font-size: 15px; position: relative; z-index: 2;">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </div>
                </div>
            `, [44, 44]);

            // Draw Multi-layer Real Road Polyline (Road Glow + Core Line)
            this.routeGlowPolyline = L.polyline(waypoints, {
                color: '#34d399',
                weight: 9,
                opacity: 0.35,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(this.map);

            this.routePolyline = L.polyline(waypoints, {
                color: '#059669',
                weight: 5,
                opacity: 0.95,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(this.map);

            this.originMarker = L.marker(originCoords, { icon: originIcon }).addTo(this.map)
                .bindPopup(`<strong>Titik Asal: ${this.tracking.origin.name}</strong><br><small>${this.tracking.origin.terminal}</small>`);

            this.destMarker = L.marker(destCoords, { icon: destIcon }).addTo(this.map)
                .bindPopup(`<strong>Titik Tujuan: ${this.tracking.destination.name}</strong><br><small>${this.tracking.destination.terminal}</small>`);

            this.vehicleMarker = L.marker(vehicleCoords, { icon: vehicleIcon, zIndexOffset: 1000 }).addTo(this.map)
                .bindPopup(`<strong>Armada Anda: ${this.tracking.vehicle.name}</strong><br>Plat: ${this.tracking.vehicle.plate}`);

            this.fitDriverRouteBounds();

            // Client-side high-resolution real road alignment
            this.fetchExactClientRoad(originCoords, destCoords);

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

        fitDriverRouteBounds() {
            if (this.map && this.routePolyline) {
                this.map.fitBounds(this.routePolyline.getBounds(), { padding: [40, 40] });
            }
        },

        centerOnDriverVehicle() {
            if (this.map && this.vehicleMarker) {
                this.map.setView(this.vehicleMarker.getLatLng(), 13, { animate: true });
            }
        },

        toggleGpsBroadcasting() {
            if (this.isGpsBroadcasting) {
                if (this.gpsWatchId) {
                    navigator.geolocation.clearWatch(this.gpsWatchId);
                    this.gpsWatchId = null;
                }
                this.isGpsBroadcasting = false;
                this.gpsStatusMessage = 'GPS Dimatikan (Simulasi Otomatis)';
            } else {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung Geolocation GPS.');
                    return;
                }

                this.gpsStatusMessage = 'Mencari sinyal GPS perangkat...';
                this.gpsWatchId = navigator.geolocation.watchPosition(
                    (position) => {
                        this.isGpsBroadcasting = true;
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const speed = Math.round((position.coords.speed || 0) * 3.6); // Convert m/s to km/h

                        this.sendGpsLocationToServer(lat, lng, speed);
                        this.gpsStatusMessage = `🟢 GPS Terhubung (Akurasi: ±${Math.round(position.coords.accuracy)}m)`;
                    },
                    (error) => {
                        console.error('GPS error:', error);
                        this.isGpsBroadcasting = false;
                        this.gpsStatusMessage = 'Gagal mengakses GPS: ' + error.message;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            }
        },

        async sendGpsLocationToServer(latitude, longitude, speed) {
            try {
                const response = await fetch("{{ route('driver.trip.location', $schedule->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        speed: speed
                    })
                });

                if (response.ok) {
                    const resData = await response.json();
                    this.lastGpsUpdate = resData.timestamp;

                    // Move driver vehicle marker
                    if (this.vehicleMarker) {
                        this.vehicleMarker.setLatLng(L.latLng(latitude, longitude));
                    }
                }
            } catch (err) {
                console.error("Failed to transmit GPS:", err);
            }
        },

        startPolling() {
            this.pollTimer = setInterval(() => {
                this.fetchManifest();
            }, 3000);
        },

        async fetchManifest() {
            try {
                const res = await fetch("{{ route('driver.api.live_manifest', $schedule->id) }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.tripStatus = data.status;
                        this.tickets = data.tickets;

                        if (data.tracking) {
                            this.tracking = data.tracking;
                            this.trackingStatusText = data.tracking.status_text;
                            this.currentSpeed = data.tracking.current_speed;

                            if (this.vehicleMarker && data.tracking.current_location && !this.isGpsBroadcasting) {
                                this.vehicleMarker.setLatLng(L.latLng(data.tracking.current_location.lat, data.tracking.current_location.lng));
                            }
                        }
                    }
                }
            } catch (err) {
                console.error("Manifest live sync error:", err);
            }
        }
    };
}
</script>
@endpush
@endsection

