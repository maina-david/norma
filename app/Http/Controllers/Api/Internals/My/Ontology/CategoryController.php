<?php

namespace App\Http\Controllers\Api\Internals\My\Ontology;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Models\Actions\ActionArea;
use App\Models\Ontology\Category;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class CategoryController
{
    /**
     * Get the control categories.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function controls(ActiveNormasManager $manager): JsonResponse
    {
        $fromActions = Task::forNormaOrOrganisation($manager->getActive(), $manager->getActiveOrganisation())
            ->whereNotNull('action_area_id')
            ->select('action_area_id');

        $fromActions = ActionArea::whereIn('id', $fromActions)->select('control_category_id');
        $categories = Category::whereIn('id', $fromActions)->get(['display_label', 'id']);

        return $this->updateAndMap($categories);
    }

    /**
     * Get the subject categories.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subjects(ActiveNormasManager $manager): JsonResponse
    {
        $fromActions = Task::forNormaOrOrganisation($manager->getActive(), $manager->getActiveOrganisation())
            ->whereNotNull('action_area_id')
            ->select('action_area_id');

        $fromActions = ActionArea::whereIn('id', $fromActions)->select('subject_category_id');
        $categories = Category::whereIn('id', $fromActions)->get(['display_label', 'id']);

        return $this->updateAndMap($categories);
    }

    /**
     * Update the labels and return the response.
     *
     * @param \Illuminate\Database\Eloquent\Collection $categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function updateAndMap(Collection $categories): JsonResponse
    {
        $categories = CategorySelectorCache::updateLabel($categories)
            ->map(fn ($item) => ['label' => $item->display_label, 'value' => $item->id])
            ->sortBy('label')
            ->values()
            ->all();

        return response()->json(['data' => $categories]);
    }
}
