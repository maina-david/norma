<?php

namespace App\Http\Middleware;

use App\Managers\WhiteLabelManager;
use Closure;
use Illuminate\Http\Request;

class SetWhiteLabel
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
        $manager = app(WhiteLabelManager::class);
        $manager->activate();

        if (!$manager->isValid()) {
            return redirect()->away('https://libryo.com/contact-us/');
        }

        return $next($request);
    }
}
