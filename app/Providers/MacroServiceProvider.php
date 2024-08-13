<?php

namespace App\Providers;

use App\Http\Services\Api\ApiResourceRequestFilter;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Router::macro('stepper', function ($endpoint, $controller, $name, $options = []) {
            $applyOptions = fn ($item) => collect($options)->each(fn ($params, $method) => $item->{$method}($params));

            tap($this->get("{$endpoint}/{stage?}", "{$controller}@create")->name("{$name}.create"), $applyOptions);
            tap($this->post("{$endpoint}/{stage}", "{$controller}@store")->name("{$name}.store"), $applyOptions);
        });

        // commented as it hasn't been used anywhere yet....
        // Blade::if('collaborateCrud', function (array $models) {
        //     $permissions = collect($models)
        //         ->reduce(function ($previous, $model) {
        //             $model = collect(['viewAny', 'view', 'create', 'update', 'delete', 'restore'])
        //                 ->map(fn ($item) => "collaborate.{$model}.{$item}");

        //             return $previous->concat($model);
        //         }, collect())
        //         ->toArray();

        //     if ($user = Auth::user()) {
        //         return $user->canAny($permissions);
        //     }

        //     return false;
        // });

        Blade::if('collaborateViewAny', function (array $models) {
            $permissions = collect($models)
                ->reduce(fn ($previous, $model) => $previous->push("collaborate.{$model}.viewAny"), collect())
                ->toArray();

            if ($user = Auth::user()) {
                return $user->canAny($permissions);
            }

            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        });

        Blade::if('ifOrgAdmin', function () {
            return userIsOrgAdmin();
        });

        Blade::if('ifOrgHasModule', function ($module) {
            /** @var Organisation */
            $organisation = app(ActiveLibryosManager::class)->getActiveOrganisation();

            return $organisation->hasModule($module);
        });

        $this->registerAuthMacros();
        $this->registerRequestMacros();
    }

    protected function registerAuthMacros(): void
    {
        SessionGuard::macro('quietLogin', function (Authenticatable $user) {
            // @phpstan-ignore-next-line
            $this->updateSession($user->getAuthIdentifier());
            $this->setUser($user);
        });
        SessionGuard::macro('quietLogout', function () {
            // @phpstan-ignore-next-line
            $this->clearUserDataFromStorage();
            // @phpstan-ignore-next-line
            $this->user = null;
            // @phpstan-ignore-next-line
            $this->loggedOut = true;
        });
    }

    protected function registerRequestMacros(): void
    {
        Request::macro('getFieldsFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getFields());
        Request::macro('getPageFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getPage());
        Request::macro('getPerPageFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getPerPage());
        Request::macro('getFiltersFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getFilters());
        Request::macro('getOrderFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getOrder());
        Request::macro('getCountsFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getCounts());
        Request::macro('getHasFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getHas());
        Request::macro('getIncludesFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getIncludes());
        Request::macro('getIdsFromQueryParams', fn () => (new ApiResourceRequestFilter($this))->getIds());
    }
}
