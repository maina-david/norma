<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // just to make sure we don't accidentaly send mail in staging or local
        if ($this->app->environment('local') || $this->app->environment('staging') || $this->app->environment('testing')) {
            Mail::alwaysTo('tech@norma.com');
        }
    }
}
