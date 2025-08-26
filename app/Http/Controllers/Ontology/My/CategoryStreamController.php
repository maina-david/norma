<?php

namespace App\Http\Controllers\Ontology\My;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Enums\Ontology\CategoryType;
use App\Http\Controllers\Controller;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryStreamController extends Controller
{
    /**
     * Used for search suggest to search for works.
     *
     * @param Request  $request
     * @param string   $key      To distinguish between different frames if there are multiple frames on the same page
     * @param bool     $controls
     * @param callable $filter
     *
     * @return Response
     */
    protected function getCategories(Request $request, string $key, bool $controls, callable $filter): Response
    {
        /** @var string */
        $search = $request->input('search', '');

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $query = Category::forNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $query = Category::forOrganisationUserAccess($organisation, $user);
        }

        call_user_func($filter, $query, $search);

        $query->select(['id', 'display_label']);

        /** @var \Illuminate\Support\Collection $categories */
        $categories = strlen($search) > 2 ? $query->limit(100)->get() : (new Category())->newCollection();
        $categories = CategorySelectorCache::updateLabel($categories);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.ontology.my.category.search-suggest',
            'target' => 'search-suggest-categories-' . $key,
            'categories' => $categories,
            'search' => $search,
            'controls' => $controls,
            'linkToDetailed' => (bool) $request->query('link'),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Used for search suggest to search for works.
     *
     * @param Request $request
     * @param string  $key     To distinguish between different frames if there are multiple frames on the same page
     *
     * @return Response
     */
    public function forTaggingSuggest(Request $request, string $key): Response
    {
        return $this->getCategories($request, $key, false, function ($query, $search) {
            $query->forTagging()->forSearch($search);
        });
    }

    /**
     * Used for search suggest to search for works.
     *
     * @param Request $request
     * @param string  $key     To distinguish between different frames if there are multiple frames on the same page
     *
     * @return Response
     */
    public function forControlsSuggest(Request $request, string $key): Response
    {
        return $this->getCategories($request, "controls-{$key}", true, function ($query, $search) {
            $query->where('category_type_id', CategoryType::CONTROL->value)->forSearch($search);
        });
    }
}
