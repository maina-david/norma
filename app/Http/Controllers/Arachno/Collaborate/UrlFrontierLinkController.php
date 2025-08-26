<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasShowAction;
use App\Models\Arachno\UrlFrontierLink;
use App\Traits\Arachno\UsesSourceFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UrlFrontierLinkController extends CollaborateController
{
    use HasShowAction;
    use UsesSourceFilter;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return UrlFrontierLink::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.url-frontier-links';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'date' => fn ($row) => $row->created_at,
            'date_crawled' => fn ($row) => $row->crawled_at,
            'url' => fn ($row) => $row->url,
            'crawler' => fn ($row) => $row->crawl?->crawler ? $row->crawl->crawler->title . ' <div class="text-xs text-norma-gray-500">(' . $row->crawl->crawler->slug . ')</div>' : '',
            'source' => fn ($row) => $row->crawl?->crawler?->source?->title,
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
        return UrlFrontierLink::with(['crawl.crawler:id,title,slug,source_id', 'crawl.crawler.source'])
            ->orderBy('id', 'desc');
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
        return [
            'formWidth' => 'max-w-7xl',
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
}
