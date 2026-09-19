<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['route', 'vehicle', 'driver.user'])
            ->withCount(['waitingLists' => function ($q) {
                $q->where('status', 'WAITING');
            }])
            ->orderBy('departure_time', 'desc')
            ->get();

        $routes = Route::where('is_active', true)->get();
        $vehicles = Vehicle::where('status', 'ACTIVE')->get();
        $drivers = Driver::where('status', 'ACTIVE')->with('user')->get();

        return view('admin.schedules.index', compact('schedules', 'routes', 'vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => ['required', 'exists:routes,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'departure_time' => ['required', 'date', 'after:now'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_extra' => ['nullable', 'boolean'],
        ]);

        $route = Route::findOrFail($validated['route_id']);
        $departure = Carbon::parse($validated['departure_time']);
        $arrival = $departure->copy()->addMinutes($route->duration_minutes);

        Schedule::create([
            'route_id' => $validated['route_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => $validated['driver_id'],
            'departure_time' => $departure,
            'arrival_time' => $arrival,
            'price' => $validated['price'],
            'status' => 'WAITING',
            'is_extra' => $request->boolean('is_extra'),
        ]);

        return back()->with('success', 'Jadwal perjalanan baru berhasil diterbitkan.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'route_id' => ['required', 'exists:routes,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'departure_time' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:WAITING,BOARDING,IN_TRANSIT,ARRIVED,COMPLETED,CANCELLED'],
            'is_extra' => ['nullable', 'boolean'],
        ]);

        $route = Route::findOrFail($validated['route_id']);
        $departure = Carbon::parse($validated['departure_time']);
        $arrival = $departure->copy()->addMinutes($route->duration_minutes);

        $schedule->update([
            'route_id' => $validated['route_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => $validated['driver_id'],
            'departure_time' => $departure,
            'arrival_time' => $arrival,
            'price' => $validated['price'],
            'status' => $validated['status'],
            'is_extra' => $request->boolean('is_extra'),
        ]);

        return back()->with('success', 'Jadwal perjalanan berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal perjalanan berhasil dihapus.');
    }
}
