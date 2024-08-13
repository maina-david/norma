<?php

namespace App\Providers;

use App\View\Composers\ThemeComposer;
use App\View\Composers\WhiteLabelComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', WhiteLabelComposer::class);
        View::composer('*', ThemeComposer::class);
    }
}
