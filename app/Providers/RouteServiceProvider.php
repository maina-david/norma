<?php

namespace App\Providers;

use App\Managers\AppManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->apiRoutes();
            $this->webRoutes();
        });
    }

    /**
     * Get the extra staging endpoints.
     *
     * @param string $domain
     *
     * @return string
     */
    protected function getExtraMatch(string $domain): string
    {
        $extraMatch = '';

        if (config('app.env') === 'staging') {
            // @codeCoverageIgnoreStart
            $domainString = str_replace('.', '\.', $domain);

            /** @var Request $request */
            $request = request();

            preg_match("/(?:collaborate|admin|api)(\d+).{$domainString}/", $request->url(), $matches);

            $extraMatch = empty($matches) ? $extraMatch : $matches[1];
            // @codeCoverageIgnoreEnd
        }

        return $extraMatch;
    }

    /**
     * Add the web routes.
     *
     * @return void
     */
    protected function webRoutes(): void
    {
        $domain = AppManager::appDomain();
        $extraMatch = $this->getExtraMatch($domain);

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/auth.php'));

        Route::middleware(['web', 'auth', 'set-app:collaborate', 'can:collaborate.access', 'active.user'])
            ->domain("collaborate{$extraMatch}." . $domain)
            ->name('collaborate.')
            ->group(base_path('routes/collaborate.php'));

        Route::middleware(['web', 'set-app:collaborate'])
            ->domain("collaborate{$extraMatch}." . $domain)
            ->name('collaborate.')
            ->group(base_path('routes/collaborate-guest.php'));

        Route::middleware(['web', 'auth', 'set-app:admin', 'can:admin.access', 'active.user'])
            ->domain("admin{$extraMatch}." . $domain)
            ->name('admin.')
            ->group(base_path('routes/admin.php'));

        // matches my*.norma and other whitelabel routes
        Route::middleware([
            'web',
            'auth',
            'set-app:my',
            'can:access.org.settings',
            'active.org',
            'active.user',
        ])
            // ->domain("my{$extraMatch}." . $domain)
            ->name('my.settings.')
            ->prefix('/settings')
            ->group(base_path('routes/settings.php'));

        Route::middleware(['web', 'auth', 'set-app:my', 'can:my.access', 'active.user'])
            ->namespace($this->namespace)
//            ->domain("my{$extraMatch}." . $domain)
            ->name('my.')
            ->group(base_path('routes/my.php'));

        Route::middleware(['web', 'set-app:my'])
            ->namespace($this->namespace)
//            ->domain("my{$extraMatch}." . $domain)
            ->name('my.')
            ->group(base_path('routes/guest.php'));
    }

    protected function apiRoutes(): void
    {
        $domain = AppManager::appDomain();
        $extraMatch = $this->getExtraMatch($domain);

        Route::prefix('api/v1')
            ->middleware(['api', 'auth:api'])
            ->name('api.v1.')
            ->namespace($this->namespace)
            ->domain('api.' . $domain)
            ->group(base_path('routes/api-v1.php'));

        Route::prefix('api/v2')
            ->middleware(['api', 'auth:api'])
            ->name('api.v2.')
            ->namespace($this->namespace)
            ->domain('api.' . $domain)
            ->group(base_path('routes/api-v2.php'));

        Route::prefix('api/v2')
            ->middleware(['api'])
            ->name('api.v2.')
            ->namespace($this->namespace)
            ->domain('api.' . $domain)
            ->group(base_path('routes/passport-v2.php'));

        Route::prefix('api/v2')
            ->middleware(['api'])
            ->name('api.v2.public.')
            ->namespace($this->namespace)
            ->domain('api.' . $domain)
            ->group(base_path('routes/api-v2-public.php'));

        // can be removed once no longer needed by cleanchain
        Route::name('api.public.')
            ->namespace($this->namespace)
            ->domain('api.' . $domain)
            ->group(base_path('routes/api-public.php'));

        Route::prefix('api/v1')
            ->middleware(['api'])
            ->name('api.v1.')
            ->namespace($this->namespace)
            ->domain('api.' . $domain)
            ->group(base_path('routes/passport-v1.php'));

        Route::prefix('api/internals')
            ->withoutMiddleware(['throttle:api'])
            ->middleware(['api:stateful', 'auth:sanctum', 'set-app:collaborate', 'can:collaborate.access', 'active.user', 'throttle:internal-api'])
            ->name('api.collaborate.')
            ->namespace($this->namespace)
            ->domain("collaborate{$extraMatch}." . $domain)
            ->group(base_path('routes/collaborate-api.php'));

        Route::prefix('api/v3')
            ->middleware(['api', 'client', 'client.org'])
            ->name('api.v3.')
            ->namespace($this->namespace)
            ->domain("api{$extraMatch}." . $domain)
            ->group(base_path('routes/api-v3.php'));

        Route::prefix('api/internals')
            ->withoutMiddleware(['throttle:api'])
            ->middleware(['api:stateful', 'auth:sanctum', 'set-app:my', 'can:my.access',  'active.user', 'throttle:internal-api'])
            ->name('api.my.')
            ->namespace($this->namespace)
//            ->domain("my{$extraMatch}." . $domain)
            ->group(base_path('routes/my-api.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('heavy-api', function (Request $request) {
            return Limit::perMinute(6)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('internal-api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('guest', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip() ?? '');
        });
    }
}
