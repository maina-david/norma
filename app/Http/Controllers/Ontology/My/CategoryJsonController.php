<?php

namespace App\Http\Controllers\Ontology\My;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryJsonController
{
    /**
     * Get the categories for tagging.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forTagging(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        if (strlen($search) < 3) {
            return response()->json([]);
        }

        $manager = app(ActiveLibryosManager::class);

        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $query = Category::forLibryo($libryo);
        } else {
            // @codeCoverageIgnoreStart
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var User $user */
            $user = Auth::user();

            $query = Category::forOrganisationUserAccess($organisation, $user);
            // @codeCoverageIgnoreEnd
        }

        /** @var \Illuminate\Support\Collection $categories */
        $categories = $query->forTagging()
            ->where('display_label', 'like', "%{$search}%")
            ->get(['id', 'display_label']);

        $categories = CategorySelectorCache::updateLabel($categories, 'title')
            ->map(fn ($category) => [
                'id' => $category->id,
                'title' => $category->display_label,
                'details' => $category->title !== $category->display_label ? Str::beforeLast($category->title, '|') : '',
            ])
            ->sortBy('title')
            ->values()
            ->all();

        return response()->json($categories);
    }
}
