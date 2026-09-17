<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        // Auto expire check
        if ($booking->status === 'PENDING_PAYMENT' && Carbon::now()->greaterThan($booking->expires_at)) {
            DB::transaction(function () use ($booking) {
                $booking->update(['status' => 'EXPIRED']);
                $booking->payment()->update(['payment_status' => 'EXPIRED']);

                BookingSeat::whereHas('bookingTrip', function ($q) use ($booking) {
                    $q->where('booking_id', $booking->id);
                })->update(['status' => 'EXPIRED']);
            });
        }

        $booking->load(['bookingTrips.schedule.route', 'bookingTrips.schedule.vehicle', 'passengers', 'payment']);

        return view('customer.payment', compact('booking'));
    }

    public function processPayment(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        if ($booking->status !== 'PENDING_PAYMENT') {
            return redirect()->route('customer.orders.show', $booking->id)->with('info', 'Status pembayaran pesanan ini sudah tidak pending.');
        }

        // Process Payment Gateway Simulation
        DB::transaction(function () use ($booking) {
            // 1. Update Payment status
            $payment = $booking->payment;
            if ($payment) {
                $payment->update([
                    'payment_status' => 'PAID',
                    'paid_at' => Carbon::now(),
                ]);
            }

            // 2. Update Booking status
            $booking->update(['status' => 'PAID']);

            // 3. Confirm Seats (RESERVED -> BOOKED)
            $bookingTrips = $booking->bookingTrips()->with('bookingSeats')->get();
            foreach ($bookingTrips as $trip) {
                foreach ($trip->bookingSeats as $bSeat) {
                    $bSeat->update(['status' => 'BOOKED']);

                    // 4. Generate E-Ticket & QR Code Hash for each passenger & schedule
                    $ticketCode = 'TKT-'.date('Ymd').'-'.strtoupper(Str::random(5));
                    $hashData = implode('|', [
                        $ticketCode,
                        $booking->booking_code,
                        $bSeat->passenger_id,
                        $trip->schedule_id,
                        config('app.key'),
                    ]);
                    $qrHash = hash('sha256', $hashData);

                    Ticket::create([
                        'ticket_code' => $ticketCode,
                        'booking_id' => $booking->id,
                        'passenger_id' => $bSeat->passenger_id,
                        'schedule_id' => $trip->schedule_id,
                        'booking_seat_id' => $bSeat->id,
                        'qr_code_hash' => $qrHash,
                        'is_checked_in' => false,
                    ]);
                }
            }

            // 5. Send Notification
            Notification::create([
                'user_id' => $booking->user_id,
                'title' => 'Pembayaran Berhasil!',
                'message' => "Pembayaran untuk kode booking {$booking->booking_code} telah dikonfirmasi. E-Ticket Anda siap digunakan.",
                'type' => 'PAYMENT',
            ]);
        });

        return redirect()->route('customer.payment.success', $booking->id);
    }

    public function success(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load(['bookingTrips.schedule.route', 'tickets.passenger', 'tickets.schedule.route', 'tickets.bookingSeat.vehicleSeat']);

        return view('customer.payment_success', compact('booking'));
    }
}
