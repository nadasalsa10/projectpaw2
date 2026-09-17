<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\Driver;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Separate Admin Login Page
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');

/*
|--------------------------------------------------------------------------
| Customer Public & Protected Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [Customer\HomeController::class, 'index'])->name('customer.home');
Route::get('/help', [Customer\HomeController::class, 'help'])->name('customer.help');
Route::get('/search', [Customer\SearchController::class, 'search'])->name('customer.search');

Route::middleware(['auth'])->group(function () {
    Route::get('/tickets/{ticket}', [Customer\TicketController::class, 'show'])->name('customer.tickets.show');
    Route::get('/orders/{booking}', [Customer\OrderController::class, 'show'])->name('customer.orders.show');
});

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/booking/select-seat', [Customer\BookingController::class, 'selectSeat'])->name('customer.booking.select_seat');
    Route::post('/booking/passengers', [Customer\BookingController::class, 'passengerForm'])->name('customer.booking.passengers');
    Route::post('/booking/summary', [Customer\BookingController::class, 'summary'])->name('customer.booking.summary');
    Route::post('/booking/store', [Customer\BookingController::class, 'storeBooking'])->name('customer.booking.store');

    Route::get('/payment/{booking}', [Customer\PaymentController::class, 'show'])->name('customer.payment.show');
    Route::post('/payment/{booking}/pay', [Customer\PaymentController::class, 'processPayment'])->name('customer.payment.pay');
    Route::get('/payment/{booking}/success', [Customer\PaymentController::class, 'success'])->name('customer.payment.success');

    Route::get('/orders', [Customer\OrderController::class, 'index'])->name('customer.orders.index');
    Route::post('/orders/{booking}/cancel', [Customer\OrderController::class, 'cancel'])->name('customer.orders.cancel');

    Route::get('/notifications', [Customer\OrderController::class, 'notifications'])->name('customer.notifications.index');
    Route::get('/profile', [Customer\ProfileController::class, 'show'])->name('customer.profile');
    Route::post('/profile', [Customer\ProfileController::class, 'update'])->name('customer.profile.update');
});

/*
|--------------------------------------------------------------------------
| Driver Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:driver'])->prefix('driver')->group(function () {
    Route::get('/dashboard', [Driver\DashboardController::class, 'index'])->name('driver.dashboard');
    Route::get('/profile', [Driver\DashboardController::class, 'profile'])->name('driver.profile');
    Route::get('/schedules', [Driver\ScheduleController::class, 'index'])->name('driver.schedules');
    Route::get('/schedules/{schedule}', [Driver\ScheduleController::class, 'show'])->name('driver.schedules.show');

    Route::get('/scan', [Driver\ScanController::class, 'index'])->name('driver.scan');
    Route::post('/scan/validate', [Driver\ScanController::class, 'validateQr'])->name('driver.scan.validate');
    Route::post('/checkin/{ticket}', [Driver\ScanController::class, 'checkIn'])->name('driver.checkin');

    Route::get('/reports', [Driver\ScheduleController::class, 'reports'])->name('driver.reports');

    Route::post('/schedules/{schedule}/status', [Driver\TripController::class, 'updateStatus'])->name('driver.trip.status');
});

/*
|--------------------------------------------------------------------------
| Admin Protected Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // Route Management
    Route::get('/routes', [Admin\RouteController::class, 'index'])->name('admin.routes.index');
    Route::post('/routes', [Admin\RouteController::class, 'store'])->name('admin.routes.store');
    Route::put('/routes/{route}', [Admin\RouteController::class, 'update'])->name('admin.routes.update');
    Route::delete('/routes/{route}', [Admin\RouteController::class, 'destroy'])->name('admin.routes.destroy');

    // Vehicle Management
    Route::get('/vehicles', [Admin\VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::post('/vehicles', [Admin\VehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::put('/vehicles/{vehicle}', [Admin\VehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('/vehicles/{vehicle}', [Admin\VehicleController::class, 'destroy'])->name('admin.vehicles.destroy');

    // Driver Management
    Route::get('/drivers', [Admin\DriverController::class, 'index'])->name('admin.drivers.index');
    Route::post('/drivers', [Admin\DriverController::class, 'store'])->name('admin.drivers.store');
    Route::put('/drivers/{driver}', [Admin\DriverController::class, 'update'])->name('admin.drivers.update');
    Route::delete('/drivers/{driver}', [Admin\DriverController::class, 'destroy'])->name('admin.drivers.destroy');

    // Schedule Management
    Route::get('/schedules', [Admin\ScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::post('/schedules', [Admin\ScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::put('/schedules/{schedule}', [Admin\ScheduleController::class, 'update'])->name('admin.schedules.update');
    Route::delete('/schedules/{schedule}', [Admin\ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    // Booking & Customer Data
    Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('/bookings/{booking}', [Admin\BookingController::class, 'show'])->name('admin.bookings.show');
    Route::put('/bookings/{booking}/status', [Admin\BookingController::class, 'updateStatus'])->name('admin.bookings.status');

    // Payments & Reports
    Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('admin.reports.index');
});
