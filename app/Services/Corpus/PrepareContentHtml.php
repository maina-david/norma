<?php

namespace App\Services\Corpus;

use DOMDocument;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class PrepareContentHtml
{
    /**
     * Takes the given HTML document, moves the links and css into the <body> tag, and then only returns what's in the <body> tag.
     *
     * @param string $content
     *
     * @return string
     */
    public function cleanHtmlContent(string $content): string
    {
        // $enc = mb_detect_encoding($content);
        // $content = mb_convert_encoding($content, 'UTF-8', $enc);
        $content = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">', '', $content);
        $content = str_replace('charset=iso-8859-1', '', $content);

        if (str_contains($content, 'data-highlight-id')) {
            /** @var string */
            $content = preg_replace('/data-highlight-id="([0-9]+)"/', 'id="id_$1"', $content);
        }

        $crawler = new DomCrawler($content);
        $linksAndStyles = $crawler->filter('head link, head style');
        // $headNode = $crawler->filter('head')->getNode(0);
        $bodyNode = $crawler->filter('body')->getNode(0);
        $innerHTML = '';
        if (!is_null($bodyNode)) {
            foreach ($linksAndStyles as $element) {
                $bodyNode->appendChild($element);
            }

            /** @var DOMDocument */
            $doc = $bodyNode->ownerDocument;
            foreach ($bodyNode->childNodes as $child) {
                $innerHTML .= $doc->saveHTML($child);
            }
        }

        return '<div class="norma-full-text">' . $innerHTML . '</div>';
    }
}
