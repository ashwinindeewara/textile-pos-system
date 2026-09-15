<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            // Redirect based on role if logged in but accessing unauthorized section
            if ($request->user()->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'Unauthorized access.');
            }
            if ($request->user()->isCashier()) {
                return redirect()->route('pos.index')->with('error', 'Unauthorized access.');
            }
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
