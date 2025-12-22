<?php

namespace Tests\Feature\Corpus;

use App\Models\Arachno\Link;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use Tests\Feature\My\MyTestCase;

class TocItemStreamControllerTest extends MyTestCase
{
    public function testToc(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $doc = Doc::factory()->create();
        $link = Link::factory()->create();
        $contentResource = ContentResource::factory()->create();
        $tocItem = TocItem::factory()->for($doc)->create(['link_id' => $link->id, 'content_resource_id' => $contentResource->id]);
        $routeNme = 'my.docs.toc';

        $tocItems = TocItem::factory(3)->for($doc)->create(['parent_id' => $tocItem->id]);

        $response = $this->get(route($routeNme, ['doc' => $doc]));
        $response->assertSee($tocItem->label);

        $response = $this->get(route($routeNme, ['doc' => $doc, 'itemId' => $tocItem->id]));
        $response->assertSee($tocItems[0]->label);
        $response->assertSee($tocItems[1]->label);
    }
}
