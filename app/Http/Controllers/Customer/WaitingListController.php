<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaitingListController extends Controller
{
    public function index()
    {
        $waitingLists = WaitingList::where('user_id', Auth::id())
            ->with(['schedule.route', 'schedule.vehicle', 'schedule.driver.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.waiting_list', compact('waitingLists'));
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'passengers_count' => ['nullable', 'integer', 'min:1', 'max:6'],
        ]);

        $schedule = Schedule::findOrFail($validated['schedule_id']);
        $passengersCount = $validated['passengers_count'] ?? 1;

        // Check if user already in waiting list for this schedule
        $existing = WaitingList::where('schedule_id', $schedule->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['WAITING', 'NOTIFIED'])
            ->first();

        if ($existing) {
            return back()->with('info', 'Anda sudah terdaftar di Daftar Tunggu (Waiting List) untuk jadwal ini.');
        }

        $waitingList = WaitingList::create([
            'schedule_id' => $schedule->id,
            'user_id' => Auth::id(),
            'passengers_count' => $passengersCount,
            'status' => 'WAITING',
        ]);

        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Terdaftar di Waiting List SIPP',
            'message' => "Anda telah masuk ke daftar tunggu rute {$schedule->route->origin} → {$schedule->route->destination} ({$schedule->departure_time->format('d M Y - H:i')} WIB). Sistem akan otomatis memberi tahu Anda jika ada pembatalan tiket!",
            'type' => 'BOOKING',
        ]);

        return back()->with('success', 'Berhasil bergabung ke Daftar Tunggu (Waiting List)! Kami akan memberi notifikasi begitu kursi tersedia.');
    }

    public function cancel(WaitingList $waitingList)
    {
        if ($waitingList->user_id !== Auth::id()) {
            abort(403);
        }

        $waitingList->update(['status' => 'CANCELLED']);

        return back()->with('success', 'Pendaftaran Daftar Tunggu berhasil dibatalkan.');
    }
}
