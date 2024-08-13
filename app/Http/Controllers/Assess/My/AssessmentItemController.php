<?php

namespace App\Http\Controllers\Assess\My;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\FilteredResource;
use App\Http\Controllers\Assess\My\Traits\AvailableActions;
use App\Http\Controllers\Assess\My\Traits\ComposesResponsesQuery;
use App\Http\Controllers\Assess\My\Traits\RedirectsAssessNotAvailable;
use App\Http\Controllers\Controller;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Assess\AssessStatsService;
use App\Services\Assess\QuarterlyReportsManager;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssessmentItemController extends Controller
{
    use ComposesResponsesQuery;
    use AvailableActions;
    use RedirectsAssessNotAvailable;

    /**
     * @param AssessStatsService      $assessStatsService
     * @param QuarterlyReportsManager $quarterlyReportsManager
     */
    public function __construct(
        protected AssessStatsService $assessStatsService,
        protected QuarterlyReportsManager $quarterlyReportsManager
    ) {
    }

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = $organisation = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            if ($response = $this->redirectIfNoAssess($libryo)) {
                return $response;
            }
            /** @var Builder */
            $query = AssessmentItemResponse::forLibryo($libryo);
            /** @var Builder */
            $libryosQuery = Libryo::whereKey($libryo->id);
        } else {
            /** @var Organisation $organisation */
            $organisation = $manager->getActiveOrganisation();

            /** @var Builder */
            $query = AssessmentItemResponse::forOrganisationUserAccess($organisation, $user);
            /** @var Builder */
            $libryosQuery = Libryo::forOrganisation($organisation->id)->userHasAccess($user);
        }

        $filters = $request->only(['domains', 'answered', 'answer', 'rating', 'controls', 'bookmarked', 'user-generated']);
        // @codeCoverageIgnoreStart
        if (!empty($filters)) {
            event(new FilteredResource(
                $user,
                UserActivityType::filteredPerformanceReport(),
                $filters,
                $libryo,
                $organisation
            ));
        }
        // @codeCoverageIgnoreEnd
        $query->filter($request->query());

        $riskData = $this->assessStatsService->getRiskMetricsForResponses($query);

        $responsesChartData = [
            'quarter_1' => $this->assessStatsService->getResponsesChartData($riskData),
            ...$this->quarterlyReportsManager->getResponseData($libryosQuery, $query)['chart_data'],
        ];

        /** @var RiskRating */
        $risk = $riskData['risk_rating'];

        $stats = [
            'rating' => ['label' => __('assess.risk_rating'), 'value' => RiskRating::lang()[$risk->value], 'color' => RiskRating::colors()[$risk->value]],
            'last_answered' => ['label' => __('assess.metrics.last_answered'), 'value' => $riskData['last_answered'], 'last_answered' => true],
            'total' => ['label' => __('assess.metrics.total_items'), 'value' => $riskData['total_count']],
            'not_answered' => ['label' => __('assess.metrics.total_' . ResponseStatus::notAssessed()->value), 'value' => $riskData['total_' . ResponseStatus::notAssessed()->value]],
            'not_applicable' => ['label' => __('assess.metrics.total_' . ResponseStatus::notApplicable()->value), 'value' => $riskData['total_' . ResponseStatus::notApplicable()->value]],
            'yes' => ['label' => __('assess.metrics.total_' . ResponseStatus::yes()->value), 'value' => $riskData['total_' . ResponseStatus::yes()->value]],
            'no' => ['label' => __('assess.metrics.total_' . ResponseStatus::no()->value), 'value' => $riskData['total_' . ResponseStatus::no()->value]],
            'high' => ['label' => __('assess.risk_rating_descriptions.labels.non_compliant_items_by_rating_' . RiskRating::high()->value), 'value' => $riskData['total_non_compliant_items_' . RiskRating::high()->value]],
            'medium' => ['label' => __('assess.risk_rating_descriptions.labels.non_compliant_items_by_rating_' . RiskRating::medium()->value), 'value' => $riskData['total_non_compliant_items_' . RiskRating::medium()->value]],
            'percentage' => ['label' => __('assess.risk_rating_descriptions.labels.non_compliant_items'), 'value' => ($riskData['percentage_non_compliant_items'] ?? '') . '%'],
        ];

        /** @var array<string, string> $requestQuery */
        $requestQuery = $request->query();

        $isFiltered = !empty(array_intersect(array_keys($requestQuery), ['domains', 'rating', 'answered', 'controls', 'answer']));

        /** @var View */
        return view('pages.assess.my.dashboard', [
            'risk' => $risk,
            'stats' => $stats,
            'responsesChartData' => $responsesChartData,
            'noResponses' => $risk->is(RiskRating::notRated()),
            'isSingleMode' => $manager->isSingleMode(),
            'subTitle' => !is_null($libryo) ? $libryo->title : ($organisation->title ?? ''),
            'libryo' => $libryo,
            'isFiltered' => $isFiltered,
        ]);
    }

    /**
     * @param Request        $request
     * @param AssessmentItem $assessmentItem
     *
     * @return View|RedirectResponse
     */
    public function show(Request $request, AssessmentItem $assessmentItem): View|RedirectResponse
    {
        $fields = [
            'response_' . ResponseStatus::yes()->value,
            'response_' . ResponseStatus::no()->value,
            'response_' . ResponseStatus::notApplicable()->value,
            'response_' . ResponseStatus::notAssessed()->value,
            'mark_unchanged',
            'last_answered',
            'answered_by',
        ];

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = $organisation = null;

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            if ($response = $this->redirectIfNoAssess($libryo)) {
                return $response;
            }
            /** @var Builder */
            $query = AssessmentItemResponse::forLibryo($libryo);
            array_unshift($fields, 'title_single');
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var Builder */
            $query = AssessmentItemResponse::forOrganisationUserAccess($organisation, $user);
            array_unshift($fields, 'title_with_libryo');
        }
        $query->forAssessmentItem($assessmentItem);

        $baseQuery = $this->composeResponsesQuery($query, $libryo, $organisation, $user);

        /** @var View */
        return view('pages.assess.my.assessment-item.show', [
            'assessmentItem' => $assessmentItem,
            'baseQuery' => $baseQuery,
            'responsesCount' => $baseQuery->count(),
            'tableFields' => $fields,
            'subTitle' => !is_null($libryo) ? $libryo->title : $organisation?->title,
            'isSingleMode' => $manager->isSingleMode(),
            'actions' => $this->getAvailableActions(),
        ]);
    }
}
