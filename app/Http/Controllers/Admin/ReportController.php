<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Schedule;

class ReportController extends Controller
{
    public function index()
    {
        $totalPaidRevenue = Payment::where('payment_status', 'PAID')->sum('amount');
        $totalBookingsCompleted = Booking::where('status', 'COMPLETED')->count();
        $totalSchedulesCompleted = Schedule::where('status', 'COMPLETED')->count();

        $recentPayments = Payment::where('payment_status', 'PAID')
            ->with(['booking.user'])
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'totalPaidRevenue',
            'totalBookingsCompleted',
            'totalSchedulesCompleted',
            'recentPayments'
        ));
    }
}
