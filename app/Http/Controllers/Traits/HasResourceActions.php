<?php

namespace App\Http\Controllers\Traits;

use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

trait HasResourceActions
{
    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    abstract protected static function resourceRoute(): string;

    /**
     * Get the action route for the resource.
     *
     * @return string
     */
    protected static function resourceActionRoute(): string
    {
        $route = sprintf('%s.actions', static::resourceRoute());

        return Route::has($route) ? route($route) : '';
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        return [];
    }

    /**
     * Filter the actions that the user can't access.
     *
     * @return array<int, ResourceAction>
     */
    protected static function filteredActions(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        return collect(static::resourceActions())
            ->filter(fn (ResourceAction $action) => $action->authorise($user))
            ->toArray();
    }

    /**
     * Get the actions to be rendered into the datatable.
     *
     * @return array<string, array<string, string>>
     */
    protected static function datatableActions(): array
    {
        /** @var array<string, array<string, string>> */
        return collect(static::filteredActions())
            ->mapWithKeys(fn ($value) => [$value->actionId() => ['label' => $value->label()]])
            ->toArray();
    }

    /**
     * Perform an action after the triggering of an action.
     *
     * @param Request                                                                                                                                     $request
     * @param \Illuminate\Http\RedirectResponse|\HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse|\Illuminate\Routing\ResponseFactory $response
     *
     * @return \Illuminate\Http\RedirectResponse|\HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse|\Illuminate\Http\JsonResponse|ResponseFactory
     */
    protected function postActionTrigger(Request $request, RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory $response): RedirectResponse|MultiplePendingTurboStreamResponse|JsonResponse|ResponseFactory
    {
        return $request->wantsJson() ? response()->json([]) : $response;
    }

    /**
     * Extract the IDs from the action request.
     *
     * @param Request $request
     *
     * @return array<int, string|int>
     */
    protected function extractIds(Request $request): array
    {
        $extracted = collect();

        $request->collect()->each(function ($value, $key) use ($extracted) {
            if (preg_match("/\d+/", $key, $matches)) {
                $extracted->push($matches[0]);
            }
        });

        return $extracted->toArray();
    }

    /**
     * Handle the action that is triggered.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse|\Illuminate\Http\JsonResponse|ResponseFactory
     */
    protected function onAction(Request $request): RedirectResponse|MultiplePendingTurboStreamResponse|JsonResponse|ResponseFactory
    {
        $actions = collect(static::filteredActions())->keyBy(fn ($action) => $action->actionId());

        $request->validate(['action' => ['required', Rule::in($actions->keys())]]);

        /** @var ResourceAction $action */
        $action = $actions->get($request->get('action'));

        $response = $action->trigger($request, $this->extractIds($request));

        return $this->postActionTrigger($request, $response);
    }
}
