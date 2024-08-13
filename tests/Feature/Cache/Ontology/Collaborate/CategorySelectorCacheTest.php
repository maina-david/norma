<?php

namespace Tests\Feature\Cache\Ontology\Collaborate;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Models\Ontology\Category;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategorySelectorCacheTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCachesTheCorrectData(): void
    {
        $categories = Category::factory()->count(5)->create();
        $parent = $categories->first();
        $childCategories = Category::factory()->count(5)->create(['parent_id' => $parent->id]);

        $categories = $categories->merge($childCategories);

        $selector = (new CategorySelectorCache());

        $selector->flush();

        $this->assertFalse(Cache::has('category_selector_cache'));

        $fromCache = $selector->get();

        $this->assertTrue(Cache::has('category_selector_cache'));

        $categories->each(function (Category $category) use ($parent, $fromCache) {
            $this->assertArrayHasKey($category->id, $fromCache);

            if ($category->parent_id) {
                $this->assertSame($parent->display_label . ' | ' . $category->display_label, $fromCache[$category->id]);

                return;
            }

            $this->assertSame($category->display_label, $fromCache[$category->id]);
        });
    }
}
