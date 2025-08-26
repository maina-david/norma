<?php

namespace App\Http\Controllers\Api\V2\Notify;

use App\Events\Auth\UserActivity\LegalUpdates\LegalUpdatesFiltered;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Notify\LegalUpdate\V2\ForNormasLegalUpdateResource;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LegalUpdateForNormasController extends AbstractApiController
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
        return ForNormasLegalUpdateResource::class;
    }

    /**
     * Get base query for model.
     *
     * @param ApiMultiGetRequest $request
     *
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        $normaIds = $request->input('normas', []);
        $startDate = $request->input('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        /** @var User $user */
        $user = Auth::user();
        $normaIds = Norma::userHasAccess($user)
            ->whereKey($normaIds)
            ->get(['id', 'title', 'organisation_id']);

        $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $filters = $request->getFiltersFromQueryParams();

        $applied = [...$filters, ...$request->only(['start_date', 'end_date'])];

        if (!empty($applied)) {
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            foreach ($normaIds as $norma) {
                $org = new Organisation();
                $org->id = $norma->organisation_id; // @phpstan-ignore-line

                event(new LegalUpdatesFiltered($filters, $user, $norma, $org));
            }
        }

        $normaIds = $normaIds->pluck('id')->all();

        /** @var Builder */
        return LegalUpdate::forNormas($normaIds)
            ->sentBetween(Carbon::parse($startDate), Carbon::parse($endDate))
            ->with([
                'work' => fn ($q) => null,
                'normas' => function ($q) use ($normaIds) {
                    $q->whereKey($normaIds);
                    $q->select(['id']);
                },
            ]);
    }
}
