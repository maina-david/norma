<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use Tests\TestCase;

class ReferenceContentControllerTest extends TestCase
{
    public function testGettingAndSettingHtmlContent(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $reference = Reference::factory()->create();
        $content = ReferenceContent::factory()->for($reference)->create();

        $this->getJson(route('api.collaborate.references.content.show', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertJson([
                'live' => $content->cached_content,
                'draft' => [
                    'html_content' => null,
                    'title' => null,
                ],
                'versions' => [],
            ]);

        $payload = [
            'title' => 'test title',
            'content' => 'test',
        ];
        $this->putJson(route('api.collaborate.references.content.update', ['reference' => $reference->id]), $payload)
            ->assertSuccessful()
            ->assertJson([
                'live' => $content->cached_content,
                'draft' => [
                    'html_content' => 'test',
                    'title' => 'test title',
                ],
                'versions' => [],
            ]);
    }
}
