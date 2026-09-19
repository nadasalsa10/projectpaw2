<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\BookingTrip;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\VehicleSeat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function selectSeat(Request $request)
    {
        $validated = $request->validate([
            'outbound_schedule_id' => ['required', 'exists:schedules,id'],
            'return_schedule_id' => ['nullable', 'required_if:trip_type,ROUND_TRIP', 'exists:schedules,id'],
            'trip_type' => ['required', 'in:ONE_WAY,ROUND_TRIP'],
            'passengers' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $outboundSchedule = Schedule::with(['route', 'vehicle.seats', 'driver.user'])->findOrFail($validated['outbound_schedule_id']);

        // Fetch taken seats for outbound
        $takenOutboundSeatIds = BookingSeat::whereHas('bookingTrip', function ($q) use ($outboundSchedule) {
            $q->where('schedule_id', $outboundSchedule->id);
        })
            ->whereIn('status', ['RESERVED', 'BOOKED'])
            ->pluck('vehicle_seat_id')
            ->toArray();

        $returnSchedule = null;
        $takenReturnSeatIds = [];
        if ($validated['trip_type'] === 'ROUND_TRIP' && ! empty($validated['return_schedule_id'])) {
            $returnSchedule = Schedule::with(['route', 'vehicle.seats', 'driver.user'])->findOrFail($validated['return_schedule_id']);
            $takenReturnSeatIds = BookingSeat::whereHas('bookingTrip', function ($q) use ($returnSchedule) {
                $q->where('schedule_id', $returnSchedule->id);
            })
                ->whereIn('status', ['RESERVED', 'BOOKED'])
                ->pluck('vehicle_seat_id')
                ->toArray();
        }

        return view('customer.select_seat', compact(
            'outboundSchedule',
            'takenOutboundSeatIds',
            'returnSchedule',
            'takenReturnSeatIds',
            'validated'
        ));
    }

    public function passengerForm(Request $request)
    {
        $validated = $request->validate([
            'outbound_schedule_id' => ['required', 'exists:schedules,id'],
            'return_schedule_id' => ['nullable', 'required_if:trip_type,ROUND_TRIP', 'exists:schedules,id'],
            'trip_type' => ['required', 'in:ONE_WAY,ROUND_TRIP'],
            'passengers' => ['required', 'integer', 'min:1', 'max:6'],
            'outbound_seats' => ['required', 'array'],
            'outbound_seats.*' => ['exists:vehicle_seats,id'],
            'return_seats' => ['nullable', 'required_if:trip_type,ROUND_TRIP', 'array'],
            'return_seats.*' => ['exists:vehicle_seats,id'],
        ]);

        $passengersCount = (int) $validated['passengers'];
        if (count($validated['outbound_seats']) !== $passengersCount) {
            return back()->with('error', "Silakan pilih tepat {$passengersCount} kursi untuk keberangkatan.");
        }

        $isRoundTrip = $validated['trip_type'] === 'ROUND_TRIP' && ! empty($validated['return_schedule_id']);
        if ($isRoundTrip && count($validated['return_seats'] ?? []) !== $passengersCount) {
            return back()->with('error', "Silakan pilih tepat {$passengersCount} kursi untuk kepulangan.");
        }

        $outboundSchedule = Schedule::with(['route', 'vehicle'])->findOrFail($validated['outbound_schedule_id']);
        $outboundSeats = VehicleSeat::whereIn('id', $validated['outbound_seats'])->get();

        $returnSchedule = null;
        $returnSeats = collect();
        if ($isRoundTrip) {
            $returnSchedule = Schedule::with(['route', 'vehicle'])->findOrFail($validated['return_schedule_id']);
            $returnSeats = VehicleSeat::whereIn('id', $validated['return_seats'])->get();
        }

        return view('customer.passenger_form', compact(
            'validated',
            'outboundSchedule',
            'outboundSeats',
            'returnSchedule',
            'returnSeats'
        ));
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'outbound_schedule_id' => ['required', 'exists:schedules,id'],
            'return_schedule_id' => ['nullable', 'exists:schedules,id'],
            'trip_type' => ['required', 'in:ONE_WAY,ROUND_TRIP'],
            'passengers' => ['required', 'array'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.phone' => ['nullable', 'string', 'max:20'],
            'passengers.*.id_number' => ['nullable', 'string', 'max:30'],
            'outbound_seats' => ['required', 'array'],
            'return_seats' => ['nullable', 'array'],
        ]);

        $outboundSchedule = Schedule::with(['route', 'vehicle'])->findOrFail($validated['outbound_schedule_id']);
        $outboundSeats = VehicleSeat::whereIn('id', $validated['outbound_seats'])->get();

        $returnSchedule = null;
        $returnSeats = collect();
        if ($validated['trip_type'] === 'ROUND_TRIP' && ! empty($validated['return_schedule_id'])) {
            $returnSchedule = Schedule::with(['route', 'vehicle'])->findOrFail($validated['return_schedule_id']);
            $returnSeats = VehicleSeat::whereIn('id', $validated['return_seats'])->get();
        }

        $passengerCount = count($validated['passengers']);
        $outboundTotal = $outboundSchedule->price * $passengerCount;
        $returnTotal = $returnSchedule ? ($returnSchedule->price * $passengerCount) : 0;
        $grandTotal = $outboundTotal + $returnTotal;

        return view('customer.booking_summary', compact(
            'validated',
            'outboundSchedule',
            'outboundSeats',
            'returnSchedule',
            'returnSeats',
            'grandTotal'
        ));
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'outbound_schedule_id' => ['required', 'exists:schedules,id'],
            'return_schedule_id' => ['nullable', 'exists:schedules,id'],
            'trip_type' => ['required', 'in:ONE_WAY,ROUND_TRIP'],
            'passengers' => ['required', 'array'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.phone' => ['nullable', 'string', 'max:20'],
            'passengers.*.id_number' => ['nullable', 'string', 'max:30'],
            'outbound_seats' => ['required', 'array'],
            'return_seats' => ['nullable', 'array'],
            'payment_method' => ['required', 'in:BANK_TRANSFER,VIRTUAL_ACCOUNT,QRIS,E_WALLET'],
        ]);

        $outboundSchedule = Schedule::findOrFail($validated['outbound_schedule_id']);
        $returnSchedule = ! empty($validated['return_schedule_id']) ? Schedule::findOrFail($validated['return_schedule_id']) : null;

        $passengerCount = count($validated['passengers']);
        $grandTotal = ($outboundSchedule->price * $passengerCount) + ($returnSchedule ? ($returnSchedule->price * $passengerCount) : 0);

        // Database Concurrency Transaction with Atomic Lock Verification
        try {
            $booking = DB::transaction(function () use ($validated, $outboundSchedule, $returnSchedule, $grandTotal) {
                // 1. Verify outbound seats concurrency
                $conflictOutbound = BookingSeat::whereHas('bookingTrip', function ($q) use ($outboundSchedule) {
                    $q->where('schedule_id', $outboundSchedule->id);
                })
                    ->whereIn('vehicle_seat_id', $validated['outbound_seats'])
                    ->whereIn('status', ['RESERVED', 'BOOKED'])
                    ->lockForUpdate()
                    ->exists();

                if ($conflictOutbound) {
                    throw new \Exception('Maaf, salah satu kursi pergi yang Anda pilih baru saja dipesan oleh orang lain. Silakan pilih kursi lain.');
                }

                // 2. Verify return seats concurrency if round-trip
                if ($returnSchedule && ! empty($validated['return_seats'])) {
                    $conflictReturn = BookingSeat::whereHas('bookingTrip', function ($q) use ($returnSchedule) {
                        $q->where('schedule_id', $returnSchedule->id);
                    })
                        ->whereIn('vehicle_seat_id', $validated['return_seats'])
                        ->whereIn('status', ['RESERVED', 'BOOKED'])
                        ->lockForUpdate()
                        ->exists();

                    if ($conflictReturn) {
                        throw new \Exception('Maaf, salah satu kursi pulang yang Anda pilih baru saja dipesan oleh orang lain. Silakan pilih kursi lain.');
                    }
                }

                // Create Master Booking
                $bookingCode = 'SIPP-'.date('Ymd').'-'.strtoupper(Str::random(5));
                $booking = Booking::create([
                    'booking_code' => $bookingCode,
                    'user_id' => Auth::id(),
                    'trip_type' => $validated['trip_type'],
                    'total_amount' => $grandTotal,
                    'status' => 'PENDING_PAYMENT',
                    'expires_at' => Carbon::now()->addMinutes(15),
                ]);

                // Create Outbound Trip
                $outboundTrip = BookingTrip::create([
                    'booking_id' => $booking->id,
                    'schedule_id' => $outboundSchedule->id,
                    'direction' => 'OUTBOUND',
                ]);

                // Create Return Trip if round-trip
                $returnTrip = null;
                if ($returnSchedule) {
                    $returnTrip = BookingTrip::create([
                        'booking_id' => $booking->id,
                        'schedule_id' => $returnSchedule->id,
                        'direction' => 'RETURN',
                    ]);
                }

                // Create Passengers & Reserve Seats
                foreach ($validated['passengers'] as $index => $passengerData) {
                    $passenger = Passenger::create([
                        'booking_id' => $booking->id,
                        'name' => $passengerData['name'],
                        'phone' => $passengerData['phone'] ?? null,
                        'id_number' => $passengerData['id_number'] ?? null,
                    ]);

                    // Assign outbound seat
                    if (isset($validated['outbound_seats'][$index])) {
                        BookingSeat::create([
                            'booking_trip_id' => $outboundTrip->id,
                            'passenger_id' => $passenger->id,
                            'vehicle_seat_id' => $validated['outbound_seats'][$index],
                            'status' => 'RESERVED',
                        ]);
                    }

                    // Assign return seat
                    if ($returnTrip && isset($validated['return_seats'][$index])) {
                        BookingSeat::create([
                            'booking_trip_id' => $returnTrip->id,
                            'passenger_id' => $passenger->id,
                            'vehicle_seat_id' => $validated['return_seats'][$index],
                            'status' => 'RESERVED',
                        ]);
                    }
                }

                // Create Payment record
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_code' => 'PAY-'.strtoupper(Str::random(10)),
                    'payment_method' => $validated['payment_method'],
                    'amount' => $grandTotal,
                    'payment_status' => 'PENDING',
                ]);

                return $booking;
            });

            return redirect()->route('customer.payment.show', $booking->id)
                ->with('success', 'Pemesanan berhasil dibuat! Silakan selesaikan pembayaran sebelum batas waktu berakhir.');

        } catch (\Exception $e) {
            return redirect()->route('customer.home')->with('error', $e->getMessage());
        }
    }
}
