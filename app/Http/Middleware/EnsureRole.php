<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $targetRoute = match ($request->user()->role) {
                'driver' => route('driver.dashboard'),
                'admin' => route('admin.dashboard'),
                default => route('customer.home'),
            };

            return redirect($targetRoute)->with('error', 'Akses ditolak: Halaman tersebut tidak sesuai dengan jenis akun Anda.');
        }

        return $next($request);
    }
}
