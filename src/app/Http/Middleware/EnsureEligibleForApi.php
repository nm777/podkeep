<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEligibleForApi
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is not verified.',
            ], 403);
        }

        if (! $user->isApproved()) {
            return response()->json([
                'message' => 'Your account has not been approved.',
            ], 403);
        }

        return $next($request);
    }
}
