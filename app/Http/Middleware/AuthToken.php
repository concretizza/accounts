<?php

namespace App\Http\Middleware;

use App\Services\AccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Authorization')) {
            $token = $request->bearerToken();

            try {
                $accessToken = AccessTokenService::decode($token);
                $request->headers->set('Authorization', 'Bearer '.$accessToken->tok);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => trans('auth.invalid'),
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }
}
