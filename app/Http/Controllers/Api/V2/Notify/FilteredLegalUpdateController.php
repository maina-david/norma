<?php

namespace App\Http\Controllers\Api\V2\Notify;

use App\Enums\Notify\LegalUpdateStatus;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Notify\LegalUpdate\V2\FilteredLegalUpdateResource;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class FilteredLegalUpdateController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return LegalUpdate::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return FilteredLegalUpdateResource::class;
    }

    /**
     * Get all notifications for the current user for the given normas.
     *
     * @param ApiMultiGetRequest $request
     *
     * @return ResourceCollection<LegalUpdate>
     */
    public function indexFiltered(Request $request): ResourceCollection
    {
        $unreadOnly = (bool) $request->input('unread', false);
        $normaIds = $request->input('places', []);
        $normaIds = is_string($normaIds) ? explode(',', $normaIds) : $normaIds;
        /** @var User $user */
        $user = Auth::user();

        $query = LegalUpdate::forNormas($normaIds)
            ->when($unreadOnly, fn ($q) => $q->userStatus($user, LegalUpdateStatus::unread()))
            ->with([
                'users' => fn ($q) => $q->whereKey($user->id),
                'work',
                'legalDomains',
                'primaryLocation',
            ])
            ->orderByRaw('COALESCE(release_at,created_at) DESC');

        /** @var ResourceCollection<LegalUpdate> */
        return parent::index($request, $query);
    }
}
