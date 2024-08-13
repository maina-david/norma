<?php

namespace App\Services\Arachno\Parse;

use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Storage\MimeTypeManager;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class ImageCapture
{
    public function __construct(
        protected Fetch $fetch,
        protected ContentResourceStore $contentResourceStore,
        protected CssProcessor $cssProcessor,
        protected MimeTypeManager $mimeTypeManager,
    ) {
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return DomCrawler
     */
    public function capture(PageCrawl $pageCrawl): DomCrawler
    {
        $this->followImageLinks($pageCrawl);

        return $pageCrawl->domCrawler;
    }

    public function followImageLinks(PageCrawl $pageCrawl): void
    {
        $images = $pageCrawl->domCrawler->filterXPath('//*[local-name()="body"]//*[local-name()="img"]/@src');

        foreach ($images as $src) {
            // leave base64 encoded images as is
            if (Str::of($src->textContent)->startsWith('data:')) {
                continue;
            }
            $options = [
                'headers' => [
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*',
                ],
            ];
            $contentCache = $this->fetch->fetchFromCacheOrUrl($pageCrawl, $src->textContent, $options);
            if (!$contentCache) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $imgData = $contentCache->response_body ?? '';
            $mimeType = $this->mimeTypeManager->extractFromContentCache($contentCache);
            $resource = $this->contentResourceStore->storeResource($imgData, $mimeType ?? 'image/*');

            /** @var DOMElement */
            $parentNode = $src->parentNode;
            $parentNode->setAttribute('src', $this->contentResourceStore->getLinkForResource($resource));
        }
    }
}
