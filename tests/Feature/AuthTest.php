<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIPP');
    }

    public function test_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'role' => 'customer',
            'name' => 'Budi Customer',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('customer.home'));
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_driver_can_register(): void
    {
        $response = $this->post('/register', [
            'role' => 'driver',
            'name' => 'Driver Agus',
            'email' => 'agus.driver@example.com',
            'phone' => '081299998888',
            'license_number' => 'SIM-B1-777666',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('driver.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'agus.driver@example.com',
            'role' => 'driver',
        ]);
        $this->assertDatabaseHas('drivers', [
            'license_number' => 'SIM-B1-777666',
        ]);
    }

    public function test_user_can_login_without_specifying_role(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '088811112222',
            'password' => Hash::make('secret123'),
            'role' => 'customer',
        ]);

        $response = $this->post('/login', [
            'login_identifier' => 'customer@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('customer.home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_driver_login_redirects_to_driver_dashboard(): void
    {
        $driverUser = User::create([
            'name' => 'Test Driver',
            'email' => 'driver@test.com',
            'phone' => '088833334444',
            'password' => Hash::make('secret123'),
            'role' => 'driver',
        ]);

        $response = $this->post('/login', [
            'login_identifier' => 'driver@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('driver.dashboard'));
        $this->assertAuthenticatedAs($driverUser);
    }

    public function test_customer_redirected_when_accessing_driver_or_admin_routes(): void
    {
        $customer = User::create([
            'name' => 'Customer Only',
            'email' => 'c@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer);

        $driverResponse = $this->get('/driver/dashboard');
        $driverResponse->assertRedirect(route('customer.home'));

        $adminResponse = $this->get('/admin/dashboard');
        $adminResponse->assertRedirect(route('customer.home'));
    }

    public function test_driver_redirected_when_accessing_customer_home(): void
    {
        $driverUser = User::create([
            'name' => 'Driver User',
            'email' => 'driver2@test.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
        ]);

        $this->actingAs($driverUser);

        $homeResponse = $this->get('/');
        $homeResponse->assertRedirect(route('driver.dashboard'));
    }
}
