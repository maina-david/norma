<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\UpdateReferenceFromTocItem;
use App\Actions\Corpus\Reference\UpdateReferencesContentFromDoc;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Services\Corpus\TocItemContentExtractor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class UpdateReferencesContentFromDocTest extends TestCase
{
    public function testHandle(): void
    {
        Queue::fake();

        /** @var Work */
        $work = Work::factory()->has(Reference::factory()->count(3))->create();
        /** @var Reference */
        $ref1 = $work->references->first();
        /** @var Reference */
        $ref2 = $work->references[1];
        /** @var Reference */
        $ref3 = $work->references[2];
        $ref2->setUid()->save();

        /** @var Doc */
        $doc = Doc::factory()->create(['source_unique_id' => '123']);
        $doc->setUid()->save();
        $content = 'Content';

        /** @var Collection<TocItem> */
        $tocItems = TocItem::factory(3)
            ->for($doc)
            ->create();

        $tocItems[2]->setUid()->save();
        $ref3->update(['uid' => $tocItems[2]->uid]);

        $content = '<div>Some Content</div>';
        $this->partialMock(TocItemContentExtractor::class, function (MockInterface $mock) use ($content) {
            $mock->shouldReceive('extractItem')->andReturn($content);
        });

        app(UpdateReferencesContentFromDoc::class)->handle($work, $doc);

        UpdateReferenceFromTocItem::assertPushed();
    }
}
