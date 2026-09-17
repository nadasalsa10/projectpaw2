<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::orderBy('created_at', 'desc')->get();

        return view('admin.routes.index', compact('routes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255', 'different:origin'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'base_price' => ['required', 'numeric', 'min:0'],
        ]);

        Route::create([
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'duration_minutes' => $validated['duration_minutes'],
            'base_price' => $validated['base_price'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Rute perjalanan baru berhasil ditambahkan.');
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255', 'different:origin'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $route->update($validated);

        return back()->with('success', 'Data rute berhasil diperbarui.');
    }

    public function destroy(Route $route)
    {
        $route->delete();

        return back()->with('success', 'Rute berhasil dihapus.');
    }
}
