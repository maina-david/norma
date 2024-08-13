<?php

namespace App\Http\Controllers\Api\V2\Notify;

use App\Actions\Notify\LegalUpdate\UpdateReadUnderstoodStatus;
use App\Enums\Notify\LegalUpdateStatus;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Notify\LegalUpdate\V2\LegalUpdateResource;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalUpdateController extends AbstractApiController
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
        return LegalUpdateResource::class;
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
        /** @var User */
        $user = Auth::user();

        /** @var Builder */
        return LegalUpdate::whereHas('libryos', fn ($q) => $q->userHasAccess($user))
            ->with([
                'work' => fn ($q) => null,
                'libryos' => function ($q) use ($user) {
                    $q->active()->userHasAccess($user);
                },
            ]);
    }

    /**
     * @param LegalUpdate $update
     *
     * @return JsonResponse
     */
    public function markAsUnderstood(LegalUpdate $update): JsonResponse
    {
        /** @var User */
        $user = Auth::user();
        UpdateReadUnderstoodStatus::run($update, $user, LegalUpdateStatus::readUnderstood());

        return response()->json([]);
    }
}
