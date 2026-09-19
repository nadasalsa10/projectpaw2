<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\BookingTrip;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSeat;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTest extends TestCase
{
    use RefreshDatabase;

    protected User $driverUser;

    protected Driver $driver;

    protected Schedule $schedule;

    protected Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverUser = User::create([
            'name' => 'Driver Agus',
            'email' => 'driver@test.com',
            'phone' => '081299998888',
            'password' => Hash::make('password'),
            'role' => 'driver',
        ]);

        $this->driver = Driver::create([
            'user_id' => $this->driverUser->id,
            'license_number' => 'SIM-B1-888999',
            'status' => 'ACTIVE',
        ]);

        $route = Route::create([
            'origin' => 'Palembang',
            'destination' => 'Jambi',
            'duration_minutes' => 360,
            'base_price' => 200000.00,
            'is_active' => true,
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Toyota Hiace',
            'license_plate' => 'BG 1111 AA',
            'capacity' => 12,
            'status' => 'ACTIVE',
        ]);

        $seat = VehicleSeat::create([
            'vehicle_id' => $vehicle->id,
            'seat_number' => 'A1',
            'is_active' => true,
        ]);

        $this->schedule = Schedule::create([
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $this->driver->id,
            'departure_time' => Carbon::now()->addHours(2),
            'arrival_time' => Carbon::now()->addHours(8),
            'price' => 200000.00,
            'status' => 'WAITING',
        ]);

        $customer = User::create([
            'name' => 'Customer Test',
            'email' => 'c@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $booking = Booking::create([
            'booking_code' => 'SIPP-TEST-001',
            'user_id' => $customer->id,
            'trip_type' => 'ONE_WAY',
            'total_amount' => 200000.00,
            'status' => 'PAID',
        ]);

        $bt = BookingTrip::create([
            'booking_id' => $booking->id,
            'schedule_id' => $this->schedule->id,
            'direction' => 'OUTBOUND',
        ]);

        $passenger = Passenger::create([
            'booking_id' => $booking->id,
            'name' => 'Penumpang Agus',
        ]);

        $bSeat = BookingSeat::create([
            'booking_trip_id' => $bt->id,
            'passenger_id' => $passenger->id,
            'vehicle_seat_id' => $seat->id,
            'status' => 'BOOKED',
        ]);

        $this->ticket = Ticket::create([
            'ticket_code' => 'TKT-TEST-001',
            'booking_id' => $booking->id,
            'passenger_id' => $passenger->id,
            'schedule_id' => $this->schedule->id,
            'booking_seat_id' => $bSeat->id,
            'qr_code_hash' => 'hash_secret_123456',
            'is_checked_in' => false,
        ]);
    }

    public function test_driver_can_view_dashboard_and_assigned_schedule(): void
    {
        $this->actingAs($this->driverUser);

        $response = $this->get('/driver/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Driver Agus');
        $response->assertSee('Palembang');
    }

    public function test_driver_can_validate_qr_and_checkin_passenger(): void
    {
        $this->actingAs($this->driverUser);

        // Validate QR Code via AJAX
        $valResponse = $this->postJson('/driver/scan/validate', [
            'code' => 'hash_secret_123456',
        ]);

        $valResponse->assertStatus(200);
        $valResponse->assertJson(['valid' => true, 'already_checked_in' => false]);

        // Perform Check-In
        $checkinResponse = $this->postJson("/driver/checkin/{$this->ticket->id}");
        $checkinResponse->assertStatus(200);
        $checkinResponse->assertJson(['success' => true]);

        $this->ticket->refresh();
        $this->assertTrue($this->ticket->is_checked_in);
    }

    public function test_driver_can_checkin_passenger_via_web_form(): void
    {
        $this->actingAs($this->driverUser);

        $checkinResponse = $this->post("/driver/checkin/{$this->ticket->id}");
        $checkinResponse->assertRedirect();
        $checkinResponse->assertSessionHas('success');

        $this->ticket->refresh();
        $this->assertTrue($this->ticket->is_checked_in);
    }

    public function test_driver_can_update_trip_status(): void
    {
        $this->actingAs($this->driverUser);

        $response = $this->post("/driver/schedules/{$this->schedule->id}/status", [
            'status' => 'IN_TRANSIT',
        ]);

        $response->assertSessionHas('success');
        $this->schedule->refresh();
        $this->assertEquals('IN_TRANSIT', $this->schedule->status);
    }

    public function test_driver_can_view_reports_page(): void
    {
        $this->actingAs($this->driverUser);

        $response = $this->get('/driver/reports');
        $response->assertStatus(200);
        $response->assertSee('Laporan Pemesanan');
        $response->assertSee('Penumpang Agus');
        $response->assertSee('TKT-TEST-001');
    }
}
