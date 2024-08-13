<?php

namespace Tests\Feature\Arachno\Collaborate;

use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\Abstracts\CrudTestCase;

class UrlFrontierLinkControllerTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'id';

    protected bool $descending = true;

    protected bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return UrlFrontierLink::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.url-frontier-links';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return ['url'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleLabels(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected static function visibleShowLabels(): array
    {
        return ['URL', 'Date Crawled', 'Crawler', 'Source'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'store', 'edit', 'update', 'destroy',
        ];
    }

    public function testIndexFilters(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create();
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        /** @var Collection<UrlFrontierLink> $links */
        $links = UrlFrontierLink::factory(5)->create();
        $route = $this->getRoute('index');

        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        /** @var UrlFrontierLink */
        $link1 = $links[0];
        /** @var UrlFrontierLink */
        $link2 = $links[1];
        $link1->update(['crawl_id' => $crawl->id]);
        $routeName = 'collaborate.url-frontier-links.index';
        $route = route($routeName, ['search' => $link1->url]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($link1->url);
        $response->assertDontSee($link2->url);

        $route = route($routeName, ['source' => $crawler->source?->id]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($link1->url);
        $response->assertDontSee($link2->url);
    }
}
