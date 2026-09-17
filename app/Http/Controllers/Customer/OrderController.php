<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['PENDING_PAYMENT', 'PAYMENT_PROCESSING', 'PAID', 'CONFIRMED', 'WAITING_DEPARTURE', 'BOARDING', 'IN_TRANSIT'])
            ->with(['bookingTrips.schedule.route', 'tickets'])
            ->orderBy('created_at', 'desc')
            ->get();

        $historyBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['COMPLETED', 'CANCELLED', 'EXPIRED'])
            ->with(['bookingTrips.schedule.route', 'tickets'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.orders.index', compact('activeBookings', 'historyBookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id() && ! Auth::user()->isDriver() && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        $booking->load([
            'bookingTrips.schedule.route',
            'bookingTrips.schedule.vehicle',
            'bookingTrips.schedule.driver.user',
            'passengers',
            'payment',
            'tickets.bookingSeat.vehicleSeat',
            'tickets.passenger',
        ]);

        return view('customer.orders.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($booking->status, ['COMPLETED', 'CANCELLED', 'IN_TRANSIT', 'BOARDING'])) {
            return back()->with('error', 'Pesanan ini tidak dapat dibatalkan.');
        }

        $booking->update(['status' => 'CANCELLED']);
        $booking->payment()->update(['payment_status' => 'FAILED']);

        BookingSeat::whereHas('bookingTrip', function ($q) use ($booking) {
            $q->where('booking_id', $booking->id);
        })->update(['status' => 'CANCELLED']);

        Notification::create([
            'user_id' => $booking->user_id,
            'title' => 'Pesanan Dibatalkan',
            'message' => "Pesanan {$booking->booking_code} telah berhasil dibatalkan.",
            'type' => 'BOOKING',
        ]);

        return redirect()->route('customer.orders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        Notification::where('user_id', Auth::id())->where('is_read', false)->update(['is_read' => true]);

        return view('customer.notifications', compact('notifications'));
    }
}
