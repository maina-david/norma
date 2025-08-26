<?php

namespace App\Http\Controllers\Ontology\Collaborate;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Enums\Assess\AssessmentItemType;
use App\Http\Services\NormaAI\Client;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CategoryJsonController
{
    /**
     * Get the categories as json.
     *
     * @param Request                            $request
     * @param \App\Http\Services\NormaAI\Client $client
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return JsonResponse
     */
    public function index(Request $request, Client $client): JsonResponse
    {
        /** @var Collection<string|int> $response */
        $response = collect([]);

        // @codeCoverageIgnoreStart
        if ($search = $request->get('search')) {
            $usableIds = $this->getUsableQuery($request)->pluck('id')->all();
            $response = $client->searchCategories($search, ['id' => $usableIds]);
        }
        // @codeCoverageIgnoreEnd

        $items = $this->getUsableQuery($request)
            ->when($response->isEmpty(), fn ($query) => $query->filter($request->only(['search'])))
            ->when($response->isNotEmpty(), fn ($query) => $query->whereIn('id', $response->all()));

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

        $cache = app(CategorySelectorCache::class)->get();

        $items = $items->get()
            ->map(function ($item) use ($cache) {
                /** @var Category $item */

                /** @var \App\Models\Ontology\CategoryDescription|null $description */
                $description = $item->descriptions->first();

                return [
                    'id' => $item->id,
                    'title' => $cache[$item->id] ?? $item->display_label,
                    'details' => trim(strip_tags(html_entity_decode($description?->description ?? ''))),
                ];
            })
            ->keyBy('id');

        $sorted = $response->map(fn ($item) => $items->get($item));

        $items->each(function ($item) use ($sorted, $response) {
            if (!$response->contains($item['id'])) {
                $sorted->push($item);
            }
        });

        $sorted = $sorted->filter();

        //        $sorted = $sorted->sortBy('title');

        return response()->json($sorted->values()->all());
    }

    /**
     * Apply restricions to the available categories.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder<Category>
     */
    protected function getUsableQuery(Request $request): Builder
    {
        $isContext = $request->has('context');
        $isAssess = $request->has('assess');
        $isActions = $request->has('actions');

        $activeRelation = fn ($query, $rel) => $query->whereHas($rel, fn ($builder) => $builder->active());

        return Category::orderBy('display_label')
            ->when($isActions, fn ($query) => $activeRelation($query, 'actionsAsSubject'))
            ->when($isContext, fn ($query) => $activeRelation($query, 'contextQuestions'))
            ->when($isAssess, fn ($query) => $query->whereHas('assessmentItems', function ($builder) {
                $builder->active()->where('type', AssessmentItemType::LEGACY->value);
            }));
    }
}
