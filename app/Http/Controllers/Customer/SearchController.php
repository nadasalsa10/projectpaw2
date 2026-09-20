<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BookingSeat;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'origin' => ['required', 'string'],
            'destination' => ['required', 'string', 'different:origin'],
            'trip_type' => ['required', 'in:ONE_WAY,ROUND_TRIP'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'required_if:trip_type,ROUND_TRIP', 'date', 'after_or_equal:departure_date'],
            'passengers' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $origin = $validated['origin'];
        $destination = $validated['destination'];
        $tripType = $validated['trip_type'];
        $departureDate = Carbon::parse($validated['departure_date']);
        $passengersCount = (int) $validated['passengers'];

        $outboundRoute = Route::where('origin', $origin)->where('destination', $destination)->first();
        if ($outboundRoute) {
            $this->ensureSchedulesExist($outboundRoute, $departureDate);
        }

        Schedule::rebalanceDriverAssignments();

        // Search Outbound Schedules
        $outboundSchedules = Schedule::whereHas('route', function ($q) use ($origin, $destination) {
            $q->where('origin', $origin)->where('destination', $destination);
        })
            ->whereDate('departure_time', $departureDate->toDateString())
            ->where('departure_time', '>=', Carbon::now())
            ->where('status', 'WAITING')
            ->with(['route', 'vehicle', 'driver.user', 'vehicle.seats'])
            ->orderBy('departure_time', 'asc')
            ->get();

        // Calculate available seats per schedule
        $outboundSchedules->each(function ($schedule) {
            $bookedCount = BookingSeat::whereHas('bookingTrip', function ($q) use ($schedule) {
                $q->where('schedule_id', $schedule->id);
            })->whereIn('status', ['RESERVED', 'BOOKED'])->count();

            $schedule->available_seats = max(0, $schedule->vehicle->capacity - $bookedCount);
        });

        // Filter out schedules without enough seats
        $outboundSchedules = $outboundSchedules->filter(fn ($s) => $s->available_seats >= $passengersCount);

        $returnSchedules = collect();
        if ($tripType === 'ROUND_TRIP' && ! empty($validated['return_date'])) {
            $returnDate = Carbon::parse($validated['return_date']);

            $returnRoute = Route::where('origin', $destination)->where('destination', $origin)->first();
            if ($returnRoute) {
                $this->ensureSchedulesExist($returnRoute, $returnDate);
            }

            // Return trip route must be reverse (Destination -> Origin)
            $returnSchedules = Schedule::whereHas('route', function ($q) use ($destination, $origin) {
                $q->where('origin', $destination)->where('destination', $origin);
            })
                ->whereDate('departure_time', $returnDate->toDateString())
                ->where('departure_time', '>=', Carbon::now())
                ->where('status', 'WAITING')
                ->with(['route', 'vehicle', 'driver.user', 'vehicle.seats'])
                ->orderBy('departure_time', 'asc')
                ->get();

            $returnSchedules->each(function ($schedule) {
                $bookedCount = BookingSeat::whereHas('bookingTrip', function ($q) use ($schedule) {
                    $q->where('schedule_id', $schedule->id);
                })->whereIn('status', ['RESERVED', 'BOOKED'])->count();

                $schedule->available_seats = max(0, $schedule->vehicle->capacity - $bookedCount);
            });

            $returnSchedules = $returnSchedules->filter(fn ($s) => $s->available_seats >= $passengersCount);
        }

        return view('customer.search_result', [
            'searchParams' => $validated,
            'outboundSchedules' => $outboundSchedules,
            'returnSchedules' => $returnSchedules,
        ]);
    }

    private function ensureSchedulesExist(Route $route, Carbon $date): void
    {
        $drivers = Driver::where('status', 'ACTIVE')->get();
        $vehicles = Vehicle::where('status', 'ACTIVE')->get();

        if ($drivers->isEmpty() || $vehicles->isEmpty()) {
            return;
        }

        // Varied departure time templates per route to prevent duplicate / uniform departure times
        $timeTemplates = [
            // Morning, Afternoon, Evening, Night schedules
            ['hour' => 6, 'minute' => 30],
            ['hour' => 9, 'minute' => 0],
            ['hour' => 13, 'minute' => 30],
            ['hour' => 16, 'minute' => 0],
            ['hour' => 19, 'minute' => 30],
            ['hour' => 21, 'minute' => 45],
        ];

        // Shift slots based on route ID so each route has distinct departure times
        $routeOffset = $route->id % 3;

        foreach ($timeTemplates as $idx => $slot) {
            // Apply slight variance per route
            $minuteOffset = ($route->id * 5) % 30;
            $minute = ($slot['minute'] + $minuteOffset) % 60;
            $hour = $slot['hour'] + (int) floor(($slot['minute'] + $minuteOffset) / 60);

            $depTime = $date->copy()->setHour($hour)->setMinute($minute)->setSecond(0);
            if ($depTime->isPast()) {
                continue;
            }

            $arrTime = $depTime->copy()->addMinutes($route->duration_minutes);
            $driverObj = $drivers[($idx + $routeOffset) % $drivers->count()];
            $vehicleObj = $vehicles[($idx + $routeOffset) % $vehicles->count()];

            // Use firstOrCreate to strictly prevent duplicate schedules
            Schedule::firstOrCreate(
                [
                    'route_id' => $route->id,
                    'departure_time' => $depTime,
                ],
                [
                    'vehicle_id' => $vehicleObj->id,
                    'driver_id' => $driverObj->id,
                    'arrival_time' => $arrTime,
                    'price' => $route->base_price,
                    'status' => 'WAITING',
                ]
            );
        }
    }
}
