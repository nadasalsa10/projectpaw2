<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load('customer');

        return view('customer.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,'.$user->id],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        if ($user->customer) {
            $user->customer->update([
                'address' => $validated['address'] ?? null,
                'emergency_phone' => $validated['emergency_phone'] ?? null,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
