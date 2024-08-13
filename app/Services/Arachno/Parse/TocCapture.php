<?php

namespace App\Services\Arachno\Parse;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Link;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use App\Stores\Arachno\LinkStore;
use Generator;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class TocCapture
{
    public function __construct(
        protected DomQueryRunner $domQueryRunner,
        protected TocItemFinder $tocItemFinder,
        protected LinkToUri $linkToUri,
        protected UrlFrontierLinkManager $urlFrontierLinkManager,
        protected LinkStore $linkStore,
    ) {
    }

    /**
     * @param PageCrawl     $pageCrawl
     * @param DomQuery      $tocQuery
     * @param callable|null $criteriaFunc
     * @param callable|null $extractLabelFunc
     * @param callable|null $extractLinkFunc
     * @param callable|null $extractUniqueIdFunc
     * @param callable|null $determineDepthFunc
     *
     * @return void
     */
    public function capture(
        PageCrawl $pageCrawl,
        DomQuery $tocQuery,
        ?callable $criteriaFunc = null,
        ?callable $extractLabelFunc = null,
        ?callable $extractLinkFunc = null,
        ?callable $extractUniqueIdFunc = null,
        ?callable $determineDepthFunc = null,
    ): void {
        $domCrawler = $pageCrawl->domCrawler;
        $pageUrl = $pageCrawl->pageUrl;

        $newCrawler = $this->domQueryRunner->runQuery($tocQuery, $domCrawler);
        if (count($newCrawler) === 0) {
            // @codeCoverageIgnoreStart
            $pageCrawl->log('Toc_query does not match the page identified as a ToC page: ' . $pageUrl->url);

            return;
            // @codeCoverageIgnoreEnd
        }

        $tocContent = $newCrawler->outerHtml();

        // we want to make sure we don't capture the same TOC twice, so we'll put it in the cache
        // and then check if it's been added before...
        $uidHash = ContentCache::buildUid($pageUrl->crawl_id ?? '', 'toc_' . $tocContent);
        $uid = ContentCache::hashForDB($uidHash);
        if (ContentCache::where('uid', $uid)->exists()) {
            $pageCrawl->log('skipping toc capture for: ' . $pageUrl->url);

            return;
        }

        ContentCache::create([
            'uid' => $uid,
            'response_body' => $tocContent,
            'crawl_id' => $pageUrl->crawl_id,
        ]);

        /** @var DomCrawler $newCrawler */
        $itemsGenerator = $this->tocItemFinder->findItems(
            $newCrawler,
            $criteriaFunc,
            $extractLabelFunc,
            $extractLinkFunc,
            $extractUniqueIdFunc,
            $determineDepthFunc,
        );

        $this->createItemsAndAddtoFrontier($itemsGenerator, $pageCrawl);
    }

    /**
     * @param Generator<TocItemDraft> $itemsGenerator
     *
     * @return void
     */
    public function createItemsAndAddtoFrontier(Generator $itemsGenerator, PageCrawl $pageCrawl, ?Doc $doc = null): void
    {
        $pageUrl = $pageCrawl->pageUrl;
        $lastLevelParents = [];
        $positionCount = [];
        /** @var TocItem|null */
        $pageUrlParentTocItem = $pageUrl->parent_toc_item_id ? TocItem::find($pageUrl->parent_toc_item_id) : null;
        $doc ??= $pageCrawl->getDoc();
        if (!$doc) {
            // @codeCoverageIgnoreStart
            $pageCrawl->log('No doc found to create toc items');
            throw new RuntimeException('No doc found to create toc items');
            // @codeCoverageIgnoreEnd
        }
        if (!$doc->id) {
            // @codeCoverageIgnoreStart
            $pageCrawl->log('Please make sure you call saveDoc first');
            throw new RuntimeException('Please make sure you call saveDoc first');
            // @codeCoverageIgnoreEnd
        }
        /** @var Doc $doc */
        $newItems = collect([]);
        $seenLinks = [];
        $docItems = $doc->tocItems()->with(['link:id,url'])->get(['source_unique_id', 'crawl_id', 'id', 'level', 'uid']);
        foreach ($itemsGenerator as $item) {
            /** @var TocItemDraft $item */
            $level = $item['level'] ?? (($pageUrlParentTocItem['level'] ?? 0) + 1);
            /** @var TocItem $parent */
            $parent = $lastLevelParents[$level - 1] ?? $pageUrlParentTocItem;
            $item['parent'] = $parent;
            if (!isset($positionCount[$parent->id ?? 0])) {
                $positionCount[$parent->id ?? 0] = 0;
            }
            $item['position'] = $positionCount[$parent->id ?? 0] ?? 0;

            $newTocItem = null;

            if (isset($item['source_unique_id'])) {
                $newTocItem = $docItems->where('source_unique_id', $item['source_unique_id'])
                    ->where('crawl_id', $pageCrawl->pageUrl->crawl_id)
                    ->first();
            }

            if (!$newTocItem) {
                $newTocItem = $this->createItem($item, $pageCrawl, $doc);
            }

            $newItems->add($newTocItem);

            if (!isset($seenLinks[$newTocItem->link?->id]) && isset($item['href'])) {
                $urlLink = $this->newUrlFrontierLink($pageUrl, $newTocItem, $item, $doc);
                $this->urlFrontierLinkManager->addFromPageCrawl(
                    $pageCrawl,
                    new Uri($newTocItem->link?->url ?? ''),
                    $urlLink
                );
            }

            // further links that need to be followed can be added too
            if (isset($item['links'])) {
                $urlLink = $this->newUrlFrontierLink($pageUrl, $newTocItem, $item, $doc);
                foreach ($item['links'] as $link) {
                    /** @var UrlFrontierLink $link */
                    $urlLink->anchor_text = $link->anchor_text;
                    $this->urlFrontierLinkManager->addFromPageCrawl(
                        $pageCrawl,
                        new Uri($link->url),
                        $urlLink
                    );
                }
            }
            $seenLinks[$newTocItem->link?->id] = true;

            $lastLevelParents[$level] = $newTocItem;
            $positionCount[$parent->id ?? 0]++;
        }
    }

    protected function newUrlFrontierLink(
        UrlFrontierLink $pageUrl,
        TocItem $newTocItem,
        TocItemDraft $item,
        ?Doc $doc = null,
    ): UrlFrontierLink {
        return new UrlFrontierLink([
            'crawl_id' => $pageUrl->crawl_id,
            'doc_id' => $doc->id ?? null,
            'parent_toc_item_id' => $newTocItem->id ?? null,
            'anchor_text' => $item['label'] ?? null,
            'referer' => $pageUrl->url,
            'wait_for' => null,
            'needs_browser' => $pageUrl->crawl?->needsBrowser() ?? false,
        ]);
    }

    public function createItem(
        TocItemDraft $item,
        PageCrawl $pageCrawl,
        Doc $doc
    ): TocItem {
        $pageUrl = $pageCrawl->pageUrl;
        $uri = $this->linkToUri->convertFromFrontier($item['href'] ?? '', $pageCrawl->getFinalRedirectedUrl());
        $sourceUniqueId = $item['source_unique_id'] ?? (string) $uri;

        $urlForLink = Str::before((string) $uri, '#');
        $urlForLink = Str::endsWith($urlForLink, '/') ? Str::beforeLast($urlForLink, '/') : $urlForLink;
        /** @var Link|null */
        $link = isset($item['href']) ? $this->linkStore->firstOrCreateFromUri($urlForLink) : null;
        $uidHash = TocItem::buildUid(
            $sourceUniqueId,
            $doc->uid_hash,
            $item['label'],
            $item['parent']->uid_hash ?? ''
        );

        /** @var TocItem */
        $newItem = TocItem::create([
            'uid' => TocItem::hashForDB($uidHash),
            'crawl_id' => $pageUrl->crawl_id,
            'source_unique_id' => $sourceUniqueId,
            'label' => $item['label'],
            'doc_id' => $doc->id,
            'link_id' => $link?->id,
            'uri_fragment' => $this->replaceInvalidCharsInFragment($uri),
            'parent_id' => $item['parent']->id ?? null,
            'level' => isset($item['parent']) ? (($item['parent']->level ?? 1) + 1) : ($item['level'] ?? 1),
            'position' => $item['position'] ?? (TocItem::where('doc_id')->max('position') + 1),
            // 'doc_position' => (TocItem::where('doc_id', $doc->id)->max('doc_position') ?? 0) + 1,
            'source_url' => $item['source_url'] ?? ((string) $uri),
        ]);

        if ($link) {
            $newItem->link()->associate($link);
        }

        return $newItem;
    }

    /**
     * @param UriInterface $uri
     *
     * @return string|null
     *
     * @see Pagecapture::replaceInvalidCharsInIds
     */
    private function replaceInvalidCharsInFragment(UriInterface $uri): ?string
    {
        $fragment = $uri->getFragment() ?: null;

        if ($fragment) {
            $fragment = str_replace('.', '_', $fragment);
            if (Str::of($fragment)->test('/^[0-9]+/')) {
                $fragment = 'l' . $fragment;
            }
        }

        return $fragment;
    }
}
