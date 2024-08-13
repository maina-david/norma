<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\BulkRemoveAllTaggables;
use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use Tests\TestCase;

class BulkRemoveAllTaggablesTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['referenceable_type' => 'works']);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id]);
        /** @var Category */
        $category = Category::factory()->create();
        /** @var Category */
        $category2 = Category::factory()->create();
        $citationRef->categories()->attach($category);
        $citationRef->categories()->attach($category2);

        app(BulkRemoveAllTaggables::class)->handle(
            [
                $workRef->id,
                $citationRef->id,
            ],
            CategoryReference::class,
            CategoryReferenceDraft::class
        );

        $citationRef->refresh();
        $this->assertTrue($citationRef->categoryDrafts->contains($category));
        $this->assertTrue($citationRef->categoryDrafts->contains($category2));
        $this->assertSame(
            MetaChangeStatus::REMOVE->value,
            $citationRef->categoryDrafts()
                ->withPivot('change_status')
                ->first()
                ->pivot
                ->change_status
        );
        $this->assertSame(
            MetaChangeStatus::REMOVE->value,
            $citationRef->categoryDrafts()
                ->withPivot('change_status')
                ->get()
                ->last()
                ->pivot
                ->change_status
        );
    }
}
