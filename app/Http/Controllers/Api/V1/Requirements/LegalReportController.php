<?php

namespace App\Http\Controllers\Api\V1\Requirements;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Work\V1\LegalReportWorkCollection;
use App\Http\Resources\Corpus\Work\V1\LegalReportWorkResource;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * @param ApiMultiGetRequest $request
     * @param Norma             $norma
     *
     * @return LegalReportWorkCollection<Work>
     */
    public function legalReport(Request $request, Norma $norma): LegalReportWorkCollection
    {
        /** @var User */
        $user = Auth::user();

        abort_unless($user->hasNormaAccess($norma), 404);

        /** @var Builder */
        $query = Work::primaryForNorma($norma)
            ->withRelationsForNorma($norma);

        $results = $this->processListingQuery($request, $query);

        $resource = new LegalReportWorkCollection($results);

        return $resource->preserveQuery();
    }
}
