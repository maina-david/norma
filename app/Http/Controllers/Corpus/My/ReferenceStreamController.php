<?php

namespace App\Http\Controllers\Corpus\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Corpus\ReferenceLinkType;
use App\Events\Auth\UserActivity\KnowYourLawActivity;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsLibryoAndOrganisation;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Tag;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReferenceStreamController extends Controller
{
    use GetsLibryoAndOrganisation;

    /**
     * @param Request $request
     * @param Work    $work
     *
     * @return Response
     */
    public function showYourRequirements(Request $request, Work $work): Response
    {
        $manager = app(ActiveLibryosManager::class);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $libryo->load('location');
            $query = $work->references()->forLibryo($libryo);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $query = $work->references()->forOrganisationUserAccess($organisation, $user);
        }

        $query->active()
            ->filter($request->only(['tags', 'domain', 'jurisdictionType']))
            ->with(['citation', 'refPlainText']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.list',
            'target' => 'corpus-references-for-work-' . $work->id,
            'work' => $work,
            'references' => $query->paginate(100),
            'baseQuery' => $query,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Reference $reference
     *
     * @return Response
     */
    public function show(Reference $reference): Response
    {
        [$libryo, $organisation] = $this->getActiveLibryoAndOrganisation();

        $reference->loadCount(['raisesConsequenceGroups' => fn ($query) => $query->forLibryoLocations($libryo)])
            ->load([
                'citation', 'refPlainText', 'summary', 'tags', 'work',
                'childReferences.htmlContent', 'htmlContent',
            ]);

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

        $amendments = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT))
            ->forLibryoLocations($libryo)
            ->count();

        $readWiths = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH))
            ->forLibryoLocations($libryo)
            ->count();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.show',
            'target' => 'reference-details-' . $reference->id,
            'reference' => $reference,
            'organisation' => $organisation,
            'amendmentsCount' => $amendments,
            'readWithsCount' => $readWiths,
            'libryo' => $libryo,
        ]);

        /** @var User $user */
        $user = Auth::user();
        KnowYourLawActivity::dispatch($user, UserActivityType::viewedReference(), ['id' => $reference->id], $libryo, $organisation);

        return turboStreamResponse($view);
    }

    /**
     * @param AssessmentItemResponse $aiResponse
     *
     * @return Response
     */
    public function indexForAssessmentItemResponse(AssessmentItemResponse $aiResponse): Response
    {
        $aiResponse->load(['assessmentItem', 'libryo']);
        /** @var \App\Models\Customer\Libryo $libryo */
        $libryo = $aiResponse->libryo;
        $query = $aiResponse->assessmentItem
            ->references()
            ->active()
            ->forActiveWork()
            ->forLibryo($libryo)
            ->orderBy('work_id')
            ->orderedPosition()
            ->with(['citation', 'refPlainText', 'legalDomains', 'locations', 'work']);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.for-related',
            'target' => 'requirements-for-assessment-item-response-' . $aiResponse->id,
            'baseQuery' => $query,
            'empty' => $query->count() === 0,
            'route' => route('my.references.for.assessment-item-response.index', ['aiResponse' => $aiResponse]),
            'response' => $aiResponse,
            'editable' => $aiResponse->assessmentItem->organisation_id === $manager->getActiveOrganisation()->id,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     * @param string  $key     To distinguish between different frames if there are multiple frames on the same page
     *
     * @return Response
     */
    public function searchSuggest(Request $request, string $key): Response
    {
        /** @var string */
        $search = $request->input('search', '');

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var User */
        $user = Auth::user();
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $tag = Tag::forLibryo($libryo)->inRandomOrder()->first();
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $tag = Tag::forOrganisationUserAccess($organisation, $user)->inRandomOrder()->first();
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.search-suggest-all',
            'target' => 'corpus-reference-search-suggest-' . $key,
            'search' => $search,
            'tag' => $tag,
            'linkTarget' => $request->input('target'),
            'linkToDetailed' => $key === 'detailed-requirements-page' ? false : true,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Reference $reference
     *
     * @return Response
     */
    public function showFullText(Reference $reference): Response
    {
        $reference->load(['htmlContent', 'childReferences.refPlainText']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.show-content',
            'target' => 'full-text-content',
            'reference' => $reference,
        ]);

        return turboStreamResponse($view);
    }
}
