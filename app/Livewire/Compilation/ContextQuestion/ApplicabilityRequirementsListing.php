<?php

namespace App\Livewire\Compilation\ContextQuestion;

use App\Actions\Compilation\IncludeExcludeFromLibryo;
use App\Enums\Compilation\ApplicabilityNoteType;
use App\Enums\Corpus\WorkStatus;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\CompiledLibryoReference;
use App\Models\Customer\Pivots\LibryoReference;
use App\Models\Customer\Pivots\LibryoSpecificWork;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Geonames\UsesLibryoOrOrganisationLocationsAndDomains;
use App\Traits\Livewire\UsesPlaceholder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ApplicabilityRequirementsListing extends Component
{
    use UsesLibryoOrOrganisationLocationsAndDomains;
    use WithPagination;
    use UsesPlaceholder;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $filters;

    /** @var int */
    public int $libryoId;

    #[Url]
    public string $search = '';

    #[Url]
    public int $page = 1;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Geonames\Location>|null */
    public ?Collection $locations = null;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Ontology\LegalDomain>|null */
    public ?Collection $domains = null;

    /** @var \Illuminate\Support\Collection<int, Libryo>|null */
    public ?Collection $libryos = null;

    /**
     * {@inheritDoc}
     */
    protected function placeholderRows(): int
    {
        return 7;
    }

    /**
     * Handle on mount.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->setPage($this->page);
    }

    /**
     * Update the page variable.
     *
     * @param int    $page
     * @param string $pageName
     *
     * @return void
     */
    protected function updatedPaginators(int $page, string $pageName): void
    {
        $this->page = $page;
    }

    /**
     * @return void
     */
    #[On('filtered')]
    public function handleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * @param string $search
     *
     * @return void
     */
    #[On('applySearch')]
    public function handleSearch(string $search = ''): void
    {
        $this->search = $search;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws Exception
     *
     * @return void
     */
    public function handleActions(array $payload): void
    {
        $libryoIds = $this->libryos?->pluck('id')?->all() ?? $this->getLibryoFieldSubQuery('id');
        $libryoIds = is_array($libryoIds) ? $libryoIds : $libryoIds->pluck('id')->all();
        /** @var ApplicabilityNoteType $type */
        $type = ApplicabilityNoteType::tryFrom($payload['type']);

        app(IncludeExcludeFromLibryo::class)->handle(
            $libryoIds,
            $payload['references'],
            $payload['action'] === 'add',
            $type,
            $payload['comment'],
        );
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        $payload = $this->getReferencesAndWorks();

        /** @var View */
        return view('livewire.compilation.context-question.applicability-requirements-listing', $payload);
    }

    /**
     * Get the works, references and pagination.
     *
     * @return array<string, mixed>
     */
    protected function getReferencesAndWorks(): array
    {
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryoIds = $this->libryos?->pluck('id')?->all() ?? $this->getLibryoFieldSubQuery('id');
        /** @var Builder $siteWorks */
        $siteWorks = LibryoSpecificWork::whereIn('place_id', $libryoIds)->select(['work_id']);

        $referencesQuery = $this->getApplicableReferencesQuery($libryoIds, $siteWorks);

        $newWork = new Work();

        $paginatedWorkIDs = $referencesQuery->clone()
            ->orderBy('title')
            ->select(['work_id', 'title'])
            ->distinct('work_id')
            ->paginate(2);

        $workIDs = collect($paginatedWorkIDs->items())->pluck('work_id')->all();

        $works = Work::active()
            ->whereIn($newWork->qualifyColumn('id'), $workIDs)
            ->orderBy('title')
            ->get(['id', 'title', 'title_translation']);

        $libryosFilter = fn ($query) => $query->whereIn((new Libryo())->qualifyColumn('id'), $libryoIds);

        $references = $referencesQuery
            ->whereIn('work_id', $workIDs)
            ->with(['refPlainText:reference_id,plain_text'])
            ->when($manager->isSingleMode(), function ($builder) use ($libryosFilter) {
                $builder->withExists([
                    'compiledLibryos as libryo_recommended' => $libryosFilter,
                    'libryos as included_in_stream' => $libryosFilter,
                ]);
            })
            ->when(!$manager->isSingleMode(), function ($builder) use ($libryosFilter) {
                $builder->withCount([
                    'libryos as included_in_stream' => $libryosFilter,
                ]);
            })
            ->get(['id', 'work_id'])
            ->groupBy('work_id');

        return [
            'worksPaginator' => $paginatedWorkIDs,
            'works' => $works,
            'references' => $references,
            'libryo' => $manager->getActive(),
            'organisation' => $organisation,
            'siteWorks' => $siteWorks->pluck('work_id')->all(),
        ];
    }

    /**
     * Get the references that apply to the current state of filters.
     *
     * @param \Illuminate\Database\Eloquent\Builder|array<int, int> $libryoIds
     * @param Builder                                               $siteWorks
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getApplicableReferencesQuery(Builder|array $libryoIds, Builder $siteWorks): Builder
    {
        $locations = $this->filters['locations'] ?? ($this->locations ?? $this->getAllUsableLocations(true))->pluck('id')->all();
        $domains = $this->filters['domains'] ?? ($this->domains ?? $this->getAllUsableLegalDomains())->pluck('id')->all();
        $categories = $this->filters['categories'] ?? [];
        $hasSearch = strlen($this->search) > 3;

        $work = new Work();

        $builder = Reference::join($work->getTable(), $work->qualifyColumn('id'), 'work_id')
            ->where($work->qualifyColumn('status'), WorkStatus::active()->value)
            ->active()
            ->compilable()
            ->when($hasSearch, fn ($query) => $query->where($work->qualifyColumn('title'), 'like', "%{$this->search}%"))
            ->where(function ($refQuery) use ($siteWorks, $work, $categories, $domains, $locations) {
                $refQuery
                    ->where(function ($generalQuery) use ($categories, $domains, $locations, $work) {
                        $generalQuery
                            ->whereNull($work->qualifyColumn('organisation_id'))
                            ->whereHas('locations', fn ($query) => $query->whereIn('id', $locations))
                            ->whereHas('legalDomains', fn ($query) => $query->whereIn('id', $domains))
                            ->when(!empty($categories), function ($query) use ($categories) {
                                $subQuery = CategoryClosure::whereIn('ancestor', $categories)->select(['descendant']);

                                $query->whereHas('categories', fn ($builder) => $builder->whereIn('id', $subQuery));
                            });
                    })
                    ->orWhereIn((new Reference())->qualifyColumn('work_id'), $siteWorks);
            });

        $this->applyRecommendations($builder, $libryoIds, $siteWorks);

        $this->applyInclusions($builder, $libryoIds);

        return $builder;
    }

    /**
     * Apply the included-excluded filter.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param \Illuminate\Database\Eloquent\Builder|array<int, int>                                  $libryoIds
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function applyInclusions(Builder|Relation $builder, Builder|array $libryoIds): Builder|Relation
    {
        $refIds = LibryoReference::whereIn('place_id', $libryoIds)->select(['reference_id']);
        $column = (new Reference())->qualifyColumn('id');

        $included = isset($this->filters['included']);
        $excluded = isset($this->filters['excluded']);

        return $builder->when($included, fn ($query) => $query->whereIn($column, $refIds))
            ->when($excluded, fn ($query) => $query->whereNotIn($column, $refIds));
    }

    /**
     * Apply the recommendation filter.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param \Illuminate\Database\Eloquent\Builder|array<int, int>                                  $libryoIds
     * @param Builder                                                                                $siteWorks
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function applyRecommendations(Builder|Relation $builder, Builder|array $libryoIds, Builder $siteWorks): Builder|Relation
    {
        /** @var Builder $refIds */
        $refIds = CompiledLibryoReference::whereIn('place_id', $libryoIds)->select(['reference_id']);

        $refIds = Reference::whereIn('work_id', $siteWorks)->select(['id'])->union($refIds);

        $column = (new Reference())->qualifyColumn('id');

        $recommended = isset($this->filters['recommended']);

        return $builder->when($recommended, fn ($query) => $query->whereIn($column, $refIds));
    }
}
