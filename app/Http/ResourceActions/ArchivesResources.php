<?php

namespace App\Http\ResourceActions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

trait ArchivesResources
{
    /**
     * The resource base query to be used when archiving.
     *
     * @return Builder
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('workflows.task.archive');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'archive';
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
        $this->baseQuery()
            ->whereKey($resourceIds)
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        Session::flash('flash.message', __('workflows.task.successfully_archived'));

        return back();
    }
}
