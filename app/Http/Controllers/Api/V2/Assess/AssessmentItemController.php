<?php

namespace App\Http\Controllers\Api\V2\Assess;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Assess\AssessmentItem\V2\AssessmentItemResource;
use App\Models\Assess\AssessmentItem;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class AssessmentItemController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return AssessmentItem::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return AssessmentItemResource::class;
    }

    /**
     * Get the allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return [
            'legalDomain',
        ];
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

        /** @var Builder $query */
        $query = AssessmentItem::whereHas('assessmentResponses', function ($q) use ($libryo) {
            $q->forLibryo($libryo);
        });

        /** @var string */
        $includes = $request->query('include', '');
        if (Str::contains($includes, ['basicCitations', 'references'])) {
            $query->with([
                'references' => function ($q) use ($libryo) {
                    $q->forLibryo($libryo);
                },
                'references.citation' => fn () => null,
                'references.refPlainText' => fn () => null,
                'references.work' => fn () => null,
            ]);
        }

        return $query;
    }

    /**
     * @param ApiMultiGetRequest $request
     * @param Libryo             $libryo
     *
     * @return ResourceCollection<AssessmentItem>
     */
    public function indexForLibryo(Request $request, Libryo $libryo): ResourceCollection
    {
        /** @var ResourceCollection<AssessmentItem> */
        return parent::index($request);
    }

    /**
     * @param Request        $request
     * @param AssessmentItem $assessmentItem
     * @param Libryo         $libryo
     *
     * @return AssessmentItemResource
     */
    public function showForLibryo(
        Request $request,
        AssessmentItem $assessmentItem,
        Libryo $libryo
    ): AssessmentItemResource {
        /** @var AssessmentItemResource */
        return parent::show($request, (string) $assessmentItem->id);
    }
}
