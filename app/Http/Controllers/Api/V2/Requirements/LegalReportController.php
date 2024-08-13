<?php

namespace App\Http\Controllers\Api\V2\Requirements;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Work\V2\LegalReportWorkResource;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class LegalReportController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return Work::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return LegalReportWorkResource::class;
    }

    /**
     * Get base query for model.
     *
     * @param Request $request
     *
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        /** @var Libryo */
        $libryo = $request->route('libryo');

        /** @var Builder */
        return Work::primaryForLibryo($libryo)
            ->withRelationsForLibryo($libryo);
    }

    /**
     * @param ApiMultiGetRequest $request
     * @param Libryo             $libryo
     *
     * @return ResourceCollection<Work>
     */
    public function legalReport(Request $request, Libryo $libryo): ResourceCollection
    {
        /** @var User */
        $user = Auth::user();

        abort_unless($user->hasLibryoAccess($libryo), 404);

        /** @var ResourceCollection<Work> */
        return parent::index($request);
    }
}
