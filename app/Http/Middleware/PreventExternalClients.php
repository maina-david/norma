<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventExternalClients
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request                                                                         $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $internal = auth('api')->client()?->internal;

        abort_unless($internal, 401, 'Unauthenticated');

        return $next($request);
    }
}
