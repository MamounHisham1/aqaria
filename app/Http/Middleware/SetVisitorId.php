<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetVisitorId
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $visitorId = $request->cookie('visitor_id');

        if (! $visitorId) {
            $visitorId = bin2hex(random_bytes(16));

            $response = $next($request);

            $response->withCookie(cookie(
                'visitor_id',
                $visitorId,
                30 * 24 * 60 // 30 days
            ));

            return $response;
        }

        return $next($request);
    }
}
