<?php

namespace Tests\Feature\Corpus;

use App\Models\Corpus\ContentResource;
use App\Stores\Corpus\ContentResourceStore;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class ContentResourceControllerTest extends MyTestCase
{
    public function testStreamContent(): void
    {
        $this->initUserLibryoOrg();

        $content = '<div>Test</div>';
        $this->mock(ContentResourceStore::class, function (MockInterface $mock) use ($content) {
            $mock->shouldReceive('get')->andReturn($content);
        });

        $hash = ContentResource::hashUid($content);
        $uid = ContentResource::hashForDB($content);
        $testPath = '/test/path/' . $hash;
        $resource = ContentResource::factory()->create(['mime_type' => 'text/html', 'path' => $testPath, 'content_hash' => $hash, 'uid' => $uid]);
        $routeName = 'my.content-resources.stream.content';
        $response = $this->get(route($routeName, ['path' => $testPath]));
        $this->assertStringContainsString($content, $response->getContent());
    }
}
