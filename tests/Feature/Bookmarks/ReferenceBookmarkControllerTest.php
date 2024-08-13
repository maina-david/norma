<?php

namespace Tests\Feature\Bookmarks;

use App\Models\Bookmarks\ReferenceBookmark;
use App\Models\Corpus\Work;
use App\Services\Search\Elastic\DocumentSearch;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class ReferenceBookmarkControllerTest extends MyTestCase
{
    public function testCreationRemovalAndFiltering(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.corpus.requirements.index';

        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($libryo);

        $this->partialMock(DocumentSearch::class, function (MockInterface $mock) use ($work) {
            $work->load(['references.legalDomains', 'references.locations.type', 'references.locations.country', 'references.citation', 'children.references.citation', 'references.refPlainText', 'children.references.refPlainText']);
            $works = new LengthAwarePaginator((new Work())->newCollection([$work]), 1, 50);
            $references = new LengthAwarePaginator($work->references, $work->references->count(), 50);
            $mock->shouldReceive('getReferencesForRequirementsSearch')->andReturn($references);
            $mock->shouldReceive('addHighlightsToResults')->andReturn($works);
        });

        $reference = $work->references->load(['citation', 'refPlainText'])->first();

        $this->get(route($routeName))->assertSuccessful()
            ->assertSee($work->title)
            ->assertSee($work->children->first()->title)
            ->assertSee($reference->refPlainText?->plain_text);

        $this->get(route($routeName, ['bookmarked' => 'yes']))->assertSuccessful()
            ->assertDontSee($work->title)
            ->assertDontSee($work->children->first()->title)
            ->assertDontSee($reference->refPlainText?->plain_text);

        $this->post(route('my.reference.bookmarks.store', ['reference' => $reference->id]))
            ->assertSuccessful();

        $this->get(route($routeName, ['bookmarked' => 'yes']))->assertSuccessful()
            ->assertSee($work->title)
            ->assertDontSee($work->children->first()->title)
            ->assertSee($reference->refPlainText?->plain_text);

        $this->assertDatabaseHas(ReferenceBookmark::class, ['reference_id' => $reference->id, 'user_id' => $user->id]);

        $this->delete(route('my.reference.bookmarks.destroy', ['reference' => $reference->id]))
            ->assertSuccessful();

        $this->get(route($routeName, ['bookmarked' => 'yes']))->assertSuccessful()
            ->assertDontSee($work->title)
            ->assertDontSee($work->children->first()->title)
            ->assertDontSee($reference->refPlainText?->plain_text);

        $this->assertDatabaseMissing(ReferenceBookmark::class, ['reference_id' => $reference->id, 'user_id' => $user->id]);
    }
}
