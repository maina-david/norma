<?php

namespace App\Services\Corpus;

use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Services\Html\ExtractBetween;
use App\Stores\Corpus\ContentResourceStore;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class TocItemContentExtractor
{
    public function __construct(
        protected ContentResourceStore $contentResourceStore,
        protected ExtractBetween $extractBetween,
        protected PrepareContentHtml $prepareContentHtml,
    ) {
    }

    /**
     * @param Doc $doc
     *
     * @throws Exception
     *
     * @return Collection<TocItem>
     */
    public function extract(Doc $doc): Collection
    {
        $items = $doc->tocItems()
            ->orderBy('doc_position')
            ->with(['contentResource:id,path'])
            ->get(['id', 'content_resource_id', 'uri_fragment']);

        $grouped = $items->groupBy('content_resource_id');

        foreach ($grouped as $contentResourceId => $tocItems) {
            $contentResource = $tocItems->first()?->contentResource;
            if (!isset($contentResource->path)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $content = $this->contentResourceStore->get($contentResource->path) ?? '';
            $next = null;
            foreach ($tocItems as $ind => $item) {
                $next = $tocItems[$ind + 1] ?? null;
                $item->_content = $this->extractBetweenItems($content, $item, $next);

                $item->content_hash = sha1($item->_content);
                $item->save();
            }
        }

        return $items;
    }

    /**
     * @param TocItem $tocItem
     *
     * @throws Exception
     *
     * @return string
     */
    public function extractItem(TocItem $tocItem): string
    {
        $contentResource = $tocItem->contentResource;
        if (!$contentResource) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }
        $content = $this->contentResourceStore->get($contentResource->path ?? '') ?? '';

        /** @var TocItem|null */
        $next = TocItem::where('doc_id', $tocItem->doc_id)
            ->where('doc_position', '>', $tocItem->doc_position)
            ->orderBy('doc_position')
            ->first();

        return $this->extractBetweenItems($content, $tocItem, $next);
    }

    /**
     * @param string       $content
     * @param TocItem      $tocItem
     * @param TocItem|null $next
     *
     * @return string
     */
    protected function extractBetweenItems(string $content, TocItem $tocItem, ?TocItem $next): string
    {
        if (!$tocItem->uri_fragment) {
            return $this->prepareContentHtml->cleanHtmlContent($content);
        }
        $firstId = $tocItem->uri_fragment;
        $secondId = $next?->uri_fragment;
        $contentBetween = $this->extractBetween->betweenIds($content, $firstId, $secondId);

        // extract the head from the original document so that we can add styles and links via PrepareContentHtml
        $fullContentCrawler = new DomCrawler($content);
        $head = $fullContentCrawler->filter('head');
        $headHtml = $head->count() > 0 ? $head->outerHtml() : '<head></head>';
        $crawler = new DomCrawler('<html>' . $headHtml . '<body>' . $contentBetween . '</body></html>');

        return $this->prepareContentHtml->cleanHtmlContent($crawler->outerHtml());
    }
}
