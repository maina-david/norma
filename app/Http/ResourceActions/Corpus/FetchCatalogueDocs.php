<?php

namespace App\Http\ResourceActions\Corpus;

use App\Actions\Arachno\AddCatalogueDocsToFrontier;
use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Corpus\CatalogueDoc;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FetchCatalogueDocs implements ResourceAction
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
        return $user && $user->can('collaborate.corpus.catalogue-doc.fetch');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string $label */
        $label = __('corpus.catalogue_doc.fetch_from_source');

        return $label;
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'fetch';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse
    {
        $docIds = CatalogueDoc::whereKey($resourceIds)
            // don't fetch if still busy fetching
            ->whereNull('fetch_started_at')
            ->whereRelation('crawler', fn ($q) => $q->where('can_fetch', true))
            ->pluck('id')
            ->all();
        app(AddCatalogueDocsToFrontier::class)->handle($docIds);

        Session::flash('flash.message', __('corpus.catalogue_doc.scheduled_for_fetch'));

        return back();
    }
}
