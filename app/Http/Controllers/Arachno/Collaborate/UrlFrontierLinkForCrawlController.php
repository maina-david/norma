<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UrlFrontierLinkForCrawlController extends UrlFrontierLinkController
{
    protected static function forResource(): ?string
    {
        return Crawl::class;
    }

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
        return 'collaborate.arachno.crawls.url-frontier-links';
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
        /** @var Crawl */
        $crawl = $this->getForResource();

        /** @var Builder */
        return parent::baseQuery($request)->where('crawl_id', $crawl->id);
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
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'crawl' => $request->route('crawl'),
        ];
    }

    /**
     * @param Crawl           $crawl
     * @param UrlFrontierLink $urlFrontierLink
     *
     * @return RedirectResponse
     */
    public function showRedirect(Crawl $crawl, UrlFrontierLink $urlFrontierLink): RedirectResponse
    {
        return redirect()
            ->to(route(
                'collaborate.url-frontier-links.show',
                ['url_frontier_link' => $urlFrontierLink->id])
            );
    }
}
