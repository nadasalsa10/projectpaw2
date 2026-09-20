<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'departure_time',
        'arrival_time',
        'price',
        'status',
        'is_extra',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
            'price' => 'decimal:2',
            'is_extra' => 'boolean',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function bookingTrips(): HasMany
    {
        return $this->hasMany(BookingTrip::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function waitingLists(): HasMany
    {
        return $this->hasMany(WaitingList::class);
    }

    public function getAvailableSeatsCountAttribute(): int
    {
        $totalCapacity = $this->vehicle ? $this->vehicle->capacity : 12;

        $bookedSeats = BookingSeat::whereHas('bookingTrip', function ($q) {
            $q->where('schedule_id', $this->id)
                ->whereHas('booking', function ($bq) {
                    $bq->whereIn('status', ['PENDING_PAYMENT', 'PAYMENT_PROCESSING', 'PAID', 'CONFIRMED', 'WAITING_DEPARTURE', 'BOARDING', 'IN_TRANSIT']);
                });
        })->whereIn('status', ['LOCKED', 'BOOKED'])->count();

        return max(0, $totalCapacity - $bookedSeats);
    }

    public function isFull(): bool
    {
        return $this->available_seats_count <= 0;
    }

    public static function rebalanceDriverAssignments(): void
    {
        $drivers = Driver::where('status', 'ACTIVE')->get();
        if ($drivers->isEmpty()) {
            return;
        }

        $driverCount = $drivers->count();
        $schedules = self::where('status', 'WAITING')
            ->where('departure_time', '>=', now())
            ->orderBy('id', 'asc')
            ->get();

        foreach ($schedules as $idx => $schedule) {
            $assignedDriver = $drivers[$idx % $driverCount];
            if ($schedule->driver_id !== $assignedDriver->id) {
                $schedule->update(['driver_id' => $assignedDriver->id]);
            }
        }
    }
}
