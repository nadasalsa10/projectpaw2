<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;
        if (! $driver) {
            abort(403, 'Akun Anda belum terdaftar sebagai driver.');
        }

        $today = Carbon::today();

        // Fetch schedules assigned to this driver for today and upcoming
        $todaySchedules = Schedule::where('driver_id', $driver->id)
            ->whereDate('departure_time', '>=', $today->toDateString())
            ->with([
                'route',
                'vehicle',
                'tickets.passenger',
                'tickets.bookingSeat.vehicleSeat',
                'tickets.booking.user',
            ])
            ->orderBy('departure_time', 'asc')
            ->get();

        $activeTrip = Schedule::where('driver_id', $driver->id)
            ->whereIn('status', ['BOARDING', 'IN_TRANSIT'])
            ->with(['route', 'vehicle', 'tickets.passenger'])
            ->first();

        // Fetch recent tickets/bookings made by customers for this driver's schedules
        $recentCustomerBookings = Ticket::whereHas('schedule', function ($q) use ($driver) {
            $q->where('driver_id', $driver->id);
        })
            ->with(['passenger', 'bookingSeat.vehicleSeat', 'schedule.route', 'booking.user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentBookingsArray = $recentCustomerBookings->map(function ($tkt) {
            return [
                'id' => $tkt->id,
                'passenger_name' => $tkt->passenger?->name ?? 'Penumpang',
                'seat_number' => $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-',
                'booking_code' => $tkt->booking?->booking_code ?? '-',
                'origin' => $tkt->schedule?->route?->origin ?? '-',
                'destination' => $tkt->schedule?->route?->destination ?? '-',
                'departure_time' => $tkt->schedule?->departure_time ? $tkt->schedule->departure_time->format('d M H:i') : '-',
                'is_checked_in' => (bool) $tkt->is_checked_in,
            ];
        })->values()->toArray();

        return view('driver.dashboard', compact('driver', 'todaySchedules', 'activeTrip', 'recentCustomerBookings', 'recentBookingsArray'));
    }

    public function liveBookings()
    {
        $driver = Auth::user()->driver;
        if (! $driver) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $recentCustomerBookings = Ticket::whereHas('schedule', function ($q) use ($driver) {
            $q->where('driver_id', $driver->id);
        })
            ->with(['passenger', 'bookingSeat.vehicleSeat', 'schedule.route', 'booking.user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($tkt) {
                return [
                    'id' => $tkt->id,
                    'passenger_name' => $tkt->passenger?->name ?? 'Penumpang',
                    'passenger_phone' => $tkt->passenger?->phone ?? '-',
                    'seat_number' => $tkt->bookingSeat?->vehicleSeat?->seat_number ?? '-',
                    'booking_code' => $tkt->booking?->booking_code ?? '-',
                    'origin' => $tkt->schedule?->route?->origin ?? '-',
                    'destination' => $tkt->schedule?->route?->destination ?? '-',
                    'departure_time' => $tkt->schedule?->departure_time ? $tkt->schedule->departure_time->format('d M H:i') : '-',
                    'is_checked_in' => (bool) $tkt->is_checked_in,
                    'created_at_human' => $tkt->created_at?->diffForHumans() ?? '-',
                ];
            });

        $activeSchedulesCount = Schedule::where('driver_id', $driver->id)
            ->whereDate('departure_time', '>=', Carbon::today())
            ->count();

        return response()->json([
            'success' => true,
            'driver_name' => Auth::user()->name,
            'total_bookings' => $recentCustomerBookings->count(),
            'bookings' => $recentCustomerBookings,
            'active_schedules_count' => $activeSchedulesCount,
        ]);
    }

    public function profile()
    {
        $driver = Auth::user()->load('driver');

        return view('driver.profile', compact('driver'));
    }
}
