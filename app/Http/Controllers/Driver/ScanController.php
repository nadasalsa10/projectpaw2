<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function index()
    {
        return view('driver.scan');
    }

    public function validateQr(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = trim($validated['code']);
        $driver = Auth::user()->driver;

        // Search by QR Code Hash or Ticket Code
        $ticket = Ticket::where('qr_code_hash', $code)
            ->orWhere('ticket_code', $code)
            ->with(['passenger', 'schedule.route', 'schedule.vehicle', 'bookingSeat.vehicleSeat', 'booking'])
            ->first();

        if (! $ticket) {
            return response()->json([
                'valid' => false,
                'message' => 'Tiket tidak ditemukan / QR Code tidak valid.',
            ], 404);
        }

        // Check if ticket schedule belongs to this driver
        if ($ticket->schedule->driver_id !== $driver->id) {
            return response()->json([
                'valid' => false,
                'message' => 'Tiket ini bukan untuk jadwal perjalanan Anda!',
                'ticket' => [
                    'ticket_code' => $ticket->ticket_code,
                    'passenger' => $ticket->passenger->name,
                    'route' => $ticket->schedule->route->origin.' → '.$ticket->schedule->route->destination,
                    'departure_time' => $ticket->schedule->departure_time->format('d M Y H:i'),
                ],
            ], 403);
        }

        // Check if ticket is already checked in
        if ($ticket->is_checked_in) {
            return response()->json([
                'valid' => true,
                'already_checked_in' => true,
                'message' => 'Penumpang ini sudah melakukan Check-In sebelumnya pada '.$ticket->checked_in_at->format('H:i:s'),
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'passenger_name' => $ticket->passenger->name,
                    'passenger_phone' => $ticket->passenger->phone,
                    'seat_number' => $ticket->bookingSeat->vehicleSeat->seat_number,
                    'route' => $ticket->schedule->route->origin.' → '.$ticket->schedule->route->destination,
                    'checked_in_at' => $ticket->checked_in_at->format('d M Y H:i:s'),
                ],
            ]);
        }

        return response()->json([
            'valid' => true,
            'already_checked_in' => false,
            'message' => 'Tiket Valid! Silakan klik tombol Check-In.',
            'ticket' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'passenger_name' => $ticket->passenger->name,
                'passenger_phone' => $ticket->passenger->phone,
                'seat_number' => $ticket->bookingSeat->vehicleSeat->seat_number,
                'route' => $ticket->schedule->route->origin.' → '.$ticket->schedule->route->destination,
                'departure_time' => $ticket->schedule->departure_time->format('d M Y H:i'),
            ],
        ]);
    }

    public function checkIn(Request $request, Ticket $ticket)
    {
        $driver = Auth::user()->driver;

        if ($ticket->schedule->driver_id !== $driver->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized driver.'], 403);
        }

        if ($ticket->is_checked_in) {
            return response()->json(['success' => false, 'message' => 'Penumpang sudah check-in.'], 400);
        }

        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => Carbon::now(),
        ]);

        // Send notification to customer
        Notification::create([
            'user_id' => $ticket->booking->user_id,
            'title' => 'Check-In Berhasil!',
            'message' => "Tiket {$ticket->ticket_code} (Kursi {$ticket->bookingSeat->vehicleSeat->seat_number}) telah divalidasi oleh Driver.",
            'type' => 'TRIP',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-In penumpang berhasil divalidasi!',
        ]);
    }
}
