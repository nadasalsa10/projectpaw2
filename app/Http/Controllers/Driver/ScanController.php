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
        if ($ticket->schedule?->driver_id !== $driver->id) {
            return response()->json([
                'valid' => false,
                'message' => 'Tiket ini bukan untuk jadwal perjalanan Anda!',
                'ticket' => [
                    'ticket_code' => $ticket->ticket_code,
                    'passenger' => $ticket->passenger?->name ?? 'Penumpang',
                    'route' => ($ticket->schedule?->route?->origin ?? '-').' → '.($ticket->schedule?->route?->destination ?? '-'),
                    'departure_time' => $ticket->schedule?->departure_time ? $ticket->schedule->departure_time->format('d M Y H:i') : '-',
                ],
            ], 403);
        }

        $origin = $ticket->schedule?->route?->origin ?? 'Asal';
        $destination = $ticket->schedule?->route?->destination ?? 'Tujuan';
        $seatNum = $ticket->bookingSeat?->vehicleSeat?->seat_number ?? '-';
        $depTime = $ticket->schedule?->departure_time ? $ticket->schedule->departure_time->format('d M Y H:i') : '-';

        // Check if ticket is already checked in
        if ($ticket->is_checked_in) {
            return response()->json([
                'valid' => true,
                'already_checked_in' => true,
                'message' => 'Penumpang ini sudah melakukan Check-In sebelumnya pada '.($ticket->checked_in_at ? $ticket->checked_in_at->format('H:i:s') : '-'),
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'passenger_name' => $ticket->passenger?->name ?? 'Penumpang',
                    'passenger_phone' => $ticket->passenger?->phone ?? '-',
                    'seat_number' => $seatNum,
                    'route' => "{$origin} → {$destination}",
                    'checked_in_at' => $ticket->checked_in_at ? $ticket->checked_in_at->format('d M Y H:i:s') : '-',
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
                'passenger_name' => $ticket->passenger?->name ?? 'Penumpang',
                'passenger_phone' => $ticket->passenger?->phone ?? '-',
                'seat_number' => $seatNum,
                'route' => "{$origin} → {$destination}",
                'departure_time' => $depTime,
            ],
        ]);
    }

    public function checkIn(Request $request, Ticket $ticket)
    {
        $driver = Auth::user()->driver;

        if ($ticket->schedule?->driver_id !== $driver->id) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized driver.'], 403);
            }

            return back()->with('error', 'Anda tidak ditugaskan untuk memvalidasi tiket ini.');
        }

        if ($ticket->is_checked_in) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Penumpang sudah check-in.'], 400);
            }

            return back()->with('error', 'Penumpang ini sudah check-in sebelumnya.');
        }

        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => Carbon::now(),
        ]);

        // Send notification to customer
        if ($ticket->booking) {
            $seatNum = $ticket->bookingSeat?->vehicleSeat?->seat_number ?? '-';
            Notification::create([
                'user_id' => $ticket->booking->user_id,
                'title' => 'Check-In Berhasil!',
                'message' => "Tiket {$ticket->ticket_code} (Kursi {$seatNum}) telah divalidasi oleh Driver.",
                'type' => 'TRIP',
            ]);
        }

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-In penumpang berhasil divalidasi!',
            ]);
        }

        return back()->with('success', 'Check-In penumpang berhasil divalidasi!');
    }
}
