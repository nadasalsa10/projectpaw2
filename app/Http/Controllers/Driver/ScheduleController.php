<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Ticket;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;

        $schedules = Schedule::where('driver_id', $driver->id)
            ->with(['route', 'vehicle', 'tickets'])
            ->orderBy('departure_time', 'desc')
            ->get();

        return view('driver.schedules.index', compact('schedules'));
    }

    public function show(Schedule $schedule)
    {
        $driver = Auth::user()->driver;

        if ($schedule->driver_id !== $driver->id) {
            abort(403, 'Anda tidak ditugaskan untuk perjalanan ini.');
        }

        $schedule->load([
            'route',
            'vehicle.seats',
            'tickets.passenger',
            'tickets.bookingSeat.vehicleSeat',
            'tickets.booking',
        ]);

        $manifestArray = $schedule->tickets->map(function ($t) {
            return [
                'id' => $t->id,
                'passenger_name' => $t->passenger?->name ?? 'Penumpang',
                'passenger_phone' => $t->passenger?->phone ?? '-',
                'seat_number' => $t->bookingSeat?->vehicleSeat?->seat_number ?? '-',
                'booking_code' => $t->booking?->booking_code ?? '-',
                'is_checked_in' => (bool) $t->is_checked_in,
            ];
        })->values()->toArray();

        $initialTracking = GeoLocationService::getLiveTrackingData($schedule);

        return view('driver.schedules.show', compact('schedule', 'manifestArray', 'initialTracking'));
    }

    public function liveManifest(Schedule $schedule)
    {
        $driver = Auth::user()->driver;

        if (! $driver || $schedule->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $schedule->load([
            'tickets.passenger',
            'tickets.bookingSeat.vehicleSeat',
            'tickets.booking',
        ]);

        $trackingData = GeoLocationService::getLiveTrackingData($schedule);

        return response()->json([
            'success' => true,
            'status' => $schedule->status,
            'total_passengers' => $schedule->tickets->count(),
            'checked_in_count' => $schedule->tickets->where('is_checked_in', true)->count(),
            'tracking' => $trackingData,
            'tickets' => $schedule->tickets->map(function ($t) {
                return [
                    'id' => $t->id,
                    'passenger_name' => $t->passenger?->name ?? 'Penumpang',
                    'passenger_phone' => $t->passenger?->phone ?? '-',
                    'seat_number' => $t->bookingSeat?->vehicleSeat?->seat_number ?? '-',
                    'booking_code' => $t->booking?->booking_code ?? '-',
                    'is_checked_in' => (bool) $t->is_checked_in,
                ];
            }),
        ]);
    }

    public function reports(Request $request)
    {
        $driver = Auth::user()->driver;

        $query = Ticket::whereHas('schedule', function ($q) use ($driver) {
            $q->where('driver_id', $driver->id);
        })->with([
            'schedule.route',
            'schedule.vehicle',
            'passenger',
            'bookingSeat.vehicleSeat',
            'booking.user',
            'booking.payment',
        ]);

        if ($request->filled('status')) {
            if ($request->status === 'checked_in') {
                $query->where('is_checked_in', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_checked_in', false);
            } elseif ($request->status === 'trip_active') {
                $query->whereHas('schedule', function ($q) {
                    $q->whereIn('status', ['WAITING', 'BOARDING', 'IN_TRANSIT', 'ARRIVED']);
                });
            } elseif ($request->status === 'trip_completed') {
                $query->whereHas('schedule', function ($q) {
                    $q->where('status', 'COMPLETED');
                });
            }
        }

        if ($request->filled('date')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->whereDate('departure_time', $request->date);
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary metrics
        $allTickets = Ticket::whereHas('schedule', fn ($q) => $q->where('driver_id', $driver->id))->with('schedule')->get();
        $totalTicketsCount = $allTickets->count();
        $checkedInCount = $allTickets->where('is_checked_in', true)->count();
        $pendingCount = $allTickets->where('is_checked_in', false)->count();
        $tripActiveCount = $allTickets->filter(fn ($t) => in_array($t->schedule?->status, ['WAITING', 'BOARDING', 'IN_TRANSIT', 'ARRIVED']))->count();
        $tripCompletedCount = $allTickets->filter(fn ($t) => $t->schedule?->status === 'COMPLETED')->count();

        $schedulesCount = Schedule::where('driver_id', $driver->id)->count();
        $activeSchedulesCount = Schedule::where('driver_id', $driver->id)->whereIn('status', ['WAITING', 'BOARDING', 'IN_TRANSIT', 'ARRIVED'])->count();
        $completedSchedulesCount = Schedule::where('driver_id', $driver->id)->where('status', 'COMPLETED')->count();

        return view('driver.reports', compact(
            'tickets',
            'totalTicketsCount',
            'checkedInCount',
            'pendingCount',
            'tripActiveCount',
            'tripCompletedCount',
            'schedulesCount',
            'activeSchedulesCount',
            'completedSchedulesCount'
        ));
    }
}
