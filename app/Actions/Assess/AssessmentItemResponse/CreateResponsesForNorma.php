<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Assess\AssessmentItem;
use App\Models\Customer\Norma;
use App\Stores\Assess\AssessmentItemResponseStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateResponsesForNorma
{
    use AsAction;

    public function __construct(protected AssessmentItemResponseStore $assessmentItemResponseStore)
    {
    }

    public function handle(Norma $norma): void
    {
        if (!$norma->hasAssessModule()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        /** @var Collection<AssessmentItem> */
        $assessItems = AssessmentItem::possibleForNorma($norma)
            ->noResponsesForNorma($norma)
            ->get(['id']);
        $this->assessmentItemResponseStore->createResponsesForItems($assessItems, $norma);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function asJob(Norma $norma): void
    {
        $this->handle($norma);
    }
}
