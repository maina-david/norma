<?php

namespace Tests\Unit\Actions\Corpus\Doc;

use App\Models\Corpus\Doc;
use App\Models\Corpus\DocMeta;
use Tests\TestCase;

class HandleCreatedTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testocMetaIsCreated(): void
    {
        $doc = Doc::factory()->create();

        $this->assertNotNull(DocMeta::find($doc->id));
    }
}
