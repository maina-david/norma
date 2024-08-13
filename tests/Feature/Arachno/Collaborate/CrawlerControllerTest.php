<?php

namespace Tests\Feature\Arachno\Collaborate;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawler;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\Abstracts\CrudTestCase;

class CrawlerControllerTest extends CrudTestCase
{
    protected bool $searchable = true;

    protected bool $collaborate = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Crawler::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.arachno.crawlers';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return ['title'];
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
        return ['Title', 'Slug', 'Schedule', 'Class Name', 'Enabled'];
    }

    public function testIndexFilters(): void
    {
        /** @var Collection<Crawler> */
        $crawlers = Crawler::factory(5)->create();
        $route = $this->getRoute('index');

        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        /** @var Crawler */
        $crawler1 = $crawlers[0];
        /** @var Crawler */
        $crawler2 = $crawlers[1];
        $routeName = 'collaborate.arachno.crawlers.index';
        $route = route($routeName, ['search' => $crawler1->title]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($crawler1->title);
        $response->assertDontSee($crawler2->title);

        $route = route($routeName, ['source' => $crawler1->source?->id]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($crawler1->title);
        $response->assertDontSee($crawler2->title);
    }

    public function testStartNewCrawl(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create();
        $routeName = 'collaborate.arachno.crawlers.start-crawl';
        $route = route($routeName, ['crawler' => $crawler->id, 'type' => CrawlType::FULL_CATALOGUE->value]);

        $this->validateAuthGuard($route, 'post');
        $this->signIn();
        $this->validateCollaborateRole($route, 'post');

        $this->assertTrue($crawler->crawls()->exists());
    }
}
