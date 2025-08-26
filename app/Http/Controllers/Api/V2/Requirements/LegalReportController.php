<?php

namespace App\Http\Controllers\Api\V2\Requirements;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Work\V2\LegalReportWorkResource;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
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
        /** @var Norma */
        $norma = $request->route('norma');

        /** @var Builder */
        return Work::primaryForNorma($norma)
            ->withRelationsForNorma($norma);
    }

    /**
     * @param ApiMultiGetRequest $request
     * @param Norma             $norma
     *
     * @return ResourceCollection<Work>
     */
    public function legalReport(Request $request, Norma $norma): ResourceCollection
    {
        /** @var User */
        $user = Auth::user();

        abort_unless($user->hasNormaAccess($norma), 404);

        /** @var ResourceCollection<Work> */
        return parent::index($request);
    }
}
