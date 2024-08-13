<?php

namespace App\Http\Controllers\Actions\Collaborate;

use App\Models\Actions\ActionArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ActionAreaJsonController
{
    /**
     * Get the listing of action areas.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
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

        $categories = collect(explode(',', $request->get('categories', '')))->filter();

        $items = ActionArea::active()
            ->where(function ($builder) use ($response, $request) {
                $search = $request->get('search');

                $builder
                    ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
                    ->when($response->isNotEmpty(), fn ($query) => $query->orWhereIn('id', $response->toArray()));
            })
            ->when($categories->isNotEmpty(), fn ($builder) => $builder->whereIn('subject_category_id', $categories->toArray()))

            ->orderBy('title')
            ->get()
            ->map(function ($item) {
                /** @var ActionArea $item */

                return [
                    'id' => $item->id,
                    'title' => $item->title,
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
}
