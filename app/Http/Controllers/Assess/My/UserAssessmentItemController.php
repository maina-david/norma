<?php

namespace App\Http\Controllers\Assess\My;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Ontology\CategoryType;
use App\Events\Auth\UserActivity\AssessmentItems\CreatedAssessmentItem;
use App\Http\Controllers\Assess\My\Traits\RedirectsAssessNotAvailable;
use App\Http\Controllers\Assess\My\Traits\UsesAssessmentResponseViews;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Controllers\Traits\PerformsActions;
use App\Http\Requests\Assess\UserAssessmentItemRequest;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveLibryosManager;
use App\Stores\Assess\AssessmentItemResponseStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class UserAssessmentItemController extends Controller
{
    use DecodesHashids;
    use PerformsActions;
    use RedirectsAssessNotAvailable;
    use UsesAssessmentResponseViews;

    /**
     * Get the tree.
     *
     * @param \App\Models\Ontology\LegalDomain $parent
     *
     * @return array<array-key, array<string, int|string>>
     */
    protected function extractChildren(LegalDomain $parent): array
    {
        $children = [];

        foreach ($parent->children as $child) {
            $child->title = "{$parent->title} | {$child->title}";
            $children[] = ['id' => $child->id, 'title' => $child->title];
            $children = array_merge($children, $this->extractChildren($child));
        }

        return $children;
    }

    /**
     * @param \App\Models\Customer\Organisation $organisation
     * @param \App\Models\Customer\Libryo|null  $libryo
     *
     * @return array<string, string>
     */
    public function getApplicableDomains(Organisation $organisation, ?Libryo $libryo): array
    {
        /** @var Collection<LegalDomain> $domains */
        $domains = LegalDomain::with(['children.children.children'])
            ->whereHas('libryos', function ($builder) use ($organisation, $libryo) {
                $builder->when($libryo, fn ($query) => $query->whereKey($libryo->id ?? null))
                    ->when(!$libryo, fn ($query) => $query->where('organisation_id', $organisation->id));
            })
            ->get(['title', 'id']);

        $mapped = [];

        foreach ($domains as $domain) {
            $mapped[] = ['id' => $domain->id, 'title' => $domain->title];
            $mapped = array_merge($mapped, $this->extractChildren($domain));
        }

        $mapped = collect($mapped)->sortBy('title')->pluck('title', 'id')->all();

        $mapped[''] = '-';

        return $mapped;
    }

    /**
     * Get the available control topics.
     *
     * @codeCoverageIgnore
     *
     * @return array<string, string>
     */
    public function getControlTopics(): array
    {
        /** @var string $key */
        $key = config('cache-keys.ontology.category.type-control.key');
        /** @var int $expiry */
        $expiry = config('cache-keys.ontology.category.type-control.expiry');

        return Cache::remember($key, now()->addMinutes($expiry), function () {
            $categories = Category::where('category_type_id', CategoryType::CONTROL)
                ->noParent()
                ->with('descendantsUnordered:id,parent_id,display_label')
                ->get(['id', 'parent_id', 'display_label']);

            $types = ['' => ''];

            foreach ($categories as $category) {
                $types[$category->id] = $category->display_label;
                $category->descendantsUnordered->push($category);

                foreach ($category->descendantsUnordered as $child) {
                    $types[$child->id] = $this->generateLabel($category->descendantsUnordered, $child);
                }
            }

            /** @var array<string, string> */
            return collect($types)->sort()->toArray();
        });
    }

    /**
     * Generate the control topic label.
     *
     * @codeCoverageIgnore
     *
     * @param \Illuminate\Support\Collection $children
     * @param \App\Models\Ontology\Category  $item
     *
     * @return string
     */
    protected function generateLabel(Collection $children, Category $item): string
    {
        if (!$item->parent_id || !$parent = $children->where('id', $item->parent_id)->first()) {
            return $item->display_label;
        }

        $parentLabel = $this->generateLabel($children, $parent);

        return "{$parentLabel} | {$item->display_label}";
    }

    /**
     * Get a listing of draft user assessment items.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        return $this->indexView($request, true);
    }

    /**
     * Show the create form.
     *
     * @return View
     */
    public function create(): View
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();

        /** @var View */
        return view('pages.assess.my.assessment-item.form', [
            'controlTopics' => $this->getControlTopics(),
            'legalDomains' => $this->getApplicableDomains($organisation, $libryo),
        ]);
    }

    /**
     * Create a new user assessment item.
     *
     * @param UserAssessmentItemRequest $request
     *
     * @return RedirectResponse
     */
    public function store(UserAssessmentItemRequest $request): RedirectResponse
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();
        $libryo?->load('organisation');

        $data = $request->validated();
        $data['organisation_id'] = $libryo->organisation_id ?? $organisation->id;
        $data['author_id'] = Auth::id();

        /** @var AssessmentItem $item */
        $item = AssessmentItem::create($data);

        $store = app(AssessmentItemResponseStore::class);

        $responses = $store->createDraftResponsesForOrganisation($item, $libryo->organisation ?? $organisation);

        $this->notifyGeneralSuccess();

        /** @var User $user */
        $user = Auth::user();

        event(new CreatedAssessmentItem($item, $user, $libryo, $organisation));

        if ($libryo && $response = $responses->get($libryo->id)) {
            return redirect()->route('my.assess.assessment-item-responses.answer', ['response' => $response]);
        }

        return redirect()->route('my.assess.assessment-item.show', ['assessmentItem' => $item->id]);
    }

    /**
     * Edit the assessment item.
     *
     * @param string $assess
     *
     * @return View
     */
    public function edit(string $assess): View
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();

        $assess = $this->decodeHash($assess, AssessmentItem::class);

        /** @var AssessmentItem $assess */
        abort_unless($assess->organisation_id == $organisation->id, 404);

        /** @var View */
        return view('pages.assess.my.assessment-item.form', [
            'controlTopics' => $this->getControlTopics(),
            'legalDomains' => $this->getApplicableDomains($organisation, $libryo),
            'resource' => $assess,
        ]);
    }

    /**
     * Update the assessment item.
     *
     * @param UserAssessmentItemRequest $request
     * @param string                    $assess
     *
     * @return RedirectResponse
     */
    public function update(UserAssessmentItemRequest $request, string $assess): RedirectResponse
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();

        $assess = $this->decodeHash($assess, AssessmentItem::class);

        /** @var AssessmentItem $assess */
        abort_unless($assess->organisation_id == $organisation->id, 404);

        $data = $request->validated();

        /** @var AssessmentItem $assess */
        $assess->update($data);

        $this->notifySuccessfulUpdate();

        return $this->redirectToPage($assess, $libryo);
    }

    /**
     * Delete the assessment item.
     *
     * @param string $assess
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $assess): RedirectResponse
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();

        $assess = $this->decodeHash($assess, AssessmentItem::class);

        /** @var AssessmentItem $assess */
        abort_unless($assess->organisation_id == $organisation->id, 404);

        if (!$assess->assessmentResponses()->where('answer', '!=', ResponseStatus::draft()->value)->exists()) {
            $assess->delete();
        }

        return $this->redirectToPage($assess, $libryo);
    }

    /**
     * Redirect to the correct page.
     *
     * @param \App\Models\Assess\AssessmentItem $assess
     * @param \App\Models\Customer\Libryo|null  $libryo
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToPage(AssessmentItem $assess, ?Libryo $libryo): RedirectResponse
    {
        /** @var AssessmentItemResponse|null $response */
        $response = AssessmentItemResponse::where('place_id', $libryo->id ?? null)->where('assessment_item_id', $assess->id)->first();

        if ($libryo && $response) {
            return redirect()->route('my.assess.assessment-item-responses.answer', ['response' => $response]);
        }

        return redirect()->route('my.assess.assessment-item.show', ['assessmentItem' => $assess->id]);
    }
}
