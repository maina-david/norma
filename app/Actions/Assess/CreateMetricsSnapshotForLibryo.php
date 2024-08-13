<?php

namespace App\Actions\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\AssessSnapshot;
use App\Models\Customer\Libryo;
use App\Services\Assess\AssessStatsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class CreateMetricsSnapshotForLibryo
{
    use AsAction;

    public function __construct(protected AssessStatsService $assessStatsService)
    {
    }

    /**
     * @param Libryo                   $libryo
     * @param Carbon                   $forDate
     * @param array<string,mixed>|null $data
     *
     * @return AssessSnapshot
     */
    public function handle(Libryo $libryo, Carbon $forDate, ?array $data = null): AssessSnapshot
    {
        /** @var Builder */
        $query = AssessmentItemResponse::forLibryo($libryo);
        $m = is_null($data) ? $this->assessStatsService->getRiskMetricsForResponses($query) : $data;

        /** @var AssessSnapshot */
        $snapshot = AssessSnapshot::firstOrCreate([
            'place_id' => $libryo->id,
            'month_date' => $forDate,
        ], [
            'place_id' => $libryo->id,
            'month_date' => $forDate,
            'risk_rating' => $m['risk_rating'],
            'last_answered' => $m['last_answered'],
            'total' => $m['total_count'],
            'total_' . ResponseStatus::yes()->value => $m['total_' . ResponseStatus::yes()->value],
            'total_' . ResponseStatus::no()->value => $m['total_' . ResponseStatus::no()->value],
            'total_' . ResponseStatus::notAssessed()->value => $m['total_' . ResponseStatus::notAssessed()->value],
            'total_' . ResponseStatus::notApplicable()->value => $m['total_' . ResponseStatus::notApplicable()->value],
            'total_non_compliant_items_' . RiskRating::high()->value => $m['total_non_compliant_items_' . RiskRating::high()->value],
            'total_non_compliant_items_' . RiskRating::medium()->value => $m['total_non_compliant_items_' . RiskRating::medium()->value],
            'total_non_compliant_items_' . RiskRating::low()->value => $m['total_non_compliant_items_' . RiskRating::low()->value],
            'non_compliant_percentage' => $m['percentage_non_compliant_items'],
        ]);

        $libryo->load(['assessmentItemResponses.assessmentItem']);
        foreach ($libryo->assessmentItemResponses as $response) {
            try {
                $snapshot->assessmentItemResponses()
                    ->attach($response->id, [
                        'answer' => $response->answer,
                        'risk_rating' => $response->getRiskRating(),
                    ]);
                // @codeCoverageIgnoreStart
            } catch (Throwable $th) {
            }
            // @codeCoverageIgnoreEnd
        }

        return $snapshot;
    }

    /**
     * @param int    $libryoId
     * @param string $forDate  Date string for the end of the month that this snapshot is for
     *
     * @return void
     */
    public function asJob(int $libryoId, string $forDate): void
    {
        /** @var Libryo */
        $libryo = Libryo::findOrFail($libryoId);
        $forDate = Carbon::parse($forDate);
        $this->handle($libryo, $forDate);
    }
}
