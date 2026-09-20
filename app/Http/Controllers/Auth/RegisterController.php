<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:customer,driver'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'required_if:role,driver', 'string', 'max:50'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            if ($validated['role'] === 'customer') {
                Customer::create([
                    'user_id' => $user->id,
                    'address' => $validated['address'] ?? null,
                    'emergency_phone' => $validated['emergency_phone'] ?? null,
                ]);
            } else {
                $driver = Driver::create([
                    'user_id' => $user->id,
                    'license_number' => $validated['license_number'] ?? ('SIM-'.strtoupper(substr(md5(uniqid()), 0, 8))),
                    'status' => 'ACTIVE',
                ]);

                // Automatically rebalance and distribute schedules evenly across all active drivers
                Schedule::rebalanceDriverAssignments();
            }

            return $user;
        });

        Auth::login($user);

        if ($user->isDriver()) {
            return redirect()->route('driver.dashboard')->with('success', 'Pendaftaran Akun Driver berhasil! Selamat datang di SIPP.');
        }

        return redirect()->route('customer.home')->with('success', 'Pendaftaran akun Customer berhasil! Selamat datang di SIPP.');
    }
}
