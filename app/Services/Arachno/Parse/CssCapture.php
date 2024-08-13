<?php

namespace App\Services\Arachno\Parse;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\ContentResource;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CssCapture
{
    public function __construct(
        protected Fetch $fetch,
        protected ContentResourceStore $contentResourceStore,
        protected CssProcessor $cssProcessor,
        protected UrlFrontierLinkManager $urlFrontierLinkManager,
    ) {
    }

    /**
     * @param PageCrawl     $pageCrawl
     * @param callable|null $postProcessCallable
     *
     * @return DomCrawler
     */
    public function capture(
        PageCrawl $pageCrawl,
        ?callable $postProcessCallable = null
    ): DomCrawler {
        $domCrawler = $pageCrawl->domCrawler;
        $this->followStylesheetLinks($pageCrawl, $postProcessCallable);
        $this->followStylesheetImports($pageCrawl);
        // inject any extra CSS into style tag in HEAD. Will be converted to a link in replaceStyles
        if ($css = $pageCrawl->getSettingCss()) {
            $this->injectCss($domCrawler, $css);
        }
        $this->replaceStyles($domCrawler, $pageCrawl->getSettingExcludedCssSelectors());

        /** @var DOMElement */
        $bodyNode = $domCrawler->filter('body')->getNode(0);
        $class = $bodyNode->getAttribute('class');
        $class = $class ? $class . ' ' : '';
        $bodyNode->setAttribute('class', $class . CssProcessor::CSS_LIBRYO_FULL_TEXT_CLASS);

        return $domCrawler;
    }

    /**
     * @param PageCrawl     $pageCrawl
     * @param callable|null $postProcessCallable
     *
     * @return void
     */
    public function followStylesheetLinks(
        PageCrawl $pageCrawl,
        ?callable $postProcessCallable = null
    ): void {
        $pageUrl = $pageCrawl->pageUrl;
        $domCrawler = $pageCrawl->domCrawler;

        $links = $domCrawler->filterXPath('//*[local-name()="head"]//*[local-name()="link" and @rel="stylesheet"]/@href');

        foreach ($links as $href) {
            $tmpUrl = new UrlFrontierLink([
                'crawl_id' => $pageUrl->crawl_id,
                'needs_browser' => false,
                'url' => $pageCrawl->getFinalRedirectedUrl(),
            ]);
            $tmpUrl->crawl()->associate($pageUrl->crawl);
            $newPageCrawl = new PageCrawl($pageCrawl->domCrawler, $tmpUrl, $pageCrawl->crawlerSettings);
            $contentCache = $this->fetch->fetchFromCacheOrUrl($newPageCrawl, $href->textContent);
            if (!$contentCache) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $css = $contentCache->response_body ?? '';
            if (trim($css) === '') {
                // @codeCoverageIgnoreStart
                $href->parentNode?->removeChild($href);
                continue;
                // @codeCoverageIgnoreEnd
            }
            $processedCss = $this->getProcessedCss(
                $contentCache,
                $pageCrawl->getSettingExcludedCssSelectors(),
                $postProcessCallable
            );

            $resource = $this->contentResourceStore->storeResource($processedCss, 'text/css');

            /** @var DOMElement */
            $parent = $href->parentNode;
            $parent->setAttribute('href', $this->contentResourceStore->getLinkForResource($resource));
        }
    }

    /**
     * Follows all stylsheet links, adds to the resources table and replaces href with new resource link.
     *
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function followStylesheetImports(PageCrawl $pageCrawl): void
    {
        $domCrawler = $pageCrawl->domCrawler;

        $stylesheets = $domCrawler->filter('style');
        $links = [];
        foreach ($stylesheets as $stylesheet) {
            $css = $stylesheet->textContent;
            if (preg_match_all('/@import\s*\'{0,1}\"{0,1}(.*?)\'{0,1}\"{0,1};/', $css, $matches) > 0) {
                foreach ($matches[1] as $link) {
                    $links[] = $link;
                }
            }
        }

        foreach ($links as $href) {
            $this->importToLink($href, $pageCrawl);
        }
    }

    /**
     * @param string             $href
     * @param PageCrawl          $pageCrawl
     * @param array<string>      $excludedSelectors
     * @param callable|null|null $postProcessCallable
     *
     * @return void
     */
    protected function importToLink(
        string $href,
        PageCrawl $pageCrawl,
        array $excludedSelectors = [],
        ?callable $postProcessCallable = null
    ): void {
        $domCrawler = $pageCrawl->domCrawler;

        if (!$resource = $this->fetchAndStoreResource($href, $pageCrawl, $excludedSelectors, $postProcessCallable)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var DOMElement */
        $head = $domCrawler->filter('head')->getNode(0);
        $lnk = $head->ownerDocument?->createElement('link');
        if (is_null($lnk)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $lnk->setAttribute('href', $this->contentResourceStore->getLinkForResource($resource));
        $lnk->setAttribute('rel', 'stylesheet');
        $head->appendChild($lnk);
    }

    /**
     * @param string             $href
     * @param PageCrawl          $pageCrawl
     * @param array<string>      $excludedSelectors
     * @param callable|null|null $postProcessCallable
     *
     * @return ContentResource|null
     */
    protected function fetchAndStoreResource(
        string $href,
        PageCrawl $pageCrawl,
        array $excludedSelectors = [],
        ?callable $postProcessCallable = null
    ): ?ContentResource {
        $contentCache = $this->fetch->fetchFromCacheOrUrl($pageCrawl, $href);
        if (!$contentCache) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        $processedCss = $this->getProcessedCss($contentCache, $excludedSelectors, $postProcessCallable);

        return $this->contentResourceStore->storeResource($processedCss, 'text/css');
    }

    /**
     * Get processed css from content cache if it's already been processed before.
     *
     * @param ContentCache       $contentCache
     * @param array<string>      $excludedSelectors
     * @param callable|null|null $postProcessCallable
     *
     * @return string
     */
    private function getProcessedCss(
        ContentCache $contentCache,
        array $excludedSelectors = [],
        ?callable $postProcessCallable = null
    ): string {
        $css = $contentCache->response_body ?? '';
        if (is_null($contentCache->processed_content)) {
            $processedCss = $this->cssProcessor->process($css, $excludedSelectors);

            if (!is_null($postProcessCallable)) {
                $processedCss = call_user_func($postProcessCallable, $processedCss);
            }

            $contentCache->processed_content = $processedCss;
            $contentCache->save();

            return $processedCss;
        }

        // @codeCoverageIgnoreStart
        return $contentCache->processed_content;
        // @codeCoverageIgnoreEnd
    }

    /**
     * If the crawler config or the source has CSS it gets injected into a style tag in the HEAD.
     *
     * @param DomCrawler $domCrawler
     * @param string     $css
     *
     * @return DomCrawler
     */
    public function injectCss(
        DomCrawler $domCrawler,
        string $css
    ): DomCrawler {
        /** @var DOMElement */
        $headNode = $domCrawler->filter('head')->getNode(0);
        $newStyle = $headNode->ownerDocument?->createElement('style', $css);
        if (is_null($newStyle)) {
            // @codeCoverageIgnoreStart
            return $domCrawler;
            // @codeCoverageIgnoreEnd
        }
        $headNode->appendChild($newStyle);

        return $domCrawler;
    }

    /**
     * Replaces all style tags with links to css uploaded to resources.
     *
     * @param DomCrawler    $domCrawler
     * @param array<string> $excludedSelectors
     *
     * @return void
     */
    public function replaceStyles(
        DomCrawler $domCrawler,
        array $excludedSelectors = [],
    ): void {
        $styles = $domCrawler->filter('style');

        foreach ($styles as $stylesheet) {
            $css = $stylesheet->textContent;
            $processedCss = $this->cssProcessor->process($css, $excludedSelectors);
            $resource = $this->contentResourceStore->storeResource($processedCss, 'text/css');

            $this->addCssLink($domCrawler, $this->contentResourceStore->getLinkForResource($resource));

            $stylesheet->parentNode?->removeChild($stylesheet);
        }
    }

    /**
     * Adds a link tag to the HEAD of the given DOM.
     *
     * @param DomCrawler $domCrawler
     * @param string     $href
     *
     * @return DomCrawler
     */
    public function addCssLink(DomCrawler $domCrawler, string $href): DomCrawler
    {
        /** @var DOMElement */
        $headNode = $domCrawler->filter('head')->getNode(0);
        $el = $headNode->ownerDocument?->createElement('link');
        if (is_null($el)) {
            // @codeCoverageIgnoreStart
            return $domCrawler;
            // @codeCoverageIgnoreEnd
        }
        $el->setAttribute('href', $href);
        $el->setAttribute('type', 'text/css');
        $el->setAttribute('rel', 'stylesheet');
        $headNode->appendChild($el);

        return $domCrawler;
    }
}
