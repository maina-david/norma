<?php

namespace App\Http\Middleware;

use App\Managers\AppManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SetCorrectSessionDomain
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $app = AppManager::appDomain();
        $req = app(AppManager::class)->requestDomain();
        $prefix = $app === $req ? '.' : '';

        Config::set('session.domain', $prefix . $req);

        return $next($request);
    }
}
