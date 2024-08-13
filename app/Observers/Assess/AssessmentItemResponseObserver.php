<?php

namespace App\Observers\Assess;

use App\Actions\Assess\AssessmentItemResponse\HandleAssessmentItemResponseSaving;
use App\Models\Assess\AssessmentItemResponse;

class AssessmentItemResponseObserver
{
    /**
     * Handle the AssessmentItemResponse "saving" event.
     *
     * @param \App\Models\Assess\AssessmentItemResponse $assessmentItemResponse
     *
     * @return void
     */
    public function saving(AssessmentItemResponse $assessmentItemResponse): void
    {
        // only assign the user when the item is being updated, not created.
        HandleAssessmentItemResponseSaving::run($assessmentItemResponse);
    }
}
