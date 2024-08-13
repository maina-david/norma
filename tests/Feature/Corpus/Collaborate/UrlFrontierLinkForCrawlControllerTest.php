<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class UrlFrontierLinkForCrawlControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['url'];
    }

    public function testItRendersTheIndexPage(): void
    {
        /** @var Crawl */
        $crawl = Crawl::factory()->create();
        /** @var Collection<UrlFrontierLink> */
        $links = UrlFrontierLink::factory(3)->create(['crawl_id' => $crawl->id]);

        $routeName = 'collaborate.arachno.crawls.url-frontier-links.index';
        $route = route($routeName, ['crawl' => $crawl->id]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);
        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($links[0], $response);

        $this->get(route('collaborate.arachno.crawls.url-frontier-links.show', ['crawl' => $crawl->id, 'url_frontier_link' => $links[0]->id]))
            ->assertRedirect();
    }
}
