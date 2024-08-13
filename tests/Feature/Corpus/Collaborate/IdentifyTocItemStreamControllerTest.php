<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Arachno\Link;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\WorkExpression;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\My\MyTestCase;

class IdentifyTocItemStreamControllerTest extends MyTestCase
{
    public function testToc(): void
    {
        $this->signIn($this->collaborateSuperUser());
        /** @var Doc */
        $doc = Doc::factory()->create();
        /** @var WorkExpression */
        $expression = WorkExpression::factory()->create();
        /** @var Link */
        $link = Link::factory()->create();
        /** @var ContentResource */
        $contentResource = ContentResource::factory()->create();
        /** @var TocItem */
        $tocItem = TocItem::factory()->for($doc)->create(['link_id' => $link->id, 'content_resource_id' => $contentResource->id]);
        $routeNme = 'collaborate.docs.identify-toc';

        /** @var Collection<TocItem> */
        $tocItems = TocItem::factory(3)->for($doc)->create(['parent_id' => $tocItem->id]);

        $response = $this->get(route($routeNme, ['doc' => $doc->id, 'expression' => $expression->id]));
        $response->assertSee($tocItem->label ?? '');

        $response = $this->get(route($routeNme, ['doc' => $doc, 'expression' => $expression->id, 'itemId' => $tocItem->id]));
        $response->assertSee($tocItems[0]->label ?? '');
        $response->assertSee($tocItems[1]->label ?? '');
    }
}
