<?php

namespace App\Http\ResourceActions\Corpus;

use App\Actions\Corpus\CatalogueWork\SyncToWork;
use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Corpus\CatalogueWork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SyncCatalogueToWork implements ResourceAction
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
        return $user && $user->can('collaborate.corpus.catalogue-work.sync');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string $label */
        $label = __('corpus.catalogue_work.sync');

        return $label;
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'sync';
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
        CatalogueWork::whereKey($resourceIds)
            ->get(['id'])
            ->each(function (CatalogueWork $catalogueWork) {
                SyncToWork::dispatch($catalogueWork->id);
            });

        Session::flash('flash.message', __('corpus.catalogue_work.scheduled_for_sync'));

        return back();
    }
}
