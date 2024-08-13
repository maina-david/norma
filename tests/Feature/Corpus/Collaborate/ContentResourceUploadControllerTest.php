<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

class ContentResourceUploadControllerTest extends TestCase
{
    public function testUpload(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        $url = 'http://example.com';
        $this->partialMock(ContentResourceStore::class, function (MockInterface $mock) use ($url) {
            $mock->shouldReceive('storeResource');
            $mock->shouldReceive('getLinkForResource')->andReturn($url);
        });

        $this->post(route('collaborate.corpus.content-resources.upload'), [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])
            ->assertSuccessful()
            ->assertSee($url);
    }
}
