<?php

namespace App\Stores\Assess;

use App\Enums\Assess\AssessActivityType;
use App\Enums\Assess\ResponseStatus;
use App\Events\Assess\AssessmentActivity\AnswerChanged;
use App\Events\Assess\AssessmentActivity\AssessmentActivityEvent;
use App\Events\Assess\AssessmentActivity\LeftNote;
use App\Events\Assess\AssessmentActivity\RequirementLinked;
use App\Events\Assess\AssessmentActivity\UploadedDocument;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItemResponse;

class AssessmentActivityStore
{
    public function createFromEvent(AssessmentActivityEvent $event): AssessmentActivity
    {
        $data = [
            'assessment_item_response_id' => $event->getResponse()->id,
            'place_id' => $event->getLibryo()?->id ?? null,
            'organisation_id' => $event->getOrganisation()?->id ?? null,
            'user_id' => $event->getUser()->id,
            'activity_type' => $event->getActivityType(),
        ];

        switch ($event->getActivityType()->value) {
            case AssessActivityType::answerChange()->value:
            default:
                /** @var AnswerChanged $event */
                $data['from_status'] = $event->getFromAnswer();
                $data['to_status'] = $event->getToAnswer();
                $data['notes'] = $event->getActivityNotes();
                break;
            case AssessActivityType::comment()->value:
                /** @var LeftNote $event */
                $data['comment_id'] = $event->getNote()->id;
                break;
            case AssessActivityType::fileUpload()->value:
                /** @var UploadedDocument $event */
                $data['file_id'] = $event->getDocument()->id;
                break;
            case AssessActivityType::requirementUnlinked()->value:
            case AssessActivityType::requirementLinked()->value:
                /** @var RequirementLinked $event */
                $data['reference_id'] = $event->getReferenceId();
                break;
        }

        /** @var AssessmentActivity */
        return AssessmentActivity::create($data);
    }

    /**
     * @param AssessmentItemResponse                $response
     * @param int|null                              $userId
     * @param \App\Enums\Assess\ResponseStatus|null $toStatus
     *
     * @return AssessmentActivity
     */
    public function createInitialForResponse(AssessmentItemResponse $response, ?int $userId = null, ?ResponseStatus $toStatus = null): AssessmentActivity
    {
        $data = [
            'assessment_item_response_id' => $response->id,
            'place_id' => $response->place_id,
            'activity_type' => AssessActivityType::responseAdded()->value,
            'to_status' => ($toStatus ?? ResponseStatus::notAssessed())->value,
            'created_at' => $response->created_at,
            'user_id' => $userId,
        ];

        /** @var AssessmentActivity */
        return AssessmentActivity::create($data);
    }
}
