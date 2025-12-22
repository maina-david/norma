<?php

namespace Tests\Feature\Ontology\My;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Ontology\Tag;
use Tests\Feature\My\MyTestCase;

class TagControllerTest extends MyTestCase
{
    public function testIndexJson(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $work = Work::factory()->create();
        $reference = Reference::factory()->for($work)->create();
        $reference->normas()->attach($norma);

        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $reference->tags()->attach($tag);
        $reference->tags()->attach($tag3);

        $response = $this->get(route('my.tags.index.json'), ['Accept' => 'application/json']);
        $response->assertSuccessful();

        $response->assertJsonFragment([['id' => $tag->id, 'title' => $tag->title]]);
        $response->assertJsonMissing([['id' => $tag2->id, 'title' => $tag2->title]]);
        $response->assertJsonFragment([['id' => $tag3->id, 'title' => $tag3->title]]);

        // test filtering
        $response = $this->get(route('my.tags.index.json', ['search' => $tag->title]), ['Accept' => 'application/json']);
        $response->assertJsonFragment([['id' => $tag->id, 'title' => $tag->title]]);
        $response->assertJsonMissing([['id' => $tag3->id, 'title' => $tag3->title]]);

        $this->activateAllStreams($user);
        $response = $this->get(route('my.tags.index.json'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
    }
}
