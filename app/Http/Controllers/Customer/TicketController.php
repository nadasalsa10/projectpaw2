<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function show(Ticket $ticket)
    {
        if ($ticket->booking->user_id !== Auth::id() && ! Auth::user()->isDriver() && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        $ticket->load([
            'booking',
            'passenger',
            'schedule.route',
            'schedule.vehicle',
            'schedule.driver.user',
            'bookingSeat.vehicleSeat',
        ]);

        return view('customer.eticket', compact('ticket'));
    }
}
