<?php

namespace Tests\Unit\Actions\Corpus\TocItem;

use App\Actions\Corpus\TocItem\UpdateTocItemDocPositions;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class UpdateTocItemDocPositionsTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Doc */
        $doc = Doc::factory()->create();
        $tocItems = TocItem::factory(3)
            ->sequence(fn (Sequence $sequence) => ['position' => $sequence->index])
            ->create(['doc_id' => $doc->id]);
        /** @var TocItem */
        $tocItem = $tocItems[0];
        $tocItemChildren = TocItem::factory(3)
            ->sequence(fn (Sequence $sequence) => ['position' => $sequence->index])
            ->create(['doc_id' => $doc->id, 'parent_id' => $tocItem->id]);

        $tocItemChildrenChildren = TocItem::factory(3)
            ->sequence(fn (Sequence $sequence) => ['position' => $sequence->index])
            ->create(['doc_id' => $doc->id, 'parent_id' => $tocItemChildren[2]->id]);
        app(UpdateTocItemDocPositions::class)->handle($doc);

        // test for this structure:
        // - item
        //     -item
        //     -item
        //     -item
        //         -item
        //         -item
        //         -item
        // -item
        // -item

        $this->assertSame(2, $tocItemChildren[0]->refresh()->doc_position);
        $this->assertSame(3, $tocItemChildren[1]->refresh()->doc_position);
        $this->assertSame(5, $tocItemChildrenChildren[0]->refresh()->doc_position);
        $this->assertSame(8, $tocItems[1]->refresh()->doc_position);
        $this->assertSame(9, $tocItems[2]->refresh()->doc_position);
    }
}
