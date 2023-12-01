<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user()->account;

        if (! $account->hasActiveSubscription()) {
            return response()->json([
                'message' => trans('payment.subscription_invalid'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
