<?php

namespace App\Http\Controllers\Compilation;

use App\Http\Controllers\Controller;
use App\Http\Services\Laconic\LaconicApiClient;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use App\Models\Geonames\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContextQuestionJsonController extends Controller
{
    /**
     * Get the listing of context questions.
     *
     * @param Request          $request
     * @param LaconicApiClient $client
     *
     * @return JsonResponse
     */
    public function index(Request $request, LaconicApiClient $client): JsonResponse
    {
        /** @var Collection<string|int> $response */
        $response = collect([]);
        //        if ($request->get('search')) {
        //            $response = $client->requestPostAsync('/find-questions', ['input' => $request->get('search'), 'top_qs' => 10]);
        //            $response = $client->resolvePromise($response, ContextQuestion::class, [], fn ($item) => $item->id);
        //        }

        $categories = collect(explode(',', $request->get('categories', '')))->filter();

        $json = ContextQuestion::where(function ($builder) use ($response, $request) {
            $builder->filter($request->only(['search']))
                ->when($response->isNotEmpty(), fn ($query) => $query->orWhereIn('id', $response->toArray()));
        });

        $json->when($categories->isNotEmpty(), fn ($builder) => $builder->hasCategories($categories->toArray()));

        $locations = [];

        if ($request->get('location_id') && $loc = Location::with('ancestors')->find($request->get('location_id'))) {
            /** @var Location $loc */
            /** @var Collection $anc */
            $anc = $loc->ancestors;
            $anc->push($loc);
            $locations = $anc->map(fn ($item) => $item->id)->toArray();
        }

        $json->when(!empty($locations), function ($query) use ($locations) {
            $query->where(function ($builder) use ($locations) {
                $builder->doesntHave('locations')
                    ->orWhereHas('locations', function ($query) use ($locations) {
                        $query->whereIn('id', $locations);
                    });
            });
        });

        $json->with(['descriptions' => function ($query) use ($locations) {
            $query->where(function ($builder) use ($locations) {
                $builder->whereNull('location_id')
                    ->when(!empty($locations), fn ($query) => $query->orWhereIn('location_id', $locations));
            });
        }]);

        $items = $json->active()->get()
            ->map(function ($question) {
                /** @var ContextQuestion $question */

                /** @var ContextQuestionDescription|null $description */
                $description = $question->descriptions->first();

                return [
                    'id' => $question->id,
                    'title' => $question->question,
                    'details' => trim(strip_tags(html_entity_decode($description?->description ?? ''))),
                ];
            })
            ->sortBy('title')
            ->keyBy('id');

        $sorted = $response->reverse()->map(fn ($item) => $items->get($item));

        $items->each(function ($item) use ($sorted, $response) {
            if (!$response->contains($item['id'])) {
                $sorted->push($item);
            }
        });

        return response()->json($sorted->filter()->toArray());
    }
}
