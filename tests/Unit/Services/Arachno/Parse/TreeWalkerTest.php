<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Services\Arachno\Parse\TreeWalker;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class TreeWalkerTest extends TestCase
{
    public function testWalk(): void
    {
        $html = file_get_contents('./tests/Unit/Services/Arachno/Parse/test-data.html');
        $crawler = new Crawler($html);

        $nodes = [];
        foreach (app(TreeWalker::class)->walk($crawler->filter('#toc')->getNode(0)) as $n) {
            $nodes[] = $n;
        }

        $this->assertTrue($nodes[0]['depth'] === 0);
        $this->assertTrue($nodes[0]['node']->getAttribute('id') === 'toc');
        $this->assertTrue($nodes[1]['node']->nodeName === 'ul');
        $this->assertTrue($nodes[1]['depth'] === 1);
        $this->assertTrue($nodes[2]['node']->nodeName === 'li');
        $this->assertTrue($nodes[2]['depth'] === 2);
    }
}
