<?php

namespace Tests\Feature\Corpus;

use App\Models\Corpus\ContentResource;
use App\Stores\Corpus\ContentResourceStore;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class ContentResourceStreamControllerTest extends MyTestCase
{
    public function testShow(): void
    {
        $this->initUserNormaOrg();

        $content = '<div>Test</div>';
        $this->mock(ContentResourceStore::class, function (MockInterface $mock) use ($content) {
            $mock->shouldReceive('get')->andReturn($content);
        });

        $contentResource = ContentResource::factory()->create(['mime_type' => 'text/html', 'path' => '/']);
        $routeNme = 'my.content-resources.show';
        $response = $this->get(route($routeNme, ['resource' => $contentResource, 'targetId' => 1]));
        $this->assertStringContainsString($content, $response->getContent());
    }
}
