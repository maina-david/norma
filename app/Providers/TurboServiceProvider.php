<?php

namespace App\Providers;

use App\Http\Turbo\TurboResponseFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class TurboServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->bindRequestAndResponseMacros();
    }

    /**
     * @return void
     */
    private function bindRequestAndResponseMacros(): void
    {
        Response::macro('turboStreamView', function ($view, array $data = []) {
            if (!$view instanceof View) {
                // @codeCoverageIgnoreStart
                /** @var View $view */
                $view = view($view, $data);
                // @codeCoverageIgnoreEnd
            }

            return TurboResponseFactory::makeStream($view);
        });

        Request::macro('isHotwire', function () {
            /** @var Request $this */
            /* @phpstan-ignore-next-line */
            return Str::contains($this->header('accept'), 'text/vnd.turbo-stream.html');
        });

        Request::macro('isForTurboFrame', function () {
            /** @var Request $this */
            return !empty($this->header('turbo-frame'));
        });
    }
}
