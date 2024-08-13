<?php

namespace App\Contracts\Http;

use App\Models\Auth\User;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

interface ResourceAction
{
    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool;

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string;

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string;

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory;
}
