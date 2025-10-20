<?php

namespace App\Console\Commands\Once;

use App\Actions\Assess\CreateMetricsSnapshotForNorma;
use App\Enums\Assess\AssessActivityType;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use App\Services\Assess\AssessStatsService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * @codeCoverageIgnore
 */
class CreateHistoricAssessSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:assess-create-historic-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A once off script to create old quarterly snapshots';

    /**
     * @return int
     */
    public function handle(): int
    {
        Norma::active()->has('assessmentItemResponses')
            ->chunk(3, function ($normas) {
                foreach ($normas as $norma) {
                    $this->info((string) $norma->id);
                    $this->createHistoricSnapshots($norma);
                }
            });

        return 0;
    }

    /**
     * @param Norma $norma
     *
     * @return void
     */
    public function createHistoricSnapshots(Norma $norma): void
    {
        $startOfQuarter = Carbon::now()->startOfMonth()->subQuarter()->startOfQuarter();
        $endOfQuarter = Carbon::now()->startOfMonth()->subQuarter()->endOfQuarter();

        $count = 1;
        while ($count <= 3) {
            /** @var Collection<AssessmentItemResponse> */
            $responses = AssessmentItemResponse::forNorma($norma)->with([
                'assessmentItem' => fn ($q) => $q->select(['id', 'risk_rating']),
                'activities' => function ($q) use ($endOfQuarter) {
                    $q->where('activity_type', AssessActivityType::answerChange()->value)
                        ->where('created_at', '<=', $endOfQuarter)
                        ->latest();
                },
            ])->get();

            foreach ($responses as $response) {
                $latest = $response->activities->first()?->to_status;

                $response->answer = is_null($latest) ? ResponseStatus::notAssessed()->value : $latest;
            }

            $responsesCount = $responses->count();
            $noResponses = $responses->where('answer', ResponseStatus::no()->value);
            $noOrNotAssessedResponses = $responses
                ->whereIn('answer', [ResponseStatus::no()->value, ResponseStatus::notAssessed()->value]);

            $notAssessedCount = $responses->where('answer', ResponseStatus::notAssessed()->value)->count();
            $notApplicableCount = $responses->where('answer', ResponseStatus::notApplicable()->value)->count();
            $noCount = $noResponses->count();
            $yesCount = $responses->where('answer', ResponseStatus::yes()->value)->count();

            $noHighRiskNoItemsCount = 0;
            $noMediumRiskNoItemsCount = 0;
            $noLowRiskNoItemsCount = 0;
            foreach ($noOrNotAssessedResponses as $response) {
                if ($response->assessmentItem->risk_rating === RiskRating::high()->value) {
                    $noHighRiskNoItemsCount++;
                }
                if ($response->assessmentItem->risk_rating === RiskRating::medium()->value) {
                    $noMediumRiskNoItemsCount++;
                }
                if ($response->assessmentItem->risk_rating === RiskRating::low()->value) {
                    $noLowRiskNoItemsCount++;
                }
            }

            $lastActivity = $this->getLastActivityDateQuarterly($norma, $endOfQuarter);
            /** @var int $monthsSinceLastActivity */
            $monthsSinceLastActivity = $lastActivity ? (int) $lastActivity->created_at?->diffInMonths(Carbon::now(), true) : 240;

            $data = [
                'risk_rating' => app(AssessStatsService::class)->getRiskRating(
                    $monthsSinceLastActivity,
                    $responsesCount,
                    $notAssessedCount,
                    $noCount,
                    $noHighRiskNoItemsCount,
                    $noMediumRiskNoItemsCount,
                ),
                'last_answered' => now()->subMonths(240),
                'total_count' => $responsesCount,
                'total_' . ResponseStatus::yes()->value => $yesCount,
                'total_' . ResponseStatus::no()->value => $noCount,
                'total_' . ResponseStatus::notAssessed()->value => $notAssessedCount,
                'total_' . ResponseStatus::notApplicable()->value => $notApplicableCount,
                'total_non_compliant_items_' . RiskRating::high()->value => $noHighRiskNoItemsCount,
                'total_non_compliant_items_' . RiskRating::medium()->value => $noMediumRiskNoItemsCount,
                'total_non_compliant_items_' . RiskRating::low()->value => $noLowRiskNoItemsCount,
                'percentage_non_compliant_items' => $responsesCount > 0 ? round($noOrNotAssessedResponses->count() / $responsesCount * 100, 2) : 0.0,
            ];

            CreateMetricsSnapshotForNorma::run($norma, $endOfQuarter, $data);

            $startOfQuarter = $startOfQuarter->startOfMonth()->subQuarter()->startOfQuarter();
            $endOfQuarter = $endOfQuarter->startOfMonth()->subQuarter()->endOfQuarter();
            $count++;
        }
    }

    /**
     * @param Norma $norma
     * @param Carbon $endOfQuarter
     *
     * @return AssessmentActivity|null
     */
    public function getLastActivityDateQuarterly(Norma $norma, Carbon $endOfQuarter): ?AssessmentActivity
    {
        /** @var AssessmentActivity|null */
        return AssessmentActivity::whereHas('assessmentItemResponse', function ($q) use ($norma) {
            $q->forNorma($norma);
        })
            ->where('activity_type', AssessActivityType::answerChange()->value)
            ->where('created_at', '<=', $endOfQuarter)
            ->latest()
            ->select(['created_at'])
            ->first();
    }
}
