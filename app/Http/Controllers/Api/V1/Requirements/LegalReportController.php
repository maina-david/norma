<?php

namespace App\Http\Controllers\Api\V1\Requirements;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Work\V1\LegalReportWorkCollection;
use App\Http\Resources\Corpus\Work\V1\LegalReportWorkResource;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
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
     * @param Libryo             $libryo
     *
     * @return LegalReportWorkCollection<Work>
     */
    public function legalReport(Request $request, Libryo $libryo): LegalReportWorkCollection
    {
        /** @var User */
        $user = Auth::user();

        abort_unless($user->hasLibryoAccess($libryo), 404);

        /** @var Builder */
        $query = Work::primaryForLibryo($libryo)
            ->withRelationsForLibryo($libryo);

        $results = $this->processListingQuery($request, $query);

        $resource = new LegalReportWorkCollection($results);

        return $resource->preserveQuery();
    }
}
