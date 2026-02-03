<?php

namespace App\Stores\Assess;

use App\Enums\Assess\ReassessInterval;
use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AssessmentItemResponseStore
{
    public function __construct(protected AssessmentActivityStore $assessmentActivityStore)
    {
    }

    /**
     * Creates the initial not assessed responses for the given assessment items for the given stream.
     *
     * @param Collection<AssessmentItem> $items
     * @param Norma                     $norma
     *
     * @return void
     */
    public function createResponsesForItems(Collection $items, Norma $norma): void
    {
        foreach ($items as $item) {
            $this->createResponseForItem($item, $norma);
        }
    }

    /**
     * @param AssessmentItem $item
     * @param Norma         $norma
     *
     * @return AssessmentItemResponse
     */
    public function createResponseForItem(AssessmentItem $item, Norma $norma): AssessmentItemResponse
    {
        /** @var AssessmentItemResponse|null */
        $response = AssessmentItemResponse::where('assessment_item_id', $item->id)
            ->where('place_id', $norma->id)
            ->with(['assessmentItem'])
            ->first();
        if (!is_null($response)) {
            // @codeCoverageIgnoreStart
            return $response;
            // @codeCoverageIgnoreEnd
        }

        $months = config('assess.default_start_due_offset.' . $item->risk_rating);
        $defaultDate = $months ? now()->addMonths($months) : null;
        $nextDueAt = $item->start_due_offset ? now()->addMonths($item->start_due_offset) : $defaultDate;

        /** @var AssessmentItemResponse|null $response */
        $response = AssessmentItemResponse::where('place_id', $norma->id)
            ->where('assessment_item_id', $item->id)
            ->first();

        /**
         * Prevent the duplication of assessment item responses by first checking if previous ones exist.
         */
        // @codeCoverageIgnoreStart
        if ($response) {
            return $response;
        }
        // @codeCoverageIgnoreEnd

        /** @var AssessmentItemResponse */
        $response = AssessmentItemResponse::create([
            'assessment_item_id' => $item->id,
            'place_id' => $norma->id,
            'answer' => ResponseStatus::notAssessed()->value,
            'next_due_at' => $nextDueAt,
            'frequency' => $item->frequency,
            'frequency_interval' => $item->frequency_interval ?? ReassessInterval::MONTH->value,
        ]);

        // populate activity log with initial not assessed status change - activity log is used for metrics
        $this->assessmentActivityStore->createInitialForResponse($response);

        return $response;
    }

    /**
     * Create draft responses and return a collection keyed by the norma ID and the value is the hashed ID of the response.
     *
     * @param AssessmentItem $item
     * @param Organisation   $organisation
     *
     * @return Collection
     */
    public function createDraftResponsesForOrganisation(AssessmentItem $item, Organisation $organisation): Collection
    {
        /** @var int|null $user */
        $user = Auth::id();

        return $organisation->normas()
            ->get(['id', 'organisation_id'])
            ->keyBy('id')
            ->map(function ($norma) use ($user, $item) {
                /** @var Norma $norma */
                /** @var AssessmentItemResponse $response */
                $response = AssessmentItemResponse::create([
                    'assessment_item_id' => $item->id,
                    'place_id' => $norma->id,
                    'answer' => ResponseStatus::draft()->value,
                    'frequency' => $item->frequency,
                    'frequency_interval' => $item->frequency_interval,
                ]);

                $this->assessmentActivityStore->createInitialForResponse($response, $user, ResponseStatus::draft());

                return $response->hash_id;
            });
    }

    /**
     * @param Collection<AssessmentItemResponse> $responses
     * @param Norma                             $norma
     *
     * @return void
     */
    public function removeResponses(Collection $responses, Norma $norma): void
    {
        $responses->filter(function ($r) use ($norma) {
            /** @var AssessmentItemResponse $r */
            return $r->place_id === $norma->id;
        })
            ->each(fn ($response) => $response->delete());
    }
}
