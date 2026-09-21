<?php

namespace App\Services;

use App\Models\Schedule;

class GeoLocationService
{
    /**
     * Standard City/Terminal coordinates across Sumatra
     *
     * @var array<string, array{lat: float, lng: float, name: string, terminal: string}>
     */
    protected static array $cityCoordinates = [
        'Palembang' => [
            'lat' => -2.976073,
            'lng' => 104.775431,
            'name' => 'Palembang',
            'terminal' => 'Titik Kumpul Pusat SIPP Palembang (Jl. Kolonel H. Burlian)',
        ],
        'Jambi' => [
            'lat' => -1.610122,
            'lng' => 103.613123,
            'name' => 'Jambi',
            'terminal' => 'Titik SIPP Jambi (Terminal Alam Barajo)',
        ],
        'Bandar Lampung' => [
            'lat' => -5.429167,
            'lng' => 105.261111,
            'name' => 'Bandar Lampung',
            'terminal' => 'Titik SIPP Lampung (Terminal Rajabasa)',
        ],
        'Bengkulu' => [
            'lat' => -3.792845,
            'lng' => 102.260787,
            'name' => 'Bengkulu',
            'terminal' => 'Titik SIPP Bengkulu (Terminal Panorama)',
        ],
        'Padang' => [
            'lat' => -0.947083,
            'lng' => 100.417181,
            'name' => 'Padang',
            'terminal' => 'Titik SIPP Padang (Terminal Anak Air)',
        ],
        'Pekanbaru' => [
            'lat' => 0.507068,
            'lng' => 101.447779,
            'name' => 'Pekanbaru',
            'terminal' => 'Titik SIPP Pekanbaru (Terminal BRPS)',
        ],
        'Medan' => [
            'lat' => 3.595196,
            'lng' => 98.672223,
            'name' => 'Medan',
            'terminal' => 'Titik SIPP Medan (Terminal Terpadu Amplas)',
        ],
        'Lubuklinggau' => [
            'lat' => -3.295744,
            'lng' => 102.861427,
            'name' => 'Lubuklinggau',
            'terminal' => 'Titik SIPP Lubuklinggau (Terminal Petanang)',
        ],
        'Prabumulih' => [
            'lat' => -3.432617,
            'lng' => 104.236111,
            'name' => 'Prabumulih',
            'terminal' => 'Titik SIPP Prabumulih (Terminal Pasar)',
        ],
        'Lahat' => [
            'lat' => -3.788500,
            'lng' => 103.541300,
            'name' => 'Lahat',
            'terminal' => 'Titik SIPP Lahat (Terminal Lembayung)',
        ],
        'Baturaja' => [
            'lat' => -4.129300,
            'lng' => 104.170600,
            'name' => 'Baturaja',
            'terminal' => 'Titik SIPP Baturaja (Terminal Batu Kuning)',
        ],
        'Jakarta' => [
            'lat' => -6.208763,
            'lng' => 106.845599,
            'name' => 'Jakarta',
            'terminal' => 'Titik SIPP Jakarta (Terminal Kampung Rambutan)',
        ],
        'Bandung' => [
            'lat' => -6.917464,
            'lng' => 107.619123,
            'name' => 'Bandung',
            'terminal' => 'Titik SIPP Bandung (Terminal Leuwipanjang)',
        ],
    ];

    /**
     * Get coordinates for a given city name
     *
     * @return array{lat: float, lng: float, name: string, terminal: string}
     */
    public static function getCityCoordinates(string $cityName): array
    {
        foreach (self::$cityCoordinates as $key => $data) {
            if (stripos($cityName, $key) !== false || stripos($key, $cityName) !== false) {
                return $data;
            }
        }

        return [
            'lat' => -2.976073,
            'lng' => 104.775431,
            'name' => $cityName,
            'terminal' => 'Titik SIPP '.$cityName,
        ];
    }

    /**
     * Generate route waypoints polyline between Origin and Destination
     *
     * @return array<int, array{0: float, 1: float}>
     */
    public static function generateRouteWaypoints(string $origin, string $destination): array
    {
        $start = self::getCityCoordinates($origin);
        $end = self::getCityCoordinates($destination);

        $intermediate = [];
        $pair = strtolower($origin.'-'.$destination);

        if (str_contains($pair, 'palembang') && str_contains($pair, 'jambi')) {
            $intermediate = [
                [-2.8012, 104.5821], // Betung / Pangkalan Balai
                [-2.4184, 104.1832], // Sungai Lilin
                [-2.0531, 103.9512], // Bayung Lencir
                [-1.8105, 103.7420], // Tempino
            ];
        } elseif (str_contains($pair, 'palembang') && str_contains($pair, 'lampung')) {
            $intermediate = [
                [-3.3762, 104.8512], // Tol Kayu Agung
                [-4.0152, 105.1205], // Pematang Panggang
                [-4.6215, 105.2104], // Menggala / Tulang Bawang
                [-5.0512, 105.2814], // Gunung Sugih / Terbanggi Besar
            ];
        } elseif (str_contains($pair, 'palembang') && str_contains($pair, 'prabumulih')) {
            $intermediate = [
                [-3.1612, 104.5512], // Indralaya Tol
                [-3.3102, 104.3812], // Gelumbang
            ];
        } elseif (str_contains($pair, 'palembang') && str_contains($pair, 'lahat')) {
            $intermediate = [
                [-3.1612, 104.5512], // Indralaya
                [-3.4326, 104.2361], // Prabumulih
                [-3.6512, 103.8821], // Muara Enim
            ];
        } elseif (str_contains($pair, 'palembang') && str_contains($pair, 'lubuklinggau')) {
            $intermediate = [
                [-3.1612, 104.5512], // Indralaya
                [-3.4326, 104.2361], // Prabumulih
                [-3.6512, 103.8821], // Muara Enim
                [-3.7885, 103.5413], // Lahat
                [-3.5215, 103.1812], // Tebing Tinggi Empat Lawang
            ];
        } elseif (str_contains($pair, 'palembang') && str_contains($pair, 'bengkulu')) {
            $intermediate = [
                [-3.1612, 104.5512], // Indralaya
                [-3.4326, 104.2361], // Prabumulih
                [-3.6512, 103.8821], // Muara Enim
                [-3.7885, 103.5413], // Lahat
                [-3.6102, 102.5812], // Kepahiang
            ];
        } elseif (str_contains($pair, 'palembang') && str_contains($pair, 'baturaja')) {
            $intermediate = [
                [-3.1612, 104.5512], // Indralaya
                [-3.4326, 104.2361], // Prabumulih
                [-3.8214, 104.1952], // Peninjauan
            ];
        }

        if (strtolower($start['name']) !== 'palembang' && strtolower($end['name']) === 'palembang') {
            $intermediate = array_reverse($intermediate);
        }

        $waypoints = [
            [$start['lat'], $start['lng']],
        ];

        foreach ($intermediate as $pt) {
            $waypoints[] = $pt;
        }

        $waypoints[] = [$end['lat'], $end['lng']];

        return $waypoints;
    }

    /**
     * Get live telemetry and map tracking details for a Schedule
     *
     * @return array<string, mixed>
     */
    public static function getLiveTrackingData(Schedule $schedule): array
    {
        $route = $schedule->route;
        $originCity = $route?->origin ?? 'Palembang';
        $destCity = $route?->destination ?? 'Jambi';

        $origin = self::getCityCoordinates($originCity);
        $destination = self::getCityCoordinates($destCity);
        $waypoints = self::generateRouteWaypoints($originCity, $destCity);

        $status = $schedule->status;
        $driver = $schedule->driver;
        $vehicle = $schedule->vehicle;

        $lat = null;
        $lng = null;
        $speed = 0;
        $progress = 0.0;
        $statusText = 'Menunggu di Terminal';

        if ($schedule->current_latitude && $schedule->current_longitude && $schedule->last_location_update && $schedule->last_location_update->gt(now()->subMinutes(15))) {
            $lat = (float) $schedule->current_latitude;
            $lng = (float) $schedule->current_longitude;
            $speed = (int) ($schedule->current_speed ?? 60);
            $progress = 50.0;
            $statusText = $status === 'IN_TRANSIT' ? 'Sedang Melaju (GPS Driver)' : 'Armada Standby';
        } else {
            switch ($status) {
                case 'WAITING':
                    $lat = $origin['lat'];
                    $lng = $origin['lng'];
                    $speed = 0;
                    $progress = 0.0;
                    $statusText = 'Armada Bersiap di Titik Kumpul / Terminal';
                    break;

                case 'BOARDING':
                    $lat = $origin['lat'];
                    $lng = $origin['lng'];
                    $speed = 0;
                    $progress = 5.0;
                    $statusText = 'Proses Boarding & Naik Penumpang';
                    break;

                case 'IN_TRANSIT':
                    $depTime = $schedule->departure_time ?? now()->subMinutes(30);
                    $arrTime = $schedule->arrival_time ?? $depTime->copy()->addMinutes($route?->duration_minutes ?? 180);
                    $totalMinutes = max(1, $depTime->diffInMinutes($arrTime));
                    $elapsedMinutes = $depTime->diffInMinutes(now(), false);

                    if ($elapsedMinutes <= 0) {
                        $ratio = 0.15;
                    } elseif ($elapsedMinutes >= $totalMinutes) {
                        $ratio = 0.90;
                    } else {
                        $ratio = min(0.95, max(0.08, $elapsedMinutes / $totalMinutes));
                    }

                    $interpolated = self::interpolatePositionAlongWaypoints($waypoints, $ratio);
                    $lat = $interpolated[0];
                    $lng = $interpolated[1];
                    $speed = 68;
                    $progress = round($ratio * 100, 1);
                    $statusText = 'Dalam Perjalanan Lintas (Kecepatan '.$speed.' km/jam)';
                    break;

                case 'ARRIVED':
                    $lat = $destination['lat'];
                    $lng = $destination['lng'];
                    $speed = 0;
                    $progress = 100.0;
                    $statusText = 'Armada Telah Tiba di Kota Tujuan';
                    break;

                case 'COMPLETED':
                    $lat = $destination['lat'];
                    $lng = $destination['lng'];
                    $speed = 0;
                    $progress = 100.0;
                    $statusText = 'Perjalanan Telah Selesai';
                    break;

                default:
                    $lat = $origin['lat'];
                    $lng = $origin['lng'];
                    $speed = 0;
                    $progress = 0.0;
                    $statusText = 'Status: '.$status;
                    break;
            }
        }

        return [
            'schedule_id' => $schedule->id,
            'status' => $status,
            'status_text' => $statusText,
            'progress_percent' => $progress,
            'current_speed' => $speed,
            'current_location' => [
                'lat' => $lat,
                'lng' => $lng,
            ],
            'origin' => $origin,
            'destination' => $destination,
            'waypoints' => $waypoints,
            'vehicle' => [
                'name' => $vehicle?->name ?? 'Armada SIPP',
                'plate' => $vehicle?->license_plate ?? 'BG 0000 XX',
                'facilities' => $vehicle?->facilities ?? 'AC, Reclining Seats',
            ],
            'driver' => [
                'name' => $driver?->user?->name ?? 'Driver SIPP',
                'phone' => $driver?->user?->phone ?? null,
                'license' => $driver?->license_number ?? 'SIM-A',
            ],
            'updated_at' => now()->format('H:i:s'),
        ];
    }

    /**
     * Interpolate a position along a series of waypoints given a 0.0 to 1.0 ratio
     *
     * @param  array<int, array{0: float, 1: float}>  $waypoints
     * @return array{0: float, 1: float}
     */
    protected static function interpolatePositionAlongWaypoints(array $waypoints, float $ratio): array
    {
        $count = count($waypoints);
        if ($count === 0) {
            return [-2.976073, 104.775431];
        }
        if ($count === 1) {
            return $waypoints[0];
        }

        $totalSegments = $count - 1;
        $scaled = $ratio * $totalSegments;
        $segmentIndex = (int) floor($scaled);
        $segmentIndex = min($segmentIndex, $totalSegments - 1);
        $subRatio = $scaled - $segmentIndex;

        $p1 = $waypoints[$segmentIndex];
        $p2 = $waypoints[$segmentIndex + 1];

        $lat = $p1[0] + ($p2[0] - $p1[0]) * $subRatio;
        $lng = $p1[1] + ($p2[1] - $p1[1]) * $subRatio;

        return [$lat, $lng];
    }
}
