<?php

namespace Tests\Unit\Models\Ontology;

use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryClosure;
use Tests\TestCase;

/**
 * Tests Closure table trait methods.
 */
class CategoryClosureTest extends TestCase
{
    public function testCategoryClosure(): void
    {
        $this->assertTrue(true);
        $category = Category::factory()->create();
        $childCategory = Category::factory()->create(['parent_id' => $category->id]);

        $pivot = CategoryClosure::where('ancestor', $category->id)->where('descendant', $childCategory->id)->first();
        $this->assertNotNull($pivot);
        $this->assertTrue($pivot->depth === 1);

        $pivot = CategoryClosure::where('ancestor', $category->id)->where('descendant', $category->id)->first();
        $this->assertNotNull($pivot);
        $this->assertTrue($pivot->depth === 0);

        $childCategory2 = Category::factory()->create(['parent_id' => $category->id]);
        $this->assertTrue($childCategory2->getSiblings()->contains($childCategory));
        $this->assertFalse($childCategory2->getSiblings()->contains($childCategory2));

        $grandChild = Category::factory()->create(['parent_id' => $childCategory->id]);

        $this->assertTrue($category->descendants->contains($childCategory));
        $this->assertTrue($category->descendants->contains($grandChild));
        $this->assertFalse($category->descendants->contains($category));
        $this->assertTrue($category->descendantsWithSelf->contains($category));

        $this->assertTrue($grandChild->ancestors->contains($category));
        $this->assertTrue($grandChild->ancestors->contains($category));
        $this->assertFalse($grandChild->ancestors->contains($grandChild));
        $this->assertTrue($category->ancestorsWithSelf->contains($category));
    }
}
