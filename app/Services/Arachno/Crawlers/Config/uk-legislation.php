<?php

/*
|--------------------------------------------------------------------------
| Slug: uk-legislation
| Title: UK Legislation
| URL: https://www.legislation.gov.uk
|--------------------------------------------------------------------------
*/

use App\Services\Arachno\Support\DomElementClass;
use Symfony\Component\DomCrawler\Crawler;

return array_merge(require ('default.php'), [
    'start_urls' => [
        [
            'url' => 'https://www.legislation.gov.uk/ukpga/2021/10/contents',
        ],
    ],
    'follow_links_queries' => [],
    'capture_queries' => [
        [
            'is_capture_page_query' => [
                'type' => 'css',
                'query' => 'body#leg #content .contentFooter .prevNextNav',
            ],
            'capture_body_queries' => [
                [
                    'type' => 'css',
                    'query' => 'body#leg #content #viewLegContents',
                ],
            ],
            'capture_head_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//head//title',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//head//link[(not(@media) or @media="screen") and @rel="stylesheet"]',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//head//style',
                ],
            ],
            'capture_remove_queries' => [],
        ],
    ],
    'is_meta_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/^https:\/\/www\.legislation\.gov\.uk\/.+\/.+\/.+\/contents/',
    ],
    'unique_id_query' => [
        'type' => 'text',
        'source' => 'url',
        'query' => '/^https:\/\/www\.legislation\.gov\.uk(.+)\/contents/',
    ],
    'title_query' => [
        'type' => 'xpath',
        'query' => '//head//title',
    ],
    'is_toc_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/^https:\/\/www\.legislation\.gov\.uk\/.+\/.+\/.+\/contents/',
    ],
    'toc_item_criteria' => function ($nodeDTO) {
        return strtolower($nodeDTO['node']->nodeName) === 'li';
    },
    'toc_item_extract_label' => function ($nodeDTO) {
        $n = $nodeDTO['node'];
        if (DomElementClass::hasClassLike($n, 'tocDefault*') || DomElementClass::hasClass($n, ['LegContentsPblock', 'LegContentsSchedules'])) {
            $components = [];
            foreach ($n->childNodes as $chNode) {
                if (strtolower($chNode->nodeName) === 'p') {
                    $crawler = new Crawler($chNode);
                    foreach ($crawler->filter('.LegContentsNo a, .LegContentsTitle a, .LegContentsHeading') as $el) {
                        $components[] = $el->textContent;
                    }

                    return trim(implode(' ', $components));
                }
            }
        } else {
            $crawler = new Crawler($n);
            $components = [];
            foreach ($crawler->filter('p.LegContentsItem > span.LegContentsNo a, p.LegContentsItem > span.LegContentsTitle a') as $el) {
                $components[] = $el->textContent;
            }

            return trim(implode(' ', $components));
        }
    },
    'toc_item_extract_link' => function ($nodeDTO) {
        $n = $nodeDTO['node'];
        $crawler = new Crawler($n);
        if (DomElementClass::hasClassLike($n, 'tocDefault*') || DomElementClass::hasClass($n, ['LegContentsPblock', 'LegContentsSchedules'])) {
            $el = $crawler->filter('p.LegContentsNo a,p.LegContentsTitle a')->first();

            /** @var \DOMElement */
            $element = $el->getNode(0);

            return $element->getAttribute('href');
        }
        $el = $crawler->filter('p.LegContentsItem span.LegContentsNo a, p.LegContentsItem span.LegContentsTitle a')->first();

        /** @var \DOMElement */
        $element = $el->getNode(0);

        return $element->getAttribute('href');
    },
    'toc_query' => [
        'type' => 'css',
        'query' => 'body#leg #content #viewLegContents .LegContents > ol',
    ],
]);
