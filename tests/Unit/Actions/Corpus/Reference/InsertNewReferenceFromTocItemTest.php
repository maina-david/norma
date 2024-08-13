<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\InsertNewReferenceFromTocItem;
use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use Mockery\MockInterface;
use Tests\TestCase;

class InsertNewReferenceFromTocItemTest extends TestCase
{
    public function testHandle(): void
    {
        $content = '<div>Some content</div>';
        $this->partialMock(TocItemContentExtractor::class, function (MockInterface $mock) use ($content) {
            $mock->shouldReceive('extractItem')->andReturn($content);
        });
        /** @var Reference */
        $workRef = Reference::factory()->create(['type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['work_id' => $workRef->work_id, 'parent_id' => $workRef->id, 'position' => 1]);
        /** @var Reference */
        $citationRef2 = Reference::factory()->create(['work_id' => $workRef->work_id, 'parent_id' => $workRef->id, 'position' => 2]);
        /** @var TocItem */
        $tocItem = TocItem::factory()->create();

        $newRef = app(InsertNewReferenceFromTocItem::class)->handle($tocItem, $workRef->work_id, $citationRef);
        $this->assertSame($workRef->work_id, $newRef->work_id);
        $this->assertSame(ReferenceStatus::pending()->value, $newRef->status);
        $this->assertSame(2, $newRef->position);
        $this->assertSame(3, $citationRef2->refresh()->position);
        $this->assertSame(1, $citationRef->refresh()->position);
        $this->assertNotNull($newRef->contentDraft);
        $this->assertSame($content, $newRef->contentDraft->html_content);

        $maxPos = Reference::where('work_id', $workRef->work_id)->max('position');
        $newRef = app(InsertNewReferenceFromTocItem::class)->handle($tocItem, $workRef->work_id);
        $this->assertSame($maxPos + 1, $newRef->position);

        /** @var TocItem */
        $tocItem = TocItem::factory()->create();
        $tocItem->setUid()->save();

        $newRef = app(InsertNewReferenceFromTocItem::class)->handle($tocItem, $workRef->work_id);
        $this->assertSame($maxPos + 2, $newRef->position);

        $sameRef = app(InsertNewReferenceFromTocItem::class)->handle($tocItem, $workRef->work_id);
        $this->assertSame($maxPos + 2, $newRef->position);

        $this->assertSame($newRef->id, $sameRef->id);
    }
}
