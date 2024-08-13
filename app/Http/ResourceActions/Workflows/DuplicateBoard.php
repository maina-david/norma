<?php

namespace App\Http\ResourceActions\Workflows;

use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Workflows\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DuplicateBoard implements ResourceAction
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
        return $user && $user->can('collaborate.workflows.board.duplicate');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('workflows.board.duplicate');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'duplicate';
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
        Board::with(['taskTypes:id'])
            ->whereKey($resourceIds)
            ->get()
            ->each(function (Board $board) {
                $copy = $board->replicate();
                $copy->title = "{$copy->title} - Copy";
                $copy->save();

                $copy->taskTypes()->attach($board->taskTypes->pluck('id')->all());
            });

        Session::flash('flash.message', __('workflows.board.successfully_duplicated'));

        return back();
    }
}
