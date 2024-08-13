<?php

namespace App\Http\Controllers\Api\V2\Notify;

use App\Events\Auth\UserActivity\LegalUpdates\LegalUpdatesFiltered;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Notify\LegalUpdate\V2\ForLibryosLegalUpdateResource;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LegalUpdateForLibryosController extends AbstractApiController
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
        return ForLibryosLegalUpdateResource::class;
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
        $libryoIds = $request->input('libryos', []);
        $startDate = $request->input('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        /** @var User $user */
        $user = Auth::user();
        $libryoIds = Libryo::userHasAccess($user)
            ->whereKey($libryoIds)
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
            foreach ($libryoIds as $libryo) {
                $org = new Organisation();
                $org->id = $libryo->organisation_id; // @phpstan-ignore-line

                event(new LegalUpdatesFiltered($filters, $user, $libryo, $org));
            }
        }

        $libryoIds = $libryoIds->pluck('id')->all();

        /** @var Builder */
        return LegalUpdate::forLibryos($libryoIds)
            ->sentBetween(Carbon::parse($startDate), Carbon::parse($endDate))
            ->with([
                'work' => fn ($q) => null,
                'libryos' => function ($q) use ($libryoIds) {
                    $q->whereKey($libryoIds);
                    $q->select(['id']);
                },
            ]);
    }
}
