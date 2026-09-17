<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::withCount('seats')->orderBy('created_at', 'desc')->get();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_plate' => ['required', 'string', 'max:50', 'unique:vehicles'],
            'capacity' => ['required', 'integer', 'min:4', 'max:24'],
            'facilities' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $vehicle = Vehicle::create([
                'name' => $validated['name'],
                'license_plate' => $validated['license_plate'],
                'capacity' => $validated['capacity'],
                'facilities' => $validated['facilities'] ?? null,
                'status' => 'ACTIVE',
            ]);

            // Auto-generate seat layout (A1, A2, B1, B2...) based on capacity
            $capacity = (int) $validated['capacity'];
            $rows = (int) ceil($capacity / 2);
            $seatIndex = 0;
            $letters = range('A', 'Z');

            for ($r = 0; $r < $rows; $r++) {
                $rowLetter = $letters[$r] ?? ('R'.($r + 1));
                for ($c = 1; $c <= 2; $c++) {
                    if ($seatIndex >= $capacity) {
                        break;
                    }

                    VehicleSeat::create([
                        'vehicle_id' => $vehicle->id,
                        'seat_number' => $rowLetter.$c,
                        'row_index' => $r,
                        'column_index' => $c - 1,
                        'is_active' => true,
                    ]);

                    $seatIndex++;
                }
            }
        });

        return back()->with('success', 'Kendaraan dan konfigurasi kursi berhasil dibuat.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_plate' => ['required', 'string', 'max:50', 'unique:vehicles,license_plate,'.$vehicle->id],
            'facilities' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,MAINTENANCE'],
        ]);

        $vehicle->update($validated);

        return back()->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return back()->with('success', 'Kendaraan berhasil dihapus.');
    }
}
