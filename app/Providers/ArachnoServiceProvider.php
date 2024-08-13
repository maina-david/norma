<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/**
 * @codeCoverageIgnore
 */
class ArachnoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('arachno.horizon_arachno_only')) {
            $appEnv = app()->environment();
            Config::set('horizon.environments.' . $appEnv, config('horizon.environments.arachno-only-' . $appEnv));
            // also override defaults, as that is merged into environment
            $supervisor1Defaults = config('horizon.defaults.supervisor-1');
            Config::set('horizon.defaults', ['supervisor-1' => $supervisor1Defaults]);
        }
    }
}
