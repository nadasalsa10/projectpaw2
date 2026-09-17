<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Ticket;
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

        return view('driver.schedules.show', compact('schedule'));
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
            }
        }

        if ($request->filled('date')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->whereDate('departure_time', $request->date);
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary metrics
        $allTickets = Ticket::whereHas('schedule', fn ($q) => $q->where('driver_id', $driver->id))->get();
        $totalTicketsCount = $allTickets->count();
        $checkedInCount = $allTickets->where('is_checked_in', true)->count();
        $pendingCount = $allTickets->where('is_checked_in', false)->count();

        $schedulesCount = Schedule::where('driver_id', $driver->id)->count();
        $completedSchedulesCount = Schedule::where('driver_id', $driver->id)->where('status', 'COMPLETED')->count();

        return view('driver.reports', compact(
            'tickets',
            'totalTicketsCount',
            'checkedInCount',
            'pendingCount',
            'schedulesCount',
            'completedSchedulesCount'
        ));
    }
}
