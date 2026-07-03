<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle incoming request - cek role user.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!in_array($user->role, $roles)) {
            // Redirect ke dashboard sesuai role masing-masing
            $dashboard = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'doctor' => route('doctor.dashboard'),
                default => route('patient.dashboard'),
            };
            return redirect($dashboard)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}