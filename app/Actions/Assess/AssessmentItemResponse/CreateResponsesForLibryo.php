<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Assess\AssessmentItem;
use App\Models\Customer\Libryo;
use App\Stores\Assess\AssessmentItemResponseStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateResponsesForLibryo
{
    use AsAction;

    public function __construct(protected AssessmentItemResponseStore $assessmentItemResponseStore)
    {
    }

    public function handle(Libryo $libryo): void
    {
        if (!$libryo->hasAssessModule()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        /** @var Collection<AssessmentItem> */
        $assessItems = AssessmentItem::possibleForLibryo($libryo)
            ->noResponsesForLibryo($libryo)
            ->get(['id']);
        $this->assessmentItemResponseStore->createResponsesForItems($assessItems, $libryo);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function asJob(Libryo $libryo): void
    {
        $this->handle($libryo);
    }
}
