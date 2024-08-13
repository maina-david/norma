<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\BulkRemoveTaggables;
use App\Enums\Corpus\MetaChangeStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use Tests\TestCase;

class BulkRemoveTaggablesTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['referenceable_type' => 'works', 'type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id]);
        /** @var Category */
        $category = Category::factory()->create();
        /** @var Category */
        $category2 = Category::factory()->create();
        $citationRef->categories()->attach($category);
        $citationRef->categories()->attach($category2);

        app(BulkRemoveTaggables::class)->handle(
            [
                $workRef->id,
                $citationRef->id,
            ],
            [$category->id],
            CategoryReference::class,
            CategoryReferenceDraft::class
        );

        $citationRef->refresh();
        $this->assertTrue($citationRef->categoryDrafts->contains($category));
        $this->assertFalse($citationRef->categoryDrafts->contains($category2));
        $this->assertSame(
            MetaChangeStatus::REMOVE->value,
            $citationRef->categoryDrafts()
                ->withPivot('change_status')
                ->first()
                ->pivot
                ->change_status
        );
    }
}
