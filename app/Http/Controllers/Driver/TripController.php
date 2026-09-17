<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function updateStatus(Request $request, Schedule $schedule)
    {
        $driver = Auth::user()->driver;

        if ($schedule->driver_id !== $driver->id) {
            abort(403, 'Anda tidak ditugaskan untuk perjalanan ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:WAITING,BOARDING,IN_TRANSIT,ARRIVED,COMPLETED,CANCELLED'],
        ]);

        $newStatus = $validated['status'];
        $schedule->update(['status' => $newStatus]);

        // Also update corresponding booking statuses if trip starts/completes
        if ($newStatus === 'IN_TRANSIT') {
            foreach ($schedule->bookingTrips as $bt) {
                $bt->booking->update(['status' => 'IN_TRANSIT']);
            }
        } elseif ($newStatus === 'COMPLETED') {
            foreach ($schedule->bookingTrips as $bt) {
                $bt->booking->update(['status' => 'COMPLETED']);
            }
        }

        // Notify passengers
        foreach ($schedule->tickets as $ticket) {
            $statusLabel = match ($newStatus) {
                'BOARDING' => 'Proses Boarding Penumpang Dimulai',
                'IN_TRANSIT' => 'Kendaraan Telah Berangkat / Dalam Perjalanan',
                'ARRIVED' => 'Kendaraan Telah Tiba Di Kota Tujuan',
                'COMPLETED' => 'Perjalanan Selesai',
                default => 'Status Perjalanan Diperbarui',
            };

            Notification::create([
                'user_id' => $ticket->booking->user_id,
                'title' => "Perjalanan {$schedule->route->origin} → {$schedule->route->destination}",
                'message' => $statusLabel,
                'type' => 'TRIP',
            ]);
        }

        return back()->with('success', 'Status perjalanan berhasil diperbarui menjadi '.$newStatus);
    }
}
