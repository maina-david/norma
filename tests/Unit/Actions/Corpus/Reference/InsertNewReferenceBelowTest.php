<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\InsertNewReferenceBelow;
use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use Tests\TestCase;

class InsertNewReferenceBelowTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['work_id' => $workRef->work_id, 'parent_id' => $workRef->id, 'position' => 1]);
        /** @var Reference */
        $citationRef2 = Reference::factory()->create(['work_id' => $workRef->work_id, 'parent_id' => $workRef->id, 'position' => 2]);

        $newRef = app(InsertNewReferenceBelow::class)->handle($citationRef);
        $this->assertSame($workRef->work_id, $newRef->work_id);
        $this->assertSame(ReferenceStatus::pending()->value, $newRef->status);
        $this->assertSame(2, $newRef->position);
        $this->assertSame(3, $citationRef2->refresh()->position);
        $this->assertSame(1, $citationRef->refresh()->position);
        $this->assertNotNull($newRef->contentDraft);
    }
}
