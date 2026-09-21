<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Notification;
use App\Models\WaitingList;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $currentStep = match ($booking->status) {
            'WAITING', 'WAITING_DEPARTURE', 'PAID', 'CONFIRMED', 'PENDING_PAYMENT', 'PAYMENT_PROCESSING' => 1,
            'BOARDING' => 2,
            'IN_TRANSIT' => 3,
            'ARRIVED', 'COMPLETED' => 4,
            default => 1,
        };

        $primarySchedule = $booking->bookingTrips->first()?->schedule;
        $initialTracking = $primarySchedule ? GeoLocationService::getLiveTrackingData($primarySchedule) : null;

        return view('customer.orders.show', compact('booking', 'currentStep', 'initialTracking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($booking->status, ['COMPLETED', 'CANCELLED', 'IN_TRANSIT', 'BOARDING'])) {
            return back()->with('error', 'Pesanan ini tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'CANCELLED']);
            $booking->payments()->update(['payment_status' => 'FAILED']);

            BookingSeat::whereHas('bookingTrip', function ($q) use ($booking) {
                $q->where('booking_id', $booking->id);
            })->update(['status' => 'CANCELLED']);

            Notification::create([
                'user_id' => $booking->user_id,
                'title' => 'Pesanan Dibatalkan',
                'message' => "Pesanan {$booking->booking_code} telah berhasil dibatalkan.",
                'type' => 'BOOKING',
            ]);

            // Notify waiting list users for each trip in this booking
            foreach ($booking->bookingTrips as $bt) {
                if ($bt->schedule) {
                    WaitingList::notifyWaitingUsers($bt->schedule);
                }
            }
        });

        return redirect()->route('customer.orders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function liveStatus(Booking $booking)
    {
        if ($booking->user_id !== Auth::id() && ! Auth::user()->isDriver() && ! Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking->load(['bookingTrips.schedule.driver.user', 'tickets.passenger', 'tickets.bookingSeat.vehicleSeat']);

        $currentStep = match ($booking->status) {
            'WAITING', 'WAITING_DEPARTURE', 'PAID', 'CONFIRMED' => 1,
            'BOARDING' => 2,
            'IN_TRANSIT' => 3,
            'ARRIVED', 'COMPLETED' => 4,
            default => 1,
        };

        $primarySchedule = $booking->bookingTrips->first()?->schedule;
        $trackingData = $primarySchedule ? GeoLocationService::getLiveTrackingData($primarySchedule) : null;

        return response()->json([
            'status' => $booking->status,
            'step' => $currentStep,
            'driver_name' => $booking->bookingTrips->first()?->schedule?->driver?->user?->name ?? 'TBA',
            'driver_phone' => $booking->bookingTrips->first()?->schedule?->driver?->user?->phone ?? null,
            'tracking' => $trackingData,
            'tickets' => $booking->tickets->map(fn ($t) => [
                'id' => $t->id,
                'passenger_name' => $t->passenger?->name ?? 'Penumpang',
                'seat_number' => $t->bookingSeat?->vehicleSeat?->seat_number ?? '-',
                'is_checked_in' => (bool) $t->is_checked_in,
            ]),
        ]);
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
