<?php

namespace App\Providers;

use App\Http\Services\NormaAI\Client;
use App\Managers\ThemeManager;
use App\Managers\WhiteLabelManager;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Customer\ActiveOrganisationManager;
use App\Services\Search\NormaAI\NormaAISearchEngine;
use App\Services\Translation\GoogleTranslator;
use App\Services\Translation\TranslatorServiceInterface;
use App\Themes\Collaborate;
use HubSpot\Discovery\Discovery;
use HubSpot\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Scout\EngineManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TranslatorServiceInterface::class, GoogleTranslator::class);

        $this->app->singleton(Discovery::class, function ($app) {
            // @codeCoverageIgnoreStart
            return Factory::createWithAccessToken($app->make('config')->get('services.hubspot.api_key'));
            // @codeCoverageIgnoreEnd
        });

        $this->app->scoped(ActiveOrganisationManager::class, function ($app) {
            return new ActiveOrganisationManager();
        });

        $this->app->scoped(ActiveNormasManager::class, function ($app) {
            return new ActiveNormasManager($app->make(ActiveOrganisationManager::class));
        });

        if ($this->app->environment('local')) {
            // @codeCoverageIgnoreStart
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(!$this->app->isProduction());

        app(WhiteLabelManager::class)->register();

        app(ThemeManager::class)->register([
            new Collaborate(),
        ]);

        Relation::morphMap([
            'works' => Work::class,
            'expressions' => WorkExpression::class,
        ]);

        // @codeCoverageIgnoreStart
        resolve(EngineManager::class)->extend('norma-ai', function () {
            return new NormaAISearchEngine(app(Client::class));
        });

        Pulse::user(fn (User $user) => [
            'name' => $user->full_name,
            'extra' => $user->email,
            'avatar' => $user->profile_photo_url,
        ]);

        Gate::define('viewPulse', function (User $user) {
            return in_array($user->email, [
                'sam.cloete@norma.com',
                'david.mjomba@norma.com',
            ]);
        });
        // @codeCoverageIgnoreEnd
    }
}
