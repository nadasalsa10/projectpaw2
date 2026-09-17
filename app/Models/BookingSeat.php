<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_trip_id',
        'passenger_id',
        'vehicle_seat_id',
        'status',
    ];

    public function bookingTrip(): BelongsTo
    {
        return $this->belongsTo(BookingTrip::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function vehicleSeat(): BelongsTo
    {
        return $this->belongsTo(VehicleSeat::class);
    }
}
