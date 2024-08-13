<?php

namespace Tests\Unit\Services\Arachno\Frontier;

use App\Services\Arachno\Frontier\LinkToUri;
use Psr\Http\Message\UriInterface;
use Tests\TestCase;

class LinkToUriTest extends TestCase
{
    public function testConvert(): void
    {
        $host = 'http://example.com';
        $file = 'dist/css/style.css';
        $converted = app(LinkToUri::class)->convert($file, $host . '/some/path/to/a/page#urifragment');
        $this->assertInstanceOf(UriInterface::class, $converted);
        $this->assertSame($host . '/some/path/to/a/' . $file, (string) $converted);

        $converted = app(LinkToUri::class)->convert('/root.css', $host . '/some/path/to/a/page#urifragment');
        $this->assertInstanceOf(UriInterface::class, $converted);
        $this->assertSame($host . '/root.css', (string) $converted);

        $converted = app(LinkToUri::class)->convert('root.css', $host . '/some/path/to/a/page#urifragment');
        $this->assertInstanceOf(UriInterface::class, $converted);
        $this->assertSame($host . '/some/path/to/a/root.css', (string) $converted);

        $converted = app(LinkToUri::class)->convert('../../root.css', $host . '/some/path/to/a/page#urifragment');
        $this->assertInstanceOf(UriInterface::class, $converted);
        $this->assertSame($host . '/some/path/root.css', (string) $converted);
    }
}
