<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'passengers_count',
        'status',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function notifyWaitingUsers(Schedule $schedule): void
    {
        $schedule->loadMissing('route');

        $waitingEntries = static::where('schedule_id', $schedule->id)
            ->where('status', 'WAITING')
            ->orderBy('created_at', 'asc')
            ->get();

        $origin = $schedule->route?->origin ?? 'Asal';
        $destination = $schedule->route?->destination ?? 'Tujuan';
        $timeStr = $schedule->departure_time ? $schedule->departure_time->format('d M Y - H:i') : '-';

        foreach ($waitingEntries as $wl) {
            $wl->update([
                'status' => 'NOTIFIED',
                'notified_at' => now(),
            ]);

            Notification::create([
                'user_id' => $wl->user_id,
                'title' => '⚡ Kursi Travel Tersedia Kembali!',
                'message' => "Kabar gembira! Ada pembatalan tiket pada rute {$origin} → {$destination} ({$timeStr} WIB). Segera pesan sebelum kehabisan!",
                'type' => 'BOOKING',
            ]);
        }
    }
}
