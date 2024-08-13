<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventNonOrganisationalClients
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
        $organisation = auth('api')->client()?->organisation_id;

        abort_unless($organisation, 401, 'Unauthenticated');

        abort_unless($organisation == $request->route('organisation'), 403, 'Forbidden');

        return $next($request);
    }
}
