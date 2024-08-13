<?php

namespace Tests\Unit\Services\Arachno\Support;

use App\Services\Arachno\Support\DomClosest;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class DomClosestTest extends TestCase
{
    public function testClosest(): void
    {
        $dom = new Crawler('<html><body><div><p class="findthis"><span><a href=""></a></span></p></div></body></html>');

        $link = $dom->filter('a')->getNode(0);
        $result = app(DomClosest::class)->closest($link, 'div');
        $this->assertInstanceOf(Crawler::class, $result);

        $result = app(DomClosest::class)->closest($link, '.findthis');
        $this->assertInstanceOf(Crawler::class, $result);

        $result = app(DomClosest::class)->closest($link, '.doesntexist');
        $this->assertNull($result);
    }
}
