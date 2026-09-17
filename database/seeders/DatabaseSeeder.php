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
        User::create([
            'name' => 'Admin SIPP',
            'email' => 'admin@example.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Customer User
        $customerUser = User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@example.com',
            'phone' => '088765432109',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        Customer::create([
            'user_id' => $customerUser->id,
            'address' => 'Jl. Merdeka No. 45, Palembang',
            'emergency_phone' => '081299998888',
        ]);

        // 3. Drivers
        $driversData = [
            ['name' => 'Driver Demo', 'email' => 'driver@example.com', 'phone' => '081377776666', 'sim' => 'SIM-B1-99887766'],
            ['name' => 'Driver Agus Subagyo', 'email' => 'driver.agus@example.com', 'phone' => '081388885555', 'sim' => 'SIM-B1-11223344'],
            ['name' => 'Driver Bambang Heru', 'email' => 'driver.bambang@example.com', 'phone' => '081399994444', 'sim' => 'SIM-B1-55667788'],
        ];

        $createdDrivers = [];
        foreach ($driversData as $dData) {
            $u = User::create([
                'name' => $dData['name'],
                'email' => $dData['email'],
                'phone' => $dData['phone'],
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]);

            $createdDrivers[] = Driver::create([
                'user_id' => $u->id,
                'license_number' => $dData['sim'],
                'status' => 'ACTIVE',
            ]);
        }

        // 4. Vehicles & Seat Layouts
        $vehiclesData = [
            ['name' => 'Toyota Hiace Executive', 'plate' => 'BG 1234 XX', 'capacity' => 12, 'facilities' => 'AC, Reclining Seats, USB Charger, Mineral Water, Full Audio'],
            ['name' => 'Isuzu Elf Long Luxury', 'plate' => 'BG 7777 AB', 'capacity' => 14, 'facilities' => 'AC, TV LED, Reclining Seats, Power Socket, Selimut'],
            ['name' => 'Toyota Hiace Premio VIP', 'plate' => 'BG 9999 CD', 'capacity' => 10, 'facilities' => 'Captain Seats, AC Dual Zone, Free WiFi, Mineral Water, Entertainment'],
        ];

        $createdVehicles = [];
        $seatLabels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2', 'E1', 'E2', 'F1', 'F2', 'G1', 'G2'];

        foreach ($vehiclesData as $vData) {
            $veh = Vehicle::create([
                'name' => $vData['name'],
                'license_plate' => $vData['plate'],
                'capacity' => $vData['capacity'],
                'facilities' => $vData['facilities'],
                'status' => 'ACTIVE',
            ]);
            $createdVehicles[] = $veh;

            for ($i = 0; $i < $vData['capacity']; $i++) {
                $label = $seatLabels[$i] ?? ('R'.($i + 1));
                VehicleSeat::create([
                    'vehicle_id' => $veh->id,
                    'seat_number' => $label,
                    'row_index' => (int) floor($i / 2),
                    'column_index' => $i % 2,
                    'is_active' => true,
                ]);
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
            $createdRoutes[] = Route::create([
                'origin' => $rData['origin'],
                'destination' => $rData['destination'],
                'duration_minutes' => $rData['duration'],
                'base_price' => $rData['price'],
                'is_active' => true,
            ]);
        }

        // 6. Generate Multiple Varied Schedules per Day (Pagi, Siang, Sore, Malam)
        $today = Carbon::today();

        // Time slots: Pagi 07:00, Pagi 09:30, Siang 13:00, Sore 16:30, Malam 20:00, Malam 21:30
        $timeSlots = [
            ['hour' => 7, 'minute' => 0],
            ['hour' => 9, 'minute' => 30],
            ['hour' => 13, 'minute' => 0],
            ['hour' => 16, 'minute' => 30],
            ['hour' => 20, 'minute' => 0],
            ['hour' => 21, 'minute' => 30],
        ];

        foreach ($createdRoutes as $routeIndex => $route) {
            // Assign 3 different departure time slots per route per day for maximum choice
            $routeSlots = [
                $timeSlots[$routeIndex % count($timeSlots)],
                $timeSlots[($routeIndex + 2) % count($timeSlots)],
                $timeSlots[($routeIndex + 4) % count($timeSlots)],
            ];

            foreach ([0, 1, 2] as $dayOffset) {
                $scheduleDate = $today->copy()->addDays($dayOffset);

                foreach ($routeSlots as $slotIdx => $slot) {
                    $driverObj = $createdDrivers[($routeIndex + $slotIdx) % count($createdDrivers)];
                    $vehicleObj = $createdVehicles[($routeIndex + $slotIdx) % count($createdVehicles)];

                    $depTime = $scheduleDate->copy()->setHour($slot['hour'])->setMinute($slot['minute']);
                    $arrTime = $depTime->copy()->addMinutes($route->duration_minutes);

                    Schedule::create([
                        'route_id' => $route->id,
                        'vehicle_id' => $vehicleObj->id,
                        'driver_id' => $driverObj->id,
                        'departure_time' => $depTime,
                        'arrival_time' => $arrTime,
                        'price' => $route->base_price,
                        'status' => 'WAITING',
                    ]);
                }
            }
        }
    }
}
