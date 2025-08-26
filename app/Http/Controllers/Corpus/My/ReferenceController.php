<?php

namespace App\Http\Controllers\Corpus\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Corpus\ReferenceLinkType;
use App\Events\Auth\UserActivity\KnowYourLawActivity;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
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
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $norma = $organisation = null;
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $norma->load('location');
            /** @var Builder */
            $query = Reference::forNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();

            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            /** @var Builder */
            $query = Reference::forOrganisationUserAccess($organisation, $user);
            $query->withCount(['normas' => function ($q) use ($organisation, $user) {
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
            KnowYourLawActivity::dispatch($user, UserActivityType::fulltextSearchTerm(), ['term' => $search], $norma, $organisation);
        } else {
            /** @var Paginator */
            $items = $query->paginate($perPage);
        }

        /** @var View */
        return view('pages.corpus.reference.my.detailed-requirements', [
            'items' => $items->setPath(route('my.corpus.references.index'))->appends($request->query() ?? []),
            'pertaining' => $norma->title ?? $organisation->title ?? null,
        ]);
    }

    /**
     * @param Reference $reference
     *
     * @return View
     */
    public function show(Reference $reference): View
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);

        /** @var Norma $norma */
        $norma = $manager->getActive();

        $forNorma = fn ($query) => $query->forNormaLocations($norma);

        $reference = $reference->loadCount([
            'raisesConsequenceGroups' => $forNorma,
        ])
            ->load([
                'citation', 'refPlainText', 'summary', 'work', 'work.source',
                'categoriesForTagging' => fn ($query) => $query->forTagging()->select(['id', 'display_label']),
                'childReferences' => $forNorma,
                'childReferences.htmlContent',
                'htmlContent',
            ]);

        $amendments = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT))
            ->forNormaLocations($norma)
            ->count();

        $readWiths = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH))
            ->forNormaLocations($norma)
            ->count();

        $reference->load([
            'assessmentItems' => function ($builder) use ($norma) {
                $builder->whereRelation('assessmentResponses', 'place_id', $norma->id ?? 0);
            },
            'assessmentItems.assessmentResponses' => fn ($builder) => $builder->where('place_id', $norma->id ?? 0),
            'assessmentItems.legalDomain.topParent',
        ]);

        /** @var Norma|null $norma */
        if ($norma?->hasActionsModule()) {
            $reference->load([
                'actionAreas:id,title',
            ]);
        }

        $user = Collaborator::find(Auth::id());
        $canUpdateMetaData = $user && $user->isMySuperUser();

        /** @var View */
        return view('pages.corpus.reference.my.show', [
            'norma' => $norma,
            'reference' => $reference,
            'organisation' => $manager->getActiveOrganisation(),
            'readWithsCount' => $readWiths,
            'amendmentsCount' => $amendments,
            'canUpdateMetaData' => $canUpdateMetaData,
        ]);
    }
}
