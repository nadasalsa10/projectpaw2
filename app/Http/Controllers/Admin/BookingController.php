<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\WaitingList;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'bookingTrips.schedule.route', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'bookingTrips.schedule.route',
            'bookingTrips.schedule.vehicle',
            'bookingTrips.schedule.driver.user',
            'passengers',
            'payment',
            'tickets.bookingSeat.vehicleSeat',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:PENDING_PAYMENT,PAYMENT_PROCESSING,PAID,CONFIRMED,WAITING_DEPARTURE,BOARDING,IN_TRANSIT,COMPLETED,CANCELLED,EXPIRED'],
        ]);

        $booking->update(['status' => $validated['status']]);

        if (in_array($validated['status'], ['CANCELLED', 'EXPIRED'])) {
            foreach ($booking->bookingTrips as $bt) {
                if ($bt->schedule) {
                    WaitingList::notifyWaitingUsers($bt->schedule);
                }
            }
        }

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }
}
