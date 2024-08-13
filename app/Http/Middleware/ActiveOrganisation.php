<?php

namespace App\Http\Middleware;

use App\Enums\Customer\OrganisationSwitcherMode;
use App\Models\Auth\User;
use App\Services\Customer\ActiveOrganisationManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ActiveOrganisation
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
        if ($organisationId = $request->input('activateOrgId')) {
            app(ActiveOrganisationManager::class)->activate($organisationId);
            /** @var \Illuminate\Routing\Route $route */
            $route = $request->route();

            /** @var string $name */
            $name = $route->getName();

            return redirect()->route($name, $route->parameters());
        }

        if (Session::has(config('session-keys.customer.active-organisation'))) {
            return $next($request);
        }

        if (Session::has(config('session-keys.customer.organisation-mode'))) {
            return $next($request);
        }

        /** @var User $user */
        $user = Auth::user();
        $manager = app(ActiveOrganisationManager::class);
        if ($user->canManageAllOrganisations()) {
            $manager->setMode(OrganisationSwitcherMode::all());

            return $next($request);
        }

        $manager->activateFirstAvailable($user);

        return $next($request);
    }
}
