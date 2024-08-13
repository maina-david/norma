<?php

namespace Tests\Feature\Api\V3\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Mockery\MockInterface;
use Tests\TestCase;

class TocItemReferenceControllerTest extends TestCase
{
    public function testGenerationOfReference(): void
    {
        $content = '<div>Some content</div>';
        $this->partialMock(TocItemContentExtractor::class, function (MockInterface $mock) use ($content) {
            $mock->shouldReceive('extractItem')->andReturn($content);
        });
        $workRef = Reference::factory()->create(['type' => ReferenceType::work()->value]);
        $tocItem = TocItem::factory()->make()->setUid();
        $tocItem->save();

        $tocItem->doc->update(['work_id' => $workRef->work_id]);

        $repo = app(ClientRepository::class);
        $client = $repo->create(null, 'Test Client', '');
        Passport::actingAsClient($client);

        $this->withExceptionHandling()
            ->postJson(route('api.v3.toc-items.reference.store', ['item' => $tocItem->id]))
            ->assertUnauthorized();

        $client->update(['internal' => true]);

        $this->withoutExceptionHandling()
            ->postJson(route('api.v3.toc-items.reference.store', ['item' => $tocItem->id]))
            ->assertSuccessful();

        $this->assertDatabaseHas(Reference::class, [
            'uid' => $tocItem->uid,
            'work_id' => $workRef->work_id,
        ]);

        $this->withoutExceptionHandling()
            ->postJson(route('api.v3.toc-items.reference.store', ['item' => $tocItem->id]))
            ->assertSuccessful();
    }
}
