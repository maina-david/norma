<?php

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

if (!function_exists('singleTurboStreamResponse')) {
    /**
     * @param string $target
     * @param string $action
     *
     * @return \HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse
     */
    function singleTurboStreamResponse(string $target, string $action = 'update'): PendingTurboStreamResponse
    {
        /** @var PendingTurboStreamResponse $response */
        $response = response()->turboStream();

        return $response->target($target)->action($action);
    }
}

if (!function_exists('multipleTurboStreamResponse')) {
    /**
     * @param array<int, \HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse> $responses
     *
     * @return \HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse
     */
    function multipleTurboStreamResponse(array $responses): MultiplePendingTurboStreamResponse
    {
        /** @var MultiplePendingTurboStreamResponse */
        return response()->turboStream($responses);
    }
}

if (!function_exists('turboStreamResponse')) {
    /**
     * @param mixed                $view
     * @param array<string, mixed> $data
     *
     * @return \Illuminate\Http\Response
     */
    function turboStreamResponse(mixed $view, array $data = []): Response
    {
        return response()->turboStreamView($view, $data);
    }
}

if (!function_exists('userCanManageAllOrgs')) {
    /**
     * @param \App\Models\Auth\User|null $user
     *
     * @return bool
     */
    function userCanManageAllOrgs(?User $user = null): bool
    {
        /* @var User $user */
        $user ??= Auth::user();

        return $user ? $user->canManageAllOrganisations() : false;
    }
}

if (!function_exists('userIsOrgAdmin')) {
    /**
     * @param \App\Models\Auth\User|null $user
     *
     * @return bool
     */
    function userIsOrgAdmin(?User $user = null): bool
    {
        /** @var User $user */
        $user = $user ?? Auth::user();

        /** @var Organisation $organisation */
        $organisation = app(ActiveNormasManager::class)->getActiveOrganisation();

        return $user->isOrganisationAdmin($organisation);
    }
}

if (!function_exists('loadingQuote')) {
    /**
     * @return string
     */
    function loadingQuote(): string
    {
        return collect([
            "Don't worry, the wait will be worth it. We're adding an extra sprinkle of magic to your content!",
            'Our tech team is currently performing the digital equivalent of CPR on your content. Hang in there!',
            "The internet is like a garden, sometimes it takes a little while for the flowers to bloom. We're watering your content as we speak!",
            'Our content elves are crafting your personalized experience as we speak. They just need a bit more time to finish their work.',
            "We're cooking up something delicious for you, and like all good recipes, it takes time. But don't worry, the end result will be worth the wait!",
            'Think of it like waiting for a slow elevator. It might take a bit longer, but the view from the top is always worth it!',
            'The wait is almost over! We just need to iron out a few bugs before your content is ready to be served.',
            "We apologize for the delay, our resident sloth took a nap on the server. But we've woken him up and your content should be arriving shortly!",
            "We're in the process of upgrading our servers to run on pure unicorn magic. In the meantime, please enjoy this cute cat video while you wait!",
            "Generating your tasks at the speed of light! (Well, not quite, but we're working on it!)",
            "Roses are red, violets are blue, just hang tight, we're almost through! Generation in progress, it won't take long, we'll have your content, ready and strong!",
            "We apologize for the delay, our team of highly trained squirrels took a coffee break. But they're back to work now!",
            'Just like a good wine, your content needs time to mature. We promise it will be worth the wait!',
            'Our developers are currently performing a mystical dance to appease the digital gods. Your content should be ready soon!',
            "We're experiencing a bit of turbulence in the digital atmosphere, but our expert pilots are navigating us safely to your content!",
            'Our coding monkeys are busy typing away to bring you the best content possible. They just need a few more bananas before they can finish!',
            "We're currently using our patented snail-mail technology to bring you your content. Just kidding, it should be arriving shortly!",
            "We're adding some extra special sauce to your content. It's taking a bit longer than expected, but trust us, it's worth it!",
            'Think of it like waiting for the perfect cup of coffee. It might take a bit longer, but the end result will be delicious!',
            "Our servers are currently playing a game of hide and seek with your content. Don't worry, we'll find it soon!",
        ])->random();
    }
}

if (!function_exists('updateHyperlinks')) {
    /**
     * @param string $content
     *
     * @return string
     */
    function updateHyperlinks(string $content): string
    {
        /** @var string $content */
        $content = preg_replace('/([^>"]|^)(https:\/\/[^\s+]+)([^<"]?)/', '$1<a href="$2" target="_blank">$2</a>$3', $content);

        /** @var string */
        return str_replace('<a href=', '<a class="text-primary" href=', $content);
    }
}

if (!function_exists('app_redirect_back')) {
    /**
     * Redirect to the requested back address or to the previous URL.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function app_redirect_back(): RedirectResponse
    {
        $back = request('redirect_back');

        return $back ? redirect($back) : back();
    }
}

if (!function_exists('debug_log')) {
    /**
     * Redirect to the requested back address or to the previous URL.
     *
     * @param mixed $param
     *
     * @return void
     */
    function debug_log(mixed $param): void
    {
        Log::channel('teams-debug')->debug(print_r($param, true));
    }
}

if (!function_exists('html_to_text')) {
    /**
     * Convert the given html to plain text.
     *
     * @codeCoverageIgnore
     *
     * @param string $content
     *
     * @return string
     */
    function html_to_text(string $content): string
    {
        if ($enc = mb_detect_encoding($content)) {
            $content = mb_convert_encoding($content, 'UTF-8', $enc);
        }

        //        try {
        //            $purifier = new HTMLPurifier(HTMLPurifier_Config::create([]));
        //
        //            return app(HtmlToText::class)->convert($purifier->purify($content));
        //        } catch (Throwable) {
        return strip_tags($content);
        //        }
    }
}

if (!function_exists('qualify_column')) {
    /**
     * Qualify the given column.
     *
     * @param class-string $model
     * @param string       $column
     *
     * @return string
     */
    function qualify_column(string $model, string $column): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        return (new $model())->qualifyColumn($column);
    }
}

if (!function_exists('get_table')) {
    /**
     * Get the table name for the model.
     *
     * @param class-string $model
     *
     * @return string
     */
    function get_table(string $model, ?string $alias = null): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $table = (new $model())->getTable();

        return $alias ? "{$table} as {$alias}" : $table;
    }
}

if (!function_exists('multi_stream')) {
    /**
     * Check if the application is in multi-stream mode.
     *
     * @return bool
     */
    function multi_stream(): bool
    {
        return !app(ActiveNormasManager::class)->isSingleMode();
    }
}

if (!function_exists('active_norma')) {
    /**
     * Get the currently active norma.
     *
     * @return ?Norma
     */
    function active_norma(): ?Norma
    {
        return app(ActiveNormasManager::class)->getActive();
    }
}
