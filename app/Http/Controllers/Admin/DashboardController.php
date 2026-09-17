<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Vehicle;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalBookings = Booking::count();
        $todayBookings = Booking::whereDate('created_at', $today->toDateString())->count();
        $totalRevenue = Payment::where('payment_status', 'PAID')->sum('amount');
        $activeSchedules = Schedule::where('departure_time', '>=', Carbon::now())->where('status', 'WAITING')->count();

        $totalCustomers = Customer::count();
        $totalDrivers = Driver::count();
        $activeVehicles = Vehicle::where('status', 'ACTIVE')->count();

        $recentBookings = Booking::with(['user', 'bookingTrips.schedule.route', 'payment'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalBookings',
            'todayBookings',
            'totalRevenue',
            'activeSchedules',
            'totalCustomers',
            'totalDrivers',
            'activeVehicles',
            'recentBookings'
        ));
    }
}
