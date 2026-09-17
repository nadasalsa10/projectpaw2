<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Route;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->isDriver()) {
                return redirect()->route('driver.dashboard');
            }
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
        }

        $origins = Route::where('is_active', true)->distinct()->pluck('origin');
        $destinations = Route::where('is_active', true)->distinct()->pluck('destination');

        $activeBooking = null;
        if (Auth::check()) {
            $activeBooking = Booking::where('user_id', Auth::id())
                ->whereIn('status', ['PAID', 'CONFIRMED', 'WAITING_DEPARTURE', 'BOARDING', 'IN_TRANSIT'])
                ->with(['bookingTrips.schedule.route', 'bookingTrips.schedule.driver.user', 'bookingTrips.schedule.vehicle'])
                ->latest()
                ->first();
        }

        $upcomingSchedules = Schedule::where('departure_time', '>=', Carbon::now())
            ->where('status', 'WAITING')
            ->with(['route', 'vehicle', 'driver.user'])
            ->orderBy('departure_time', 'asc')
            ->take(4)
            ->get();

        return view('customer.home', compact('origins', 'destinations', 'activeBooking', 'upcomingSchedules'));
    }

    public function help()
    {
        return view('customer.help');
    }
}
