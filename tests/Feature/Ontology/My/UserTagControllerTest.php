<?php

namespace Tests\Feature\Ontology\My;

use App\Models\Ontology\UserTag;
use Tests\Feature\My\MyTestCase;

class UserTagControllerTest extends MyTestCase
{
    public function testIndexJson(): void
    {
        $user = $this->activateAllStreams();
        $org = $user->organisations->first();
        $tag = UserTag::factory()->for($org)->create();
        $tag2 = UserTag::factory()->create();
        $tag3 = UserTag::factory()->for($org)->create();

        $response = $this->get(route('my.user-tags.index.json'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $tag->id, 'title' => $tag->title]]);
        $response->assertJsonMissing([['id' => $tag2->id, 'title' => $tag2->title]]);
        $response->assertJsonFragment([['id' => $tag3->id, 'title' => $tag3->title]]);

        // test filtering
        $response = $this->get(route('my.user-tags.index.json', ['search' => $tag->title]), ['Accept' => 'application/json']);
        $response->assertJsonFragment([['id' => $tag->id, 'title' => $tag->title]]);
        $response->assertJsonMissing([['id' => $tag3->id, 'title' => $tag3->title]]);
    }
}
