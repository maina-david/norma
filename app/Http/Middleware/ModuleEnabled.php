<?php

namespace App\Http\Middleware;

use App\Enums\System\LibryoModule;
use App\Services\Customer\ActiveLibryosManager;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use RuntimeException;

class ModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @param string                   $module
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $module)
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        if (!in_array($module, LibryoModule::options())) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Module "' . $module . '" does not exist');
            // @codeCoverageIgnoreEnd
        }

        if (!$organisation->hasModule($module)) {
            // @codeCoverageIgnoreStart
            /** @var string */
            $moduleStr = __('customer.organisation.modules.' . $module);
            /** @var string */
            $message = __('customer.organisation.module_not_enabled', ['module' => $moduleStr]);
            throw new AuthorizationException($message);
            // @codeCoverageIgnoreEnd
        }

        return $next($request);
    }
}
