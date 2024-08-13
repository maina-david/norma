<?php

namespace App\Stores\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class AssessmentItemStore
{
    use AttachesDetaches;

    /**
     * Attach the context questions.
     *
     * @param AssessmentItem                  $item
     * @param Collection<ContextQuestion|int> $questions
     *
     * @return void
     */
    public function attachContextQuestions(AssessmentItem $item, Collection $questions): void
    {
        $this->attachRelations($item, 'contextQuestions', $questions);
    }

    /**
     * Detach the context questions.
     *
     * @param AssessmentItem                  $item
     * @param Collection<ContextQuestion|int> $questions
     *
     * @return void
     */
    public function detachContextQuestions(AssessmentItem $item, Collection $questions): void
    {
        $this->detachRelations($item, 'contextQuestions', $questions);
    }
}
