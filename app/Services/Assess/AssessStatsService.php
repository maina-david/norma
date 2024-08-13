<?php

namespace App\Services\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AssessStatsService
{
    /**
     * @param Builder $responsesQuery
     *
     * @return array<string, mixed>
     */
    public function getRiskMetricsForResponses(Builder $responsesQuery): array
    {
        $responsesCount = $responsesQuery->count();

        $default = $this->getDefaultMetrics();

        if ($responsesCount === 0) {
            return $default;
        }

        $notApplicableCount = $responsesQuery->clone()->notApplicable()->count();
        $notAssessedCount = $responsesQuery->clone()->notAssessed()->count();
        $noResponsesCount = $responsesQuery->clone()->answeredNo()->count();
        $yesResponsesCount = $responsesQuery->clone()->answeredYes()->count();
        $nonCompliantResponsesHighRiskCount = $responsesQuery->clone()
            ->answeredNo()
            ->forHighRisk()
            ->count();
        $nonCompliantResponsesMediumRiskCount = $responsesQuery->clone()
            ->answeredNo()
            ->forMediumRisk()
            ->count();
        $nonCompliantResponsesLowRiskCount = $responsesQuery->clone()
            ->answeredNo()
            ->forLowRisk()
            ->count();

        /** @var AssessmentActivity|null */
        $activity = AssessmentActivity::answerChange()
            ->whereRelation('assessmentItemResponse', function ($q) use ($responsesQuery) {
                $q->mergeConstraintsFrom($responsesQuery->clone());
            })->latest('created_at')->first();

        /** @var Carbon */
        $lastActivityDate = $activity->created_at ?? now()->subYears(20);
        $monthsSinceLastActivity = (int) $lastActivityDate->diffInMonths(Carbon::now(), true);

        return [
            'risk_rating' => $this->getRiskRating(
                $monthsSinceLastActivity,
                $responsesCount,
                $notAssessedCount,
                $noResponsesCount,
                $nonCompliantResponsesHighRiskCount,
                $nonCompliantResponsesMediumRiskCount,
            ),
            'total_count' => $responsesCount,
            'total_' . ResponseStatus::yes()->value => $yesResponsesCount,
            'total_' . ResponseStatus::no()->value => $noResponsesCount,
            'total_' . ResponseStatus::notAssessed()->value => $notAssessedCount,
            'total_' . ResponseStatus::notApplicable()->value => $notApplicableCount,
            'total_non_compliant_items_' . RiskRating::high()->value => $nonCompliantResponsesHighRiskCount,
            'total_non_compliant_items_' . RiskRating::medium()->value => $nonCompliantResponsesMediumRiskCount,
            'total_non_compliant_items_' . RiskRating::low()->value => $nonCompliantResponsesLowRiskCount,
            'percentage_non_compliant_items' => round($noResponsesCount / $responsesCount * 100),
            'last_answered' => $lastActivityDate,
        ];
    }

    /**
     * @param int $monthsSinceLastActivity
     * @param int $responsesCount
     * @param int $notAssessedCount
     * @param int $noResponsesCount
     * @param int $noResponsesHighRiskCount
     * @param int $noResponsesMediumRiskCount
     *
     * @return RiskRating
     */
    public function getRiskRating(
        int $monthsSinceLastActivity,
        int $responsesCount,
        int $notAssessedCount,
        int $noResponsesCount,
        int $noResponsesHighRiskCount,
        int $noResponsesMediumRiskCount,
    ): RiskRating {
        if ($responsesCount === 0) {
            return RiskRating::low();
        }
        $highNotAssessed = $notAssessedCount / $responsesCount * 100 >= 50;
        $highNoAnswers = $noResponsesCount / $responsesCount * 100 >= 40;
        $mediumNotAssessed = $notAssessedCount / $responsesCount * 100 > 0;
        $mediumNoAnswers = $noResponsesCount / $responsesCount * 100 >= 15;

        if ($highNotAssessed || $highNoAnswers || $monthsSinceLastActivity > 5 || $noResponsesHighRiskCount >= 1) {
            $riskRating = RiskRating::high();
        } elseif ($mediumNotAssessed || $mediumNoAnswers || $monthsSinceLastActivity > 2 || $noResponsesMediumRiskCount >= 5) {
            $riskRating = RiskRating::medium();
        } else {
            $riskRating = RiskRating::low();
        }

        return $riskRating;
    }

    /**
     * @param Builder $libryoQuery
     *
     * @return Builder
     */
    public function buildRiskRatingQuery(Builder $libryoQuery): Builder
    {
        $responsesTable = (new AssessmentItemResponse())->getTable();
        $libryoTable = (new Libryo())->getTable();
        $yes = ResponseStatus::yes()->value;
        $no = ResponseStatus::no()->value;
        $notAssessed = ResponseStatus::notAssessed()->value;
        $notApplicable = ResponseStatus::notApplicable()->value;
        $high = RiskRating::high()->value;
        $medium = RiskRating::medium()->value;

        $totalCount = 'select count(*)
            from `' . $responsesTable . '`
            where `place_id` = `' . $libryoTable . '`.id
                and `deleted_at` is NULL';

        $lastActivityDateQuery = AssessmentActivity::answerChange()->maxActivitySubquery($libryoTable . '.id');

        $baseQuery = AssessmentItemResponse::whereRaw('place_id = `' . $libryoTable . '`.id')
            ->notDraft()
            ->selectRaw('count(*)');
        $answeredNoQuery = $baseQuery->clone()->answeredNo();
        $answeredYesQuery = $baseQuery->clone()->answeredYes();
        $notApplicableQuery = $baseQuery->clone()->notApplicable();
        $notAssessedQuery = $baseQuery->clone()->notAssessed();

        $noResponseHighQuery = AssessmentItemResponse::answeredNo()
            ->forHighRisk()
            ->whereRaw('place_id = `' . $libryoTable . '`.id')
            ->selectRaw('count(*)');

        $noResponseMediumQuery = AssessmentItemResponse::answeredNo()
            ->forMediumRisk()
            ->whereRaw('place_id = `' . $libryoTable . '`.id')
            ->selectRaw('count(*)');

        $case = 'CASE
            #no responses
            WHEN `total_count` = 0 THEN ' . RiskRating::notRated()->value . '
            #high risk
            WHEN ((`count_by_status_' . $notAssessed . '` / `total_count` * 100 >= 50)
                    or (`count_by_status_' . $no . '` / `total_count` * 100 >= 40 )
                    or (TIMESTAMPDIFF(MONTH, `last_activity_date` , NOW()) > 5))
                    or (`total_non_compliant_items_' . $high . '` >= 1)
                THEN ' . $high . '
            #medium risk
            WHEN ((`count_by_status_' . $notAssessed . '` > 0)
                    or (`count_by_status_' . $no . '` / `total_count` * 100 >= 15)
                    or (TIMESTAMPDIFF(MONTH, `last_activity_date` , NOW()) > 2))
                    or (`total_non_compliant_items_' . $medium . '` >= 5)
                THEN ' . $medium . '
            #low risk
            ELSE ' . RiskRating::low()->value . '
            END';

        $query = $libryoQuery
            ->leftJoinSub(
                $libryoQuery->clone()
                    ->selectSub($totalCount, 'total_count')
                    ->selectRaw('(' . $notAssessedQuery->toSql() . ') as count_by_status_' . $notAssessed, $notAssessedQuery->getBindings())
                    ->selectRaw('(' . $notApplicableQuery->toSql() . ') as count_by_status_' . $notApplicable, $notApplicableQuery->getBindings())
                    ->selectRaw('(' . $answeredYesQuery->toSql() . ') as count_by_status_' . $yes, $answeredYesQuery->getBindings())
                    ->selectRaw('(' . $answeredNoQuery->toSql() . ') as count_by_status_' . $no, $answeredNoQuery->getBindings())
                    ->selectRaw('(' . $lastActivityDateQuery->toSql() . ') as last_activity_date', $lastActivityDateQuery->getBindings())
                    ->selectRaw('(' . $noResponseHighQuery->toSql() . ') as total_non_compliant_items_' . $high, $noResponseHighQuery->getBindings())
                    ->selectRaw('(' . $noResponseMediumQuery->toSql() . ') as total_non_compliant_items_' . $medium, $noResponseMediumQuery->getBindings())
                    ->addSelect('id'),
                'responsesCount',
                'responsesCount.id',
                '=',
                $libryoTable . '.id'
            )
            ->addSelect([
                '*',
                'total_count',
                'count_by_status_' . $notAssessed,
                'count_by_status_' . $yes,
                'count_by_status_' . $notApplicable,
                'count_by_status_' . $no,
                'last_activity_date',
                'total_non_compliant_items_' . $high,
                'total_non_compliant_items_' . $medium,
            ])
            ->selectSub($case, 'risk_rating')
            ->selectSub('ROUND(`count_by_status_' . $no . '` / `total_count` * 100)', 'percentage_non_compliant_items');

        /** @var Builder */
        return $query;
    }

    public function buildDetailedRiskRatingQuery(Builder $libryoQuery): Builder
    {
        $clonedLibryoQuery = $libryoQuery->clone();
        $query = $this->buildRiskRatingQuery($libryoQuery);
        $libryoTable = (new Libryo())->getTable();
        $yes = ResponseStatus::yes()->value;
        $no = ResponseStatus::no()->value;
        $notAssessed = ResponseStatus::notAssessed()->value;
        $notApplicable = ResponseStatus::notApplicable()->value;
        $high = RiskRating::high()->value;
        $medium = RiskRating::medium()->value;
        $low = RiskRating::low()->value;
        $notRated = RiskRating::notRated()->value;

        $baseQuery = AssessmentItemResponse::whereRaw('place_id = `' . $libryoTable . '`.id')
            ->notDraft()
            ->selectRaw('count(*)');
        $answeredNoHiQuery = $baseQuery->clone()->answeredNo()->forHighRisk();
        $answeredNoMedQuery = $baseQuery->clone()->answeredNo()->forMediumRisk();
        $answeredNoLoQuery = $baseQuery->clone()->answeredNo()->forLowRisk();
        $answeredNoNotRatedQuery = $baseQuery->clone()->answeredNo()->forNotRatedRisk();

        $answeredYesHiQuery = $baseQuery->clone()->answeredYes()->forHighRisk();
        $answeredYesMedQuery = $baseQuery->clone()->answeredYes()->forMediumRisk();
        $answeredYesLoQuery = $baseQuery->clone()->answeredYes()->forLowRisk();
        $answeredYesNotRatedQuery = $baseQuery->clone()->answeredYes()->forNotRatedRisk();

        $notApplicableHiQuery = $baseQuery->clone()->notApplicable()->forHighRisk();
        $notApplicableMedQuery = $baseQuery->clone()->notApplicable()->forMediumRisk();
        $notApplicableLoQuery = $baseQuery->clone()->notApplicable()->forLowRisk();
        $notApplicableNotRatedQuery = $baseQuery->clone()->notApplicable()->forNotRatedRisk();

        $notAssessedQueryHiQuery = $baseQuery->clone()->notAssessed()->forHighRisk();
        $notAssessedQueryMedQuery = $baseQuery->clone()->notAssessed()->forMediumRisk();
        $notAssessedQueryLoQuery = $baseQuery->clone()->notAssessed()->forLowRisk();
        $notAssessedQueryNotRatedQuery = $baseQuery->clone()->notAssessed()->forNotRatedRisk();

        $query->leftJoinSub(
            $clonedLibryoQuery
                ->selectRaw('(' . $answeredNoHiQuery->toSql() . ') as count_by_status_risk_' . $no . '_' . $high, $answeredNoHiQuery->getBindings())
                ->selectRaw('(' . $answeredNoMedQuery->toSql() . ') as count_by_status_risk_' . $no . '_' . $medium, $answeredNoMedQuery->getBindings())
                ->selectRaw('(' . $answeredNoLoQuery->toSql() . ') as count_by_status_risk_' . $no . '_' . $low, $answeredNoLoQuery->getBindings())
                ->selectRaw('(' . $answeredNoNotRatedQuery->toSql() . ') as count_by_status_risk_' . $no . '_' . $notRated, $answeredNoNotRatedQuery->getBindings())

                ->selectRaw('(' . $answeredYesHiQuery->toSql() . ') as count_by_status_risk_' . $yes . '_' . $high, $answeredYesHiQuery->getBindings())
                ->selectRaw('(' . $answeredYesMedQuery->toSql() . ') as count_by_status_risk_' . $yes . '_' . $medium, $answeredYesMedQuery->getBindings())
                ->selectRaw('(' . $answeredYesLoQuery->toSql() . ') as count_by_status_risk_' . $yes . '_' . $low, $answeredYesLoQuery->getBindings())
                ->selectRaw('(' . $answeredYesNotRatedQuery->toSql() . ') as count_by_status_risk_' . $yes . '_' . $notRated, $answeredYesNotRatedQuery->getBindings())

                ->selectRaw('(' . $notApplicableHiQuery->toSql() . ') as count_by_status_risk_' . $notApplicable . '_' . $high, $notApplicableHiQuery->getBindings())
                ->selectRaw('(' . $notApplicableMedQuery->toSql() . ') as count_by_status_risk_' . $notApplicable . '_' . $medium, $notApplicableMedQuery->getBindings())
                ->selectRaw('(' . $notApplicableLoQuery->toSql() . ') as count_by_status_risk_' . $notApplicable . '_' . $low, $notApplicableLoQuery->getBindings())
                ->selectRaw('(' . $notApplicableNotRatedQuery->toSql() . ') as count_by_status_risk_' . $notApplicable . '_' . $notRated, $notApplicableNotRatedQuery->getBindings())

                ->selectRaw('(' . $notAssessedQueryHiQuery->toSql() . ') as count_by_status_risk_' . $notAssessed . '_' . $high, $notAssessedQueryHiQuery->getBindings())
                ->selectRaw('(' . $notAssessedQueryMedQuery->toSql() . ') as count_by_status_risk_' . $notAssessed . '_' . $medium, $notAssessedQueryMedQuery->getBindings())
                ->selectRaw('(' . $notAssessedQueryLoQuery->toSql() . ') as count_by_status_risk_' . $notAssessed . '_' . $low, $notAssessedQueryLoQuery->getBindings())
                ->selectRaw('(' . $notAssessedQueryNotRatedQuery->toSql() . ') as count_by_status_risk_' . $notAssessed . '_' . $notRated, $notAssessedQueryNotRatedQuery->getBindings())
                ->addSelect('id'),
            'responsesRiskCount',
            'responsesRiskCount.id',
            '=',
            $libryoTable . '.id'
        );

        /** @var Builder */
        return $query;
    }

    /**
     * @param array<string,mixed> $responseData
     *
     * @return array<int|string, mixed>
     */
    public function getResponsesChartData(array $responseData): array
    {
        /** @var int */
        $responsesCount = $responseData['total_count'];
        if ($responsesCount === 0) {
            return [
                ResponseStatus::no()->value => 0,
                ResponseStatus::yes()->value => 0,
                ResponseStatus::notApplicable()->value => 0,
                ResponseStatus::notAssessed()->value => 0,
            ];
        }

        /** @var int */
        $notAssessedCount = $responseData['total_' . ResponseStatus::notAssessed()->value];
        /** @var int */
        $notApplicableCount = $responseData['total_' . ResponseStatus::notApplicable()->value];
        /** @var int */
        $noCount = $responseData['total_' . ResponseStatus::no()->value];
        /** @var int */
        $yesCount = $responseData['total_' . ResponseStatus::yes()->value];

        return [
            'title' => __('assess.metrics.current'),
            ResponseStatus::no()->value => $noCount,
            ResponseStatus::yes()->value => $yesCount,
            ResponseStatus::notApplicable()->value => $notApplicableCount,
            ResponseStatus::notAssessed()->value => $notAssessedCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultMetrics(): array
    {
        return [
            'risk_rating' => RiskRating::notRated(),
            'total_count' => 0,
            'total_' . ResponseStatus::yes()->value => 0,
            'total_' . ResponseStatus::no()->value => 0,
            'total_' . ResponseStatus::notAssessed()->value => 0,
            'total_' . ResponseStatus::notApplicable()->value => 0,
            'total_non_compliant_items_' . RiskRating::high()->value => 0,
            'total_non_compliant_items_' . RiskRating::medium()->value => 0,
            'percentage_non_compliant_items' => 0,
            'last_answered' => null,
        ];
    }
}
