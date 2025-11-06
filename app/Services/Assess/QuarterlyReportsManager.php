<?php

namespace App\Services\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessSnapshot;
use App\Models\Assess\Pivots\AssessSnapshotAssessmentItemResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class QuarterlyReportsManager
{
    public function __construct(protected AssessStatsService $assessStatsService)
    {
    }

    /**
     * @param Builder $normasQuery
     * @param Builder $responsesQuery
     *
     * @return array<string, mixed>
     */
    public function getResponseData(Builder $normasQuery, Builder $responsesQuery): array
    {
        $chartData = [];
        $tableData = [];
        $rawData = [];

        $startOfQuarter = Carbon::now()->startOfMonth()->subQuarter()->startOfQuarter();
        $endOfQuarter = Carbon::now()->startOfMonth()->subQuarter()->endOfQuarter();

        $count = 2;
        while ($count <= 4) {
            /** @var Collection<AssessSnapshot> */
            $snapshots = AssessSnapshot::whereHas('norma', fn ($q) => $q->mergeConstraintsFrom($normasQuery->clone()))
                ->forMonth($endOfQuarter)
                ->with([
                    'assessmentItemResponses' => fn ($q) => $q->select('id')->mergeConstraintsFrom($responsesQuery->clone()),
                ])
                ->get();

            if ($snapshots->isEmpty()) {
                // @codeCoverageIgnoreStart
                $startOfQuarter = $startOfQuarter->startOfMonth()->subQuarter()->startOfQuarter();
                $endOfQuarter = $endOfQuarter->startOfMonth()->subQuarter()->endOfQuarter();
                $count++;
                continue;
                // @codeCoverageIgnoreEnd
            }

            $monthsSinceLastActivity = (int) $snapshots->max('last_answered')->diffInMonths(Carbon::now(), true);

            $values = [
                ResponseStatus::no()->value => 0,
                ResponseStatus::yes()->value => 0,
                ResponseStatus::notApplicable()->value => 0,
                ResponseStatus::notAssessed()->value => 0,
                'total_non_compliant_items_' . RiskRating::high()->value => 0,
                'total_non_compliant_items_' . RiskRating::medium()->value => 0,
                'total_non_compliant_items_' . RiskRating::low()->value => 0,
                'total_non_compliant_items_' . RiskRating::notRated()->value => 0,
            ];
            foreach ($snapshots as $snapshot) {
                foreach ($snapshot->assessmentItemResponses as $response) {
                    /** @var AssessSnapshotAssessmentItemResponse */
                    $pivot = $response->pivot;
                    if (ResponseStatus::draft()->is($pivot->answer)) {
                        // @codeCoverageIgnoreStart
                        continue;
                        // @codeCoverageIgnoreEnd
                    }
                    $values[$pivot->answer] = $values[$pivot->answer] + 1;

                    if (ResponseStatus::no()->is($pivot->answer)) {
                        switch ($pivot->risk_rating) {
                            case RiskRating::high()->value:
                                $values['total_non_compliant_items_' . RiskRating::high()->value] = $values['total_non_compliant_items_' . RiskRating::high()->value] + 1;
                                break;
                            case RiskRating::medium()->value:
                                $values['total_non_compliant_items_' . RiskRating::medium()->value] = $values['total_non_compliant_items_' . RiskRating::medium()->value] + 1;
                                break;
                            case RiskRating::low()->value:
                                $values['total_non_compliant_items_' . RiskRating::low()->value] = $values['total_non_compliant_items_' . RiskRating::low()->value] + 1;
                                break;
                            default:
                                $values['total_non_compliant_items_' . RiskRating::notRated()->value] = $values['total_non_compliant_items_' . RiskRating::notRated()->value] + 1;
                                break;
                        }
                    }
                }
            }

            $total = $values[ResponseStatus::no()->value] + $values[ResponseStatus::yes()->value] + $values[ResponseStatus::notApplicable()->value] + $values[ResponseStatus::notAssessed()->value];

            $chartData['quarter_' . $count] = [
                'title' => $startOfQuarter->format('M, Y') . ' - ' . $endOfQuarter->format('M, Y'),
                ResponseStatus::yes()->value => $values[ResponseStatus::yes()->value],
                ResponseStatus::no()->value => $values[ResponseStatus::no()->value],
                ResponseStatus::notApplicable()->value => $values[ResponseStatus::notApplicable()->value],
                ResponseStatus::notAssessed()->value => $values[ResponseStatus::notAssessed()->value],
            ];

            $tableData['quarter_' . $count] = [
                'title' => $startOfQuarter->format('M, Y') . ' - ' . $endOfQuarter->format('M, Y'),
                'total' => $total,
                ResponseStatus::no()->value => $values[ResponseStatus::no()->value],
                ResponseStatus::yes()->value => $values[ResponseStatus::yes()->value],
                ResponseStatus::notApplicable()->value => $values[ResponseStatus::notApplicable()->value],
                ResponseStatus::notAssessed()->value => $values[ResponseStatus::notAssessed()->value],
                'risk_rating' => $this->assessStatsService->getRiskRating(
                    $monthsSinceLastActivity,
                    $total,
                    $values[ResponseStatus::notAssessed()->value],
                    $values[ResponseStatus::no()->value],
                    $values['total_non_compliant_items_' . RiskRating::high()->value],
                    $values['total_non_compliant_items_' . RiskRating::medium()->value],
                ),
            ];

            $rawData['quarter_' . $count] = [
                'total_non_compliant_items_' . RiskRating::high()->value => $values['total_non_compliant_items_' . RiskRating::high()->value],
                'total_non_compliant_items_' . RiskRating::medium()->value => $values['total_non_compliant_items_' . RiskRating::medium()->value],
                'total_non_compliant_items_' . RiskRating::low()->value => $values['total_non_compliant_items_' . RiskRating::low()->value],
                'total_non_compliant_items_' . RiskRating::notRated()->value => $values['total_non_compliant_items_' . RiskRating::notRated()->value],
            ] + $tableData['quarter_' . $count];

            $startOfQuarter = $startOfQuarter->startOfMonth()->subQuarter()->startOfQuarter();
            $endOfQuarter = $endOfQuarter->startOfMonth()->subQuarter()->endOfQuarter();
            $count++;
        }

        return ['chart_data' => $chartData, 'table_data' => $tableData, 'raw' => $rawData];
    }
}
