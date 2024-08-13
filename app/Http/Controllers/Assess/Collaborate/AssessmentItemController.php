<?php

namespace App\Http\Controllers\Assess\Collaborate;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Cache\Ontology\Collaborate\LegalDomainSelectorCache;
use App\Enums\Assess\AssessmentItemType;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Assess\AssessmentItemRequest;
use App\Http\ResourceActions\Assess\ArchiveAssessmentItem;
use App\Http\ResourceActions\Assess\RestoreAssessmentItem;
use App\Http\Services\Laconic\LaconicApiClient;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemDescription;
use App\Models\Geonames\Location;
use App\Traits\UsesArchivedTableFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssessmentItemController extends CollaborateController
{
    use UsesArchivedTableFilter;

    /** @var string */
    protected string $sortBy = 'component_1';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return AssessmentItem::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.assessment-items';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return AssessmentItemRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        $domains = (new LegalDomainSelectorCache())->get();

        return [
            'id' => fn ($row) => $row->id,
            'description' => fn ($row) => static::renderPartial($row, 'description-column', ['domains' => $domains]),
            'works' => fn ($row) => static::renderLink(route('collaborate.works.index', ['assessmentItems' => [$row->id]]), __('corpus.work.works')),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->whereNull('organisation_id')
            ->when(
                $request->routeIs(['collaborate.assessment-items.edit', 'collaborate.assessment-items.show']),
                function ($builder) {
                    return $builder->with(['assessmentItemCategories']);
                }
            )
            ->when(
                $request->routeIs(['collaborate.assessment-items.show']),
                function ($builder) {
                    return $builder->with(['contextQuestions', 'guidanceNotes.location', 'descriptions.location']);
                }
            );
    }

    /**
     * Get extra data to be sent back to the form views.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function formViewsData(Request $request): array
    {
        return [
            'formWidth' => 'w-full',
        ];
    }

    /**
     * Get extra data to be sent back to the create view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function createViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }

    /**
     * Get extra data to be sent back to the edit view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function editViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }

    /**
     * Get extra data to be sent back to the show view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function showViewData(Request $request): array
    {
        return array_merge($this->formViewsData($request), [
            'categories' => (new CategorySelectorCache())->get(),
            'domains' => (new LegalDomainSelectorCache())->get(),
        ]);
    }

    /**
     * Post-Store hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function postStore(Model $model, Request $request): void
    {
        $model->categories()->sync($request->get('categories', []));
    }

    /**
     * Post-Update hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        $model->categories()->sync($request->get('categories', []));
    }

    /**
     * Get the assessment items as json.
     *
     * @param Request          $request
     * @param LaconicApiClient $client
     *
     * @return JsonResponse
     */
    public function indexJson(Request $request, LaconicApiClient $client): JsonResponse
    {
        /** @var Collection<string|int> $response */
        $response = collect([]);

        //        if ($request->get('search')) {
        //            $response = $client->requestPostAsync('/find-assessments', ['input' => $request->get('search'), 'top_items' => 30]);
        //            $response = $client->resolvePromise($response, AssessmentItem::class, [], fn ($item) => $item->id);
        //        }

        // @codeCoverageIgnoreStart
        if (empty($request->get('search')) && empty($request->get('categories'))) {
            return response()->json([]);
        }
        // @codeCoverageIgnoreEnd

        /** @var AssessmentItemType $type */
        $type = AssessmentItemType::tryFrom($request->get('type', AssessmentItemType::LEGACY->value));

        $items = AssessmentItem::orderBy('component_1')
            ->whereNull('organisation_id')
            ->orderBy('component_2')
            ->active()
            ->when($type, fn ($query) => $query->where('type', $type->value))
            ->where(function ($builder) use ($response, $request) {
                $builder->filter($request->only(['search']))
                    ->when($response->isNotEmpty(), fn ($query) => $query->orWhereIn('id', $response->toArray()));
            });

        $categories = collect(explode(',', $request->get('categories', '')))->filter();

        $items->when($categories->isNotEmpty(), fn ($builder) => $builder->hasCategories($categories->toArray()));

        $locations = [];

        if ($request->get('location_id') && $loc = Location::with('ancestors')->find($request->get('location_id'))) {
            /** @var Location $loc */
            /** @var Collection $anc */
            $anc = $loc->ancestors;
            $anc->push($loc);
            $locations = $anc->map(fn ($item) => $item->id)->toArray();
        }

        $items->with(['descriptions' => function ($query) use ($locations) {
            $query->where(function ($builder) use ($locations) {
                $builder->whereNull('location_id')
                    ->when(!empty($locations), fn ($query) => $query->orWhereIn('location_id', $locations));
            });
        }]);

        $items = $items->get()
            ->map(function ($item) {
                /** @var AssessmentItem $item */

                /** @var AssessmentItemDescription|null $description */
                $description = $item->descriptions->first();

                return [
                    'id' => $item->id,
                    'title' => $item->description,
                    'details' => trim(strip_tags(html_entity_decode($description?->description ?? ''))),
                ];
            })
            ->keyBy('id');

        $sorted = $response->reverse()->map(fn ($item) => $items->get($item));

        $items->each(function ($item) use ($sorted, $response) {
            if (!$response->contains($item['id'])) {
                $sorted->push($item);
            }
        });

        return response()->json($sorted->filter()->toArray());
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceActions(): array
    {
        return [
            new ArchiveAssessmentItem(),
            new RestoreAssessmentItem(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        return [
            ...$this->archivedTableFilter(),
        ];
    }
}
