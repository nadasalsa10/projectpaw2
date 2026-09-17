<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::with('user')->orderBy('created_at', 'desc')->get();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'license_number' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'driver',
            ]);

            Driver::create([
                'user_id' => $user->id,
                'license_number' => $validated['license_number'],
                'status' => 'ACTIVE',
            ]);
        });

        return back()->with('success', 'Driver baru berhasil ditambahkan.');
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$driver->user_id],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,'.$driver->user_id],
            'license_number' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:ACTIVE,INACTIVE,ON_TRIP'],
        ]);

        DB::transaction(function () use ($driver, $validated) {
            $driver->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            $driver->update([
                'license_number' => $validated['license_number'],
                'status' => $validated['status'],
            ]);
        });

        return back()->with('success', 'Data driver berhasil diperbarui.');
    }

    public function destroy(Driver $driver)
    {
        DB::transaction(function () use ($driver) {
            $user = $driver->user;
            $driver->delete();
            $user?->delete();
        });

        return back()->with('success', 'Driver berhasil dihapus.');
    }
}
