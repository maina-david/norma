<?php

namespace App\Http\Middleware;

use App\Actions\System\ApiLog\LogApiCall;
use Closure;
use Illuminate\Http\Request;

class LogApiCalls
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
        $response = $next($request);

        LogApiCall::run($request, $response);

        return $response;
    }
}
