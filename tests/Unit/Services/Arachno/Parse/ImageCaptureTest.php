<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\ContentResource;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\ImageCapture;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class ImageCaptureTest extends AbstractDomParseTests
{
    use DatabaseTransactions;

    public function testImageCapture(): void
    {
        $this->prep();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make();

        $resourceBody = '...';
        // so it doesn't try fetch the URL
        /** @var ContentCache */
        $cache = ContentCache::factory([
            'url' => 'http://example.com/test.jpg',
            'response_body' => $resourceBody,
            'response_headers' => ['content-type' => ['image/jpeg; utf-8']],
        ])->create();

        $cache->crawl_id = $this->crawl?->id;
        $cache->setUid();
        $cache->save();
        $pageUrl->crawl_id = $this->crawl?->id;
        $pageUrl->url = 'http://example.com';
        $pageUrl->save();
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);
        $capturedDom = app(ImageCapture::class)->capture($pageCrawl);

        $testCrawler = new Crawler($this->html);
        $this->assertTrue(
            count($testCrawler->filter('body img')) === count($capturedDom->filter('body img')),
            'Failed asserting that the same images are present'
        );
        /** @var DOMElement */
        $n = $capturedDom->filter('img[alt="Red dot"]')->getNode(0);
        $str = $n->getAttribute('src');
        $this->assertTrue(
            Str::startsWith($str, 'data:'),
            'Failed asserting that the base64 images were untouched'
        );

        /** @var DOMElement */
        $n = $capturedDom->filter('body img')->getNode(0);
        $firstImageSrc = $n->getAttribute('src');
        $this->assertTrue(
            Str::startsWith($firstImageSrc, ContentResourceStore::PATH_PREFIX),
            'Failed asserting that image src has been changed'
        );
        $this->assertTrue(
            ContentResource::where('path', $firstImageSrc)->exists(),
            'Failed asserting that content resource exists for image'
        );

        $this->assertTrue(ContentResource::where('uid', ContentResource::hashForDB($resourceBody))->exists());
    }
}
