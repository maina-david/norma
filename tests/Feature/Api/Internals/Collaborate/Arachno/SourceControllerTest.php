<?php

namespace Tests\Feature\Api\Internals\Collaborate\Arachno;

use App\Models\Arachno\Source;
use Tests\TestCase;

class SourceControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGettingSources(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $source = Source::factory()->create();

        $this->getJson(route('api.collaborate.sources.index.json'))
            ->assertSuccessful()
            ->assertJson([
                [
                    'id' => $source->id,
                ],
            ]);
    }
}
