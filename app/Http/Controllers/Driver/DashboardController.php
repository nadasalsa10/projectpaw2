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
            ->take(5)
            ->get();

        return view('driver.dashboard', compact('driver', 'todaySchedules', 'activeTrip', 'recentCustomerBookings'));
    }

    public function profile()
    {
        $driver = Auth::user()->load('driver');

        return view('driver.profile', compact('driver'));
    }
}
