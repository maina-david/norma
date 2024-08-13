<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Enums\Assess\ResponseStatus;
use App\Events\Auth\UserActivity\AssessmentItems\FilteredAssessmentItems;
use App\Events\Auth\UserActivity\AssessmentItems\SearchedAssessmentItems;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait UsesAssessmentResponseViews
{
    use DecodesHashids;
    use ComposesResponsesQuery;
    use RedirectsAssessNotAvailable;
    use AvailableActions;

    public function indexView(Request $request, bool $draft): View|RedirectResponse
    {
        $fields = [
            'response_' . ResponseStatus::yes()->value,
            'response_' . ResponseStatus::no()->value,
            'response_' . ResponseStatus::notApplicable()->value,
            'response_' . ResponseStatus::notAssessed()->value,
            'mark_unchanged',
            'answered',
            'review',
        ];

        /** @var User $user */
        $user = $request->user();

        $bookmarksFilter = fn ($q) => $q->forUser($user);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = $organisation = null;
        $singleMode = false;
        $pendingCount = 0;

        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();

            if ($response = $this->redirectIfNoAssess($libryo)) {
                return $response;
            }
            /** @var Builder */
            $query = AssessmentItemResponse::forLibryo($libryo)
                ->when($draft, fn ($builder) => $builder->draft())
                ->when(!$draft, fn ($builder) => $builder->notDraft());

            $query->when(is_null($request->get('answer')), function ($builder) {
                $builder->where('answer', '!=', ResponseStatus::notApplicable()->value);
            });

            $pendingCount = AssessmentItemResponse::forLibryo($libryo)->draft()->count();

            // ordering makes query quite slow for all streams mode
            // $aiTable = (new AssessmentItem())->getTable();
            // $responseTable = (new AssessmentItemResponse())->getTable();
            // $query->select(['*'])
            //    ->selectSub(sprintf("select CONCAT_WS(' ', %s.component_1, %s.component_2, %s.component_3, %s.component_4, %s.component_5, %s.component_6, %s.component_7, %s.component_8) from {$aiTable} where {$aiTable}.id = {$responseTable}.assessment_item_id", $aiTable, $aiTable, $aiTable, $aiTable, $aiTable, $aiTable, $aiTable, $aiTable), 'description')
            //    ->orderBy('description', 'ASC');
            array_unshift($fields, 'title_single');
            $singleMode = true;
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();

            $baseQuery = $this->composeResponsesQuery($query, $libryo, $organisation, $user);
            $baseQuery->with([
                'assessmentItem.author:id,fname,sname',
                'assessmentItem.bookmarks' => $bookmarksFilter,
            ]);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $forResponsesCallback = function ($q) use ($draft, $organisation, $request) {
                /** @var \App\Models\Auth\User $user */
                $user = $request->user();

                $q->forOrganisationUserAccess($organisation, $user)
                    ->when($draft, fn ($builder) => $builder->draft())
                    ->when(!$draft, fn ($builder) => $builder->notDraft());
            };
            /** @var Builder */
            $baseQuery = AssessmentItem::whereHas('assessmentResponses', $forResponsesCallback)
                ->filter($request->all())
                ->with(['legalDomain.topParent', 'author:id,fname,sname', 'bookmarks' => $bookmarksFilter])
                ->withCount(['assessmentResponses' => $forResponsesCallback]);
        }

        $filters = $request->only(['domains', 'answer', 'answered']);

        if ($request->has('search')) {
            event(new SearchedAssessmentItems($user, $manager->getActive(), $manager->getActiveOrganisation()));
        }

        if (!empty($filters)) {
            event(new FilteredAssessmentItems($filters, $user, $manager->getActive(), $manager->getActiveOrganisation()));
        }

        /** @var View */
        return view('pages.assess.my.assessment-item-response.index', [
            'draft' => $draft,
            'pendingCount' => $pendingCount,
            'baseQuery' => $baseQuery,
            'subTitle' => !is_null($libryo) ? $libryo->title : $organisation?->title,
            'tableFields' => $fields,
            'actions' => $this->getAvailableActions(),
            'singleMode' => $singleMode,
            'filtersApplied' => !empty($filters) || !empty($request->get('search', '')),
        ]);
    }
}
