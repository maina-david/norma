<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Enums\Arachno\CrawlType;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Arachno\CrawlerRequest;
use App\Models\Arachno\Crawler;
use App\Traits\Arachno\UsesSourceFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CrawlerController extends CollaborateController
{
    use UsesSourceFilter;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Crawler::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.arachno.crawlers';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return CrawlerRequest::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => $row->title,
            'slug' => fn ($row) => $row->slug,
            'source' => fn ($row) => $row->source?->title,
            'enabled' => fn ($row) => $row->enabled ? __('interface.yes') : __('interface.no'),
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Builder */
        return Crawler::with(['source']);
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'fluid' => true,
            'paginate' => 50,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        /** @var Crawler */
        $crawler = Crawler::find($request->route('crawler'));

        return [
            'formWidth' => 'max-w-7xl',
            'crawlTypes' => $crawler->getAvailableCrawlTypes(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [
            'source' => $this->getSourceFilter(),
        ];
    }

    /**
     * @param Crawler $crawler
     * @param int     $type
     *
     * @return RedirectResponse
     */
    public function startNewCrawl(Crawler $crawler, int $type): RedirectResponse
    {
        $crawlType = CrawlType::from($type);
        $crawler->createAndStartCrawl($crawlType);
        $this->notifyGeneralSuccess();

        return redirect()->to(route('collaborate.arachno.crawlers.show', ['crawler' => $crawler->id]));
    }

    /**
     * Pre-Store hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        $model->setUid();
    }
}
