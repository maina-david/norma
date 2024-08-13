<?php

namespace Tests\Unit\Services\Arachno\Frontier;

use App\Models\Arachno\Link;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Services\Arachno\Frontier\DocHashGenerator;
use Tests\TestCase;

class DocHashGeneratorTest extends TestCase
{
    public function testSetVersionHash(): void
    {
        $content = 'Content';
        $hash = ContentResource::hashUid($content);
        $resource = ContentResource::factory()->create(['mime_type' => 'text/html', 'path' => '/', 'content_hash' => $hash]);
        $doc = Doc::factory()->create(['source_unique_id' => '123', 'first_content_resource_id' => $resource->id]);
        $doc->setUid()->save();
        $links = Link::factory(2)->create();

        // test without toc items
        $checkAgainst = sha1($doc->uid_hash . '__' . sha1($resource->content_hash));
        app(DocHashGenerator::class)->setHash($doc);
        $this->assertSame($checkAgainst, $doc->version_hash);

        // test with TOC items
        $tocItems = TocItem::factory(2)->for($doc)->create();
        $tocItems[0]->update(['link_id' => $links[0]->id, 'content_resource_id' => $resource->id]);
        $tocItems[1]->update(['link_id' => $links[1]->id, 'content_resource_id' => $resource->id]);
        $links[0]->setUid()->save();
        $links[1]->setUid()->save();

        $links[0]->contentResources()->attach($resource);
        $links[1]->contentResources()->attach($resource);

        $resourceStr = implode('__', [$resource->content_hash, $resource->content_hash]);

        $checkAgainst = sha1($doc->uid_hash . '__' . sha1($resourceStr) . '__' . sha1($tocItems[0]->uid_hash . '__' . $tocItems[1]->uid_hash));
        app(DocHashGenerator::class)->setHash($doc);
        $this->assertSame($checkAgainst, $doc->version_hash);
    }
}
