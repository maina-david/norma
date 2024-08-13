<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class TocItemControllerTest extends TestCase
{
    public function testTocItemActions(): void
    {
        $doc = Doc::factory()->create();
        $expression = WorkExpression::factory()->create(['doc_id' => $doc->id]);

        $items = TocItem::factory()->count(5)->create(['doc_id' => $doc->id]);
        $parentId = $items->first()->id;

        $children = TocItem::factory()->forDoc($doc)->count(2)->create(['parent_id' => $parentId, 'doc_id' => $doc->id]);

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->getJson(route('api.collaborate.work-expressions.toc-items.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->getJson(route('api.collaborate.work-expressions.toc-items.index', ['expression' => $expression->id, 'item' => $parentId]))
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');

        $toInsert = $items->take(2)->pluck('id')->all();
        $remaining = $items->reverse()->take(3)->pluck('id')->all();

        $this->assertDatabaseMissing(Reference::class, ['work_id' => $expression->work_id, 'type' => 11]);

        $payload = [
            'toc_items' => $toInsert,
        ];

        $this->postJson(route('api.collaborate.work-expressions.toc-items.bulk.insert-from-toc', ['expression' => $expression->id]), $payload)
            ->assertSuccessful();

        $this->assertSame(2, Reference::where('work_id', $expression->work_id)->where('type', 11)->count());

        $payload = [
            'toc_items' => $children->pluck('id')->all(),
        ];

        $created = Reference::where('work_id', $expression->work_id)->where('type', 11)->first();

        $this->postJson(route('api.collaborate.work-expressions.toc-items.bulk.insert-from-toc', ['expression' => $expression->id, 'reference' => $created->id]), $payload)
            ->assertSuccessful();

        $this->assertSame(4, Reference::where('work_id', $expression->work_id)->where('type', 11)->count());

        $this->postJson(route('api.collaborate.work-expressions.toc-items.insert-from-toc', ['expression' => $expression->id, 'item' => $remaining[1], 'reference' => $created->id]))
            ->assertSuccessful();

        $this->assertSame(5, Reference::where('work_id', $expression->work_id)->where('type', 11)->count());

        $this->postJson(route('api.collaborate.work-expressions.toc-items.update-from-toc', ['expression' => $expression->id, 'item' => $remaining[0], 'reference' => $created->id]))
            ->assertSuccessful();

        $this->assertSame(5, Reference::where('work_id', $expression->work_id)->where('type', 11)->count());
    }
}
