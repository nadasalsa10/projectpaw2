<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\BookingTrip;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSeat;
use App\Models\WaitingList;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $waitingCustomer;

    protected User $admin;

    protected Schedule $schedule;

    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin SIPP',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Booked',
            'email' => 'customer1@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $this->waitingCustomer = User::create([
            'name' => 'Customer Waiting',
            'email' => 'customer2@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $driverUser = User::create([
            'name' => 'Driver Test',
            'email' => 'driver@test.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
        ]);

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'SIM-123',
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
            'name' => 'Toyota Hiace 1-Seat',
            'license_plate' => 'BG 1111 WL',
            'capacity' => 1,
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
            'driver_id' => $driver->id,
            'departure_time' => Carbon::now()->addDays(2),
            'arrival_time' => Carbon::now()->addDays(2)->addHours(6),
            'price' => 200000.00,
            'status' => 'WAITING',
            'is_extra' => false,
        ]);

        // Create booking filling all 1 seat of this schedule
        $this->booking = Booking::create([
            'booking_code' => 'SIPP-WL-001',
            'user_id' => $this->customer->id,
            'trip_type' => 'ONE_WAY',
            'total_amount' => 200000.00,
            'status' => 'PAID',
        ]);

        $bt = BookingTrip::create([
            'booking_id' => $this->booking->id,
            'schedule_id' => $this->schedule->id,
            'direction' => 'OUTBOUND',
        ]);

        $passenger = Passenger::create([
            'booking_id' => $this->booking->id,
            'name' => 'Penumpang One',
        ]);

        BookingSeat::create([
            'booking_trip_id' => $bt->id,
            'passenger_id' => $passenger->id,
            'vehicle_seat_id' => $seat->id,
            'status' => 'BOOKED',
        ]);
    }

    public function test_customer_can_join_waiting_list_for_full_schedule(): void
    {
        $this->actingAs($this->waitingCustomer);

        $response = $this->post('/waiting-list/join', [
            'schedule_id' => $this->schedule->id,
            'passengers_count' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waiting_lists', [
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->waitingCustomer->id,
            'status' => 'WAITING',
        ]);
    }

    public function test_cancelling_booking_notifies_waiting_list_users(): void
    {
        // Add user to waiting list
        $wl = WaitingList::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->waitingCustomer->id,
            'passengers_count' => 1,
            'status' => 'WAITING',
        ]);

        // Customer cancels booking
        $this->actingAs($this->customer);
        $response = $this->post("/orders/{$this->booking->id}/cancel");
        $response->assertSessionHas('success');

        $wl->refresh();
        $this->assertEquals('NOTIFIED', $wl->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->waitingCustomer->id,
            'title' => '⚡ Kursi Travel Tersedia Kembali!',
        ]);
    }

    public function test_customer_can_view_and_cancel_waiting_list(): void
    {
        $this->actingAs($this->waitingCustomer);

        $wl = WaitingList::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->waitingCustomer->id,
            'passengers_count' => 1,
            'status' => 'WAITING',
        ]);

        $response = $this->get('/waiting-list');
        $response->assertStatus(200);
        $response->assertSee('Daftar Tunggu');

        $cancelResp = $this->post("/waiting-list/{$wl->id}/cancel");
        $cancelResp->assertSessionHas('success');

        $wl->refresh();
        $this->assertEquals('CANCELLED', $wl->status);
    }

    public function test_admin_can_create_extra_mudik_schedule(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/schedules', [
            'route_id' => $this->schedule->route_id,
            'vehicle_id' => $this->schedule->vehicle_id,
            'driver_id' => $this->schedule->driver_id,
            'departure_time' => Carbon::now()->addDays(3)->format('Y-m-d\TH:i'),
            'price' => 250000,
            'is_extra' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('schedules', [
            'price' => 250000,
            'is_extra' => true,
        ]);
    }
}
