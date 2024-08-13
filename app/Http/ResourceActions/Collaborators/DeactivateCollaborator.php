<?php

namespace App\Http\ResourceActions\Collaborators;

use App\Contracts\Http\ResourceAction;
use App\Events\Collaborators\CollaboratorDeactivated;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DeactivateCollaborator implements ResourceAction
{
    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.collaborators.collaborator.deactivate');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string $label */
        $label = __('collaborators.deactivate');

        return $label;
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'deactivate';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse
    {
        Collaborator::whereKey($resourceIds)
            ->get()
            ->each(function (Collaborator $collaborator) {
                $collaborator->active = false;
                $collaborator->save();
                CollaboratorDeactivated::dispatch($collaborator);
            });

        Session::flash('flash.message', __('collaborators.successfully_deactivated'));

        return back();
    }
}
