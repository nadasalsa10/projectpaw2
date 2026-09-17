<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('trip_type', ['ONE_WAY', 'ROUND_TRIP'])->default('ONE_WAY');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'PENDING_PAYMENT',
                'PAYMENT_PROCESSING',
                'PAID',
                'CONFIRMED',
                'WAITING_DEPARTURE',
                'BOARDING',
                'IN_TRANSIT',
                'COMPLETED',
                'CANCELLED',
                'EXPIRED',
            ])->default('PENDING_PAYMENT');
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
