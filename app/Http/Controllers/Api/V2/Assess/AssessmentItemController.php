<?php

namespace App\Http\Controllers\Api\V2\Assess;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Assess\AssessmentItem\V2\AssessmentItemResource;
use App\Models\Assess\AssessmentItem;
use App\Models\Customer\Norma;
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
        /** @var Norma */
        $norma = $request->route('norma');

        /** @var Builder $query */
        $query = AssessmentItem::whereHas('assessmentResponses', function ($q) use ($norma) {
            $q->forNorma($norma);
        });

        /** @var string */
        $includes = $request->query('include', '');
        if (Str::contains($includes, ['basicCitations', 'references'])) {
            $query->with([
                'references' => function ($q) use ($norma) {
                    $q->forNorma($norma);
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
     * @param Norma             $norma
     *
     * @return ResourceCollection<AssessmentItem>
     */
    public function indexForNorma(Request $request, Norma $norma): ResourceCollection
    {
        /** @var ResourceCollection<AssessmentItem> */
        return parent::index($request);
    }

    /**
     * @param Request        $request
     * @param AssessmentItem $assessmentItem
     * @param Norma         $norma
     *
     * @return AssessmentItemResource
     */
    public function showForNorma(
        Request $request,
        AssessmentItem $assessmentItem,
        Norma $norma
    ): AssessmentItemResource {
        /** @var AssessmentItemResource */
        return parent::show($request, (string) $assessmentItem->id);
    }
}
