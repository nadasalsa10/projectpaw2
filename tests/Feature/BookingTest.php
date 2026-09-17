<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSeat;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Schedule $schedule;

    protected VehicleSeat $seat1;

    protected VehicleSeat $seat2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Tester Customer',
            'email' => 'customer@test.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'customer',
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
            'license_plate' => 'BG 8888 ZZ',
            'capacity' => 12,
            'status' => 'ACTIVE',
        ]);

        $this->seat1 = VehicleSeat::create([
            'vehicle_id' => $vehicle->id,
            'seat_number' => 'A1',
            'is_active' => true,
        ]);

        $this->seat2 = VehicleSeat::create([
            'vehicle_id' => $vehicle->id,
            'seat_number' => 'A2',
            'is_active' => true,
        ]);

        $this->schedule = Schedule::create([
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'departure_time' => Carbon::now()->addDays(2),
            'arrival_time' => Carbon::now()->addDays(2)->addHours(6),
            'price' => 200000.00,
            'status' => 'WAITING',
        ]);
    }

    public function test_customer_can_search_travel(): void
    {
        $response = $this->get('/search?'.http_build_query([
            'origin' => 'Palembang',
            'destination' => 'Jambi',
            'trip_type' => 'ONE_WAY',
            'departure_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'passengers' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Palembang');
        $response->assertSee('Jambi');
    }

    public function test_customer_can_store_booking_and_process_payment(): void
    {
        $this->actingAs($this->customer);

        $response = $this->post('/booking/store', [
            'outbound_schedule_id' => $this->schedule->id,
            'trip_type' => 'ONE_WAY',
            'outbound_seats' => [$this->seat1->id],
            'payment_method' => 'BANK_TRANSFER',
            'passengers' => [
                ['name' => 'Penampung 1', 'phone' => '0811223344'],
            ],
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertEquals('PENDING_PAYMENT', $booking->status);

        // Process payment
        $payResponse = $this->post("/payment/{$booking->id}/pay");
        $payResponse->assertRedirect(route('customer.payment.success', $booking->id));

        $booking->refresh();
        $this->assertEquals('PAID', $booking->status);
        $this->assertDatabaseHas('tickets', [
            'booking_id' => $booking->id,
            'is_checked_in' => false,
        ]);
    }
}
