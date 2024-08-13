<?php

namespace App\Http\Middleware;

use App\Managers\AppManager;
use Closure;
use Illuminate\Http\Request;

class SetCorrectApp
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @param string                   $current
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $current)
    {
        app(AppManager::class)->setApp($current);

        return $next($request);
    }
}
