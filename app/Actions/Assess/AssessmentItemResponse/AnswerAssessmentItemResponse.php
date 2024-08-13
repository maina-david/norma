<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Enums\Assess\ResponseStatus;
use App\Events\Assess\AssessmentActivity\AnswerChanged;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Services\HTMLPurifierService;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class AnswerAssessmentItemResponse
{
    use AsAction;

    /**
     * @param AssessmentItemResponse $response
     * @param array<string, mixed>   $input
     * @param User                   $user
     *
     * @return void
     */
    public function handle(AssessmentItemResponse $response, array $input, User $user): void
    {
        $fromAnswer = $response->answer;

        try {
            $answer = ResponseStatus::fromValue((int) $input['answer'])->value;
        } catch (Throwable $th) {
            abort(422);
        }

        // if it's a User Generated SAI, then use the previous next_due_at. If missing, use now
        $startDue = $response->next_due_at && $response->assessmentItem->organisation_id ? $response->next_due_at : now();

        $nextDueAt = $response->frequency ? $startDue->add($response->frequency_interval->name, $response->frequency) : null;

        $response->update([
            'answer' => $answer,
            'answered_at' => Carbon::now(),
            'last_answered_by' => $user->id,
            'next_due_at' => $nextDueAt,
        ]);

        /** @var HTMLPurifierService */
        $purifier = app(HTMLPurifierService::class);
        /** @var string */
        $notes = $input['notes'];
        $notes = $purifier->cleanComment($notes);

        /** @var \App\Models\Customer\Libryo $libryo */
        $libryo = $response->libryo;

        AnswerChanged::dispatch(
            $user,
            $libryo,
            $response,
            $fromAnswer,
            $answer,
            $notes,
        );
    }
}
