<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftRole
{
    /**
     * Restrict POS routes according to the currently active shift staff's role.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $staff = User::currentShiftStaff();

        if (! $staff || $roles === [] || in_array($staff->role, $roles, true)) {
            return $next($request);
        }

        return redirect()
            ->route($staff->defaultRouteName())
            ->with('error', "Akses dibatasi untuk peran {$staff->role_label}. Dialihkan ke halaman tugas {$staff->name}.");
    }
}
