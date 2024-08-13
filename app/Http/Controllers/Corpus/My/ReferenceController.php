<?php

namespace App\Http\Controllers\Corpus\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Corpus\ReferenceLinkType;
use App\Events\Auth\UserActivity\KnowYourLawActivity;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Search\Elastic\DocumentSearch;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferenceController extends Controller
{
    /**
     * @param DocumentSearch $documentSearch
     */
    public function __construct(protected DocumentSearch $documentSearch)
    {
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function indexDetailedRequirements(Request $request): View
    {
        $filters = $request->except(['search']);
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = $organisation = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $libryo->load('location');
            /** @var Builder */
            $query = Reference::forLibryo($libryo);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();

            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            /** @var Builder */
            $query = Reference::forOrganisationUserAccess($organisation, $user);
            $query->withCount(['libryos' => function ($q) use ($organisation, $user) {
                $q->forOrganisation($organisation->id)->userHasAccess($user);
            }]);
        }

        $query->typeCitation()
            ->active()
            ->forActiveWork()
            ->filter($filters)
            ->with(['citation', 'refPlainText', 'work', 'locations', 'legalDomains', 'tags']);
        $perPage = 50;

        if (!in_array('search', $filters)) {
            $query->orderBy('work_id')
                ->orderedPosition();
        }

        /** @var string|null */
        $search = $request->input('search');
        if (!empty($search)) {
            /** @var User $user */
            $user = Auth::user();
            $page = (int) $request->input('page', 1);
            /** @var Paginator */
            $items = $this->documentSearch->getReferencesForRequirementsSearch($search, $query, $page, $perPage);
            KnowYourLawActivity::dispatch($user, UserActivityType::fulltextSearchTerm(), ['term' => $search], $libryo, $organisation);
        } else {
            /** @var Paginator */
            $items = $query->paginate($perPage);
        }

        /** @var View */
        return view('pages.corpus.reference.my.detailed-requirements', [
            'items' => $items->setPath(route('my.corpus.references.index'))->appends($request->query() ?? []),
            'pertaining' => $libryo->title ?? $organisation->title ?? null,
        ]);
    }

    /**
     * @param Reference $reference
     *
     * @return View
     */
    public function show(Reference $reference): View
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);

        /** @var Libryo $libryo */
        $libryo = $manager->getActive();

        $forLibryo = fn ($query) => $query->forLibryoLocations($libryo);

        $reference = $reference->loadCount([
            'raisesConsequenceGroups' => $forLibryo,
        ])
            ->load([
                'citation', 'refPlainText', 'summary', 'work', 'work.source',
                'categoriesForTagging' => fn ($query) => $query->forTagging()->select(['id', 'display_label']),
                'childReferences' => $forLibryo,
                'childReferences.htmlContent',
                'htmlContent',
            ]);

        $amendments = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT))
            ->forLibryoLocations($libryo)
            ->count();

        $readWiths = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH))
            ->forLibryoLocations($libryo)
            ->count();

        $reference->load([
            'assessmentItems' => function ($builder) use ($libryo) {
                $builder->whereRelation('assessmentResponses', 'place_id', $libryo->id ?? 0);
            },
            'assessmentItems.assessmentResponses' => fn ($builder) => $builder->where('place_id', $libryo->id ?? 0),
            'assessmentItems.legalDomain.topParent',
        ]);

        /** @var Libryo|null $libryo */
        if ($libryo?->hasActionsModule()) {
            $reference->load([
                'actionAreas:id,title',
            ]);
        }

        $user = Collaborator::find(Auth::id());
        $canUpdateMetaData = $user && $user->isMySuperUser();

        /** @var View */
        return view('pages.corpus.reference.my.show', [
            'libryo' => $libryo,
            'reference' => $reference,
            'organisation' => $manager->getActiveOrganisation(),
            'readWithsCount' => $readWiths,
            'amendmentsCount' => $amendments,
            'canUpdateMetaData' => $canUpdateMetaData,
        ]);
    }
}
