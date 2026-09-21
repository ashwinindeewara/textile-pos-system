<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemNotLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin() || $request->routeIs('system.locked')) {
            return $next($request);
        }

        if (SubscriptionService::isLocked()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'locked' => true,
                    'message' => 'This system is locked. Please contact your service provider.',
                ], 403);
            }

            return redirect()->route('system.locked');
        }

        return $next($request);
    }
}
