<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\UpdateReferenceFromTocItem;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use Mockery\MockInterface;
use Tests\TestCase;

class UpdateReferenceFromTocItemTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $ref = Reference::factory()->create();

        /** @var TocItem */
        $tocItem = TocItem::factory()->create();

        $content = '<div>Some Content</div>';
        $this->partialMock(TocItemContentExtractor::class, function (MockInterface $mock) use ($content) {
            $mock->shouldReceive('extractItem')->andReturn($content);
        });

        $contentDraft = app(UpdateReferenceFromTocItem::class)->handle($tocItem, $ref);

        $this->assertSame($content, $contentDraft->html_content);
        $this->assertSame($tocItem->label, $contentDraft->title);
    }
}
