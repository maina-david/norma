<?php

namespace App\Observers\Ontology;

use App\Models\Ontology\Category;

class CategoryObserver
{
    /**
     * Listen to the saving event.
     *
     * @param \App\Models\Ontology\Category $category
     *
     * @return void
     */
    public function saving(Category $category): void
    {
        if ($category->isDirty(['parent_id'])) {
            $category->load('parent');
            $category->level = ($category->parent->level ?? 0) + 1;
        }
    }

    /**
     * Listen to the saved event.
     *
     * @param \App\Models\Ontology\Category $category
     *
     * @return void
     */
    public function saved(Category $category): void
    {
        if ($category->isDirty('level')) {
            $category->load(['children']);
            $category->children->each(function ($child) use ($category) {
                /** @var Category $child */
                $child->level = $category->level + 1;
                $child->save();
            });
        }
    }
}
