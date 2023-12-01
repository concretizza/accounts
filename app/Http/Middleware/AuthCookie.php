<?php

namespace App\Http\Middleware;

use App\Enums\AuthEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->header('Authorization')) {
            $cookieValue = $request->cookie(AuthEnum::COOKIE->value);

            if ($cookieValue) {
                $request->headers->set('Authorization', 'Bearer '.$cookieValue);
            }
        }

        return $next($request);
    }
}
