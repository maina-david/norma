<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\BulkRemoveAllTaggableDrafts;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Tag;
use Tests\TestCase;

class BulkRemoveAllTaggableDraftsTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['referenceable_type' => 'works']);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id]);
        /** @var Tag */
        $tag = Tag::factory()->create();
        $citationRef->tagDrafts()->attach($tag);

        app(BulkRemoveAllTaggableDrafts::class)->handle(
            [$workRef->id],
            ReferenceTagDraft::class
        );

        $citationRef->refresh();
        $this->assertTrue($citationRef->tagDrafts->isEmpty());
    }
}
