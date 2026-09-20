<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSeat;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin SIPP',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Official Customer User (M. Fadhil Anhar)
        $customerUser = User::firstOrCreate(
            ['email' => 'mfadhilanhar@gmail.com'],
            [
                'name' => 'M. Fadhil Anhar',
                'phone' => '0895393859635',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        Customer::firstOrCreate(
            ['user_id' => $customerUser->id],
            [
                'address' => 'Jl. Merdeka No. 45, Palembang',
                'emergency_phone' => '081299998888',
            ]
        );

        // 3. Official Driver (Nada Salsabilah)
        $driverUser = User::firstOrCreate(
            ['email' => 'neocity234@gmail.com'],
            [
                'name' => 'Nada Salsabilah',
                'phone' => '089503215283',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );

        Driver::firstOrCreate(
            ['user_id' => $driverUser->id],
            [
                'license_number' => 'SIM-A1-12345',
                'status' => 'ACTIVE',
            ]
        );

        // 4. Vehicles & Seat Layouts
        $vehiclesData = [
            ['name' => 'Toyota Hiace Executive', 'plate' => 'BG 1234 XX', 'capacity' => 12, 'facilities' => 'AC, Reclining Seats, USB Charger, Mineral Water, Full Audio'],
            ['name' => 'Isuzu Elf Long Luxury', 'plate' => 'BG 7777 AB', 'capacity' => 14, 'facilities' => 'AC, TV LED, Reclining Seats, Power Socket, Selimut'],
            ['name' => 'Toyota Hiace Premio VIP', 'plate' => 'BG 9999 CD', 'capacity' => 10, 'facilities' => 'Captain Seats, AC Dual Zone, Free WiFi, Mineral Water, Entertainment'],
        ];

        $createdVehicles = [];
        $seatLabels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2', 'E1', 'E2', 'F1', 'F2', 'G1', 'G2'];

        foreach ($vehiclesData as $vData) {
            $veh = Vehicle::firstOrCreate(
                ['license_plate' => $vData['plate']],
                [
                    'name' => $vData['name'],
                    'capacity' => $vData['capacity'],
                    'facilities' => $vData['facilities'],
                    'status' => 'ACTIVE',
                ]
            );
            $createdVehicles[] = $veh;

            for ($i = 0; $i < $vData['capacity']; $i++) {
                $label = $seatLabels[$i] ?? ('R'.($i + 1));
                VehicleSeat::firstOrCreate(
                    [
                        'vehicle_id' => $veh->id,
                        'seat_number' => $label,
                    ],
                    [
                        'row_index' => (int) floor($i / 2),
                        'column_index' => $i % 2,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 5. Routes (Palembang <-> Cities in Sumatra)
        $routesData = [
            ['origin' => 'Palembang', 'destination' => 'Jambi', 'duration' => 360, 'price' => 200000],
            ['origin' => 'Jambi', 'destination' => 'Palembang', 'duration' => 360, 'price' => 200000],

            ['origin' => 'Palembang', 'destination' => 'Bandar Lampung', 'duration' => 300, 'price' => 220000],
            ['origin' => 'Bandar Lampung', 'destination' => 'Palembang', 'duration' => 300, 'price' => 220000],

            ['origin' => 'Palembang', 'destination' => 'Bengkulu', 'duration' => 540, 'price' => 250000],
            ['origin' => 'Bengkulu', 'destination' => 'Palembang', 'duration' => 540, 'price' => 250000],

            ['origin' => 'Palembang', 'destination' => 'Padang', 'duration' => 900, 'price' => 350000],
            ['origin' => 'Padang', 'destination' => 'Palembang', 'duration' => 900, 'price' => 350000],

            ['origin' => 'Palembang', 'destination' => 'Pekanbaru', 'duration' => 840, 'price' => 320000],
            ['origin' => 'Pekanbaru', 'destination' => 'Palembang', 'duration' => 840, 'price' => 320000],

            ['origin' => 'Palembang', 'destination' => 'Medan', 'duration' => 1200, 'price' => 450000],
            ['origin' => 'Medan', 'destination' => 'Palembang', 'duration' => 1200, 'price' => 450000],

            ['origin' => 'Palembang', 'destination' => 'Lubuklinggau', 'duration' => 420, 'price' => 180000],
            ['origin' => 'Lubuklinggau', 'destination' => 'Palembang', 'duration' => 420, 'price' => 180000],

            ['origin' => 'Palembang', 'destination' => 'Prabumulih', 'duration' => 120, 'price' => 80000],
            ['origin' => 'Prabumulih', 'destination' => 'Palembang', 'duration' => 120, 'price' => 80000],

            ['origin' => 'Palembang', 'destination' => 'Lahat', 'duration' => 300, 'price' => 150000],
            ['origin' => 'Lahat', 'destination' => 'Palembang', 'duration' => 300, 'price' => 150000],

            ['origin' => 'Palembang', 'destination' => 'Baturaja', 'duration' => 240, 'price' => 130000],
            ['origin' => 'Baturaja', 'destination' => 'Palembang', 'duration' => 240, 'price' => 130000],
        ];

        $createdRoutes = [];
        foreach ($routesData as $rData) {
            $createdRoutes[] = Route::firstOrCreate(
                [
                    'origin' => $rData['origin'],
                    'destination' => $rData['destination'],
                ],
                [
                    'duration_minutes' => $rData['duration'],
                    'base_price' => $rData['price'],
                    'is_active' => true,
                ]
            );
        }

        // 6. Generate Multiple Varied Schedules per Day (Pagi, Siang, Sore, Malam)
        $today = Carbon::today();
        $activeDrivers = Driver::where('status', 'ACTIVE')->get();

        // Varied time templates (Pagi, Siang, Sore, Malam)
        $baseSlots = [
            ['hour' => 6, 'minute' => 30],
            ['hour' => 9, 'minute' => 0],
            ['hour' => 13, 'minute' => 30],
            ['hour' => 16, 'minute' => 15],
            ['hour' => 19, 'minute' => 30],
            ['hour' => 21, 'minute' => 45],
        ];

        foreach ($createdRoutes as $routeIndex => $route) {
            // Select 4 varied departure slots per route
            $selectedSlotIndexes = [
                ($routeIndex) % count($baseSlots),
                ($routeIndex + 2) % count($baseSlots),
                ($routeIndex + 3) % count($baseSlots),
                ($routeIndex + 5) % count($baseSlots),
            ];

            foreach (range(0, 14) as $dayOffset) {
                $scheduleDate = $today->copy()->addDays($dayOffset);

                foreach ($selectedSlotIndexes as $slotIdx => $slotKey) {
                    $slot = $baseSlots[$slotKey];

                    // Slight minute variation per route for natural realism
                    $minuteOffset = ($routeIndex * 10) % 30;
                    $minute = ($slot['minute'] + $minuteOffset) % 60;
                    $hour = $slot['hour'] + (int) floor(($slot['minute'] + $minuteOffset) / 60);

                    // Rotate driver and vehicle across days and slots
                    $driverObj = $activeDrivers->isNotEmpty()
                        ? $activeDrivers[($routeIndex + $slotIdx + $dayOffset) % $activeDrivers->count()]
                        : null;
                    $vehicleObj = $createdVehicles[($routeIndex + $slotIdx + $dayOffset) % count($createdVehicles)];

                    $depTime = $scheduleDate->copy()->setHour($hour)->setMinute($minute)->setSecond(0);
                    $arrTime = $depTime->copy()->addMinutes($route->duration_minutes);

                    Schedule::firstOrCreate(
                        [
                            'route_id' => $route->id,
                            'departure_time' => $depTime,
                        ],
                        [
                            'vehicle_id' => $vehicleObj->id,
                            'driver_id' => $driverObj?->id,
                            'arrival_time' => $arrTime,
                            'price' => $route->base_price,
                            'status' => 'WAITING',
                        ]
                    );
                }
            }
        }
    }
}
