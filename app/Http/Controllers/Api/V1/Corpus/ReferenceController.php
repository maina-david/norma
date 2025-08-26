<?php

namespace App\Http\Controllers\Api\V1\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Reference\V1\ReferenceCollection;
use App\Http\Resources\Corpus\Reference\V1\ReferenceResource;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferenceController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return Reference::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return ReferenceResource::class;
    }

    /**
     * Get the allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        // @codeCoverageIgnoreStart
        return [
            'summary',
            'tags',
            'raisesConsequenceGroups',
            'raisesConsequenceGroups.consequences',
        ];
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param ApiMultiGetRequest $request
     * @param Work               $work
     * @param Norma             $norma
     *
     * @return ReferenceCollection<Reference>
     */
    public function forNormaForWork(Request $request, Work $work, Norma $norma): ReferenceCollection
    {
        /** @var User */
        $user = Auth::user();

        abort_unless($user->hasNormaAccess($norma), 404);

        /** @var Builder */
        $query = Reference::where('work_id', $work->id)
            ->forNorma($norma)
            ->with(['summary', 'tags', 'citation', 'htmlContent:reference_id,cached_content', 'refPlainText']);

        $results = $this->processListingQuery($request, $query);

        $resource = new ReferenceCollection($results);

        return $resource->preserveQuery();
    }
}
