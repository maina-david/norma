<?php

namespace App\Observers\Assess;

use App\Actions\Assess\AssessmentItemResponse\AnswerAssessmentItemResponse;
use App\Enums\Assess\AssessmentItemType;
use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;

class AssessmentItemObserver
{
    /**
     * Listen to the creating event.
     *
     * @param \App\Models\Assess\AssessmentItem $item
     *
     * @return void
     */
    public function creating(AssessmentItem $item): void
    {
        $default = $item->type ?? AssessmentItemType::LEGACY->value;

        $item->type = $item->organisation_id ? AssessmentItemType::USER_DEFINED->value : $default;
    }

    /**
     * Listen to the deleted event.
     *
     * @param \App\Models\Assess\AssessmentItem $item
     *
     * @return void
     */
    public function deleted(AssessmentItem $item): void
    {
        $payload = [
            'answer' => ResponseStatus::notApplicable()->value,
            'notes' => __('assess.assessment_item.deleted_reason'),
        ];

        $item->assessmentResponses()
            ->with(['norma'])
            ->whereHas('norma')
            ->chunkById(1000, function ($chunk) use ($item, $payload) {
                $chunk->each(function (AssessmentItemResponse $response) use ($item, $payload) {
                    $response->setRelation('assessmentItem', $item);

                    AnswerAssessmentItemResponse::run($response, $payload, new User());
                });
            });
    }
}
