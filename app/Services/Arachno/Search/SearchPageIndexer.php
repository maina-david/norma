<?php

namespace App\Services\Arachno\Search;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Enums\Arachno\Parse\PdfProperty;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\SearchPage;
use App\Models\Arachno\SearchPageMeta;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\DomQueryRunner;
use App\Services\Arachno\Parse\PdfExtractor;
use App\Services\Html\HtmlToText;
use App\Services\Language\LanguageDetector;
use App\Services\Storage\MimeTypeManager;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Meilisearch\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;

class SearchPageIndexer
{
    public function __construct(
        protected MimeTypeManager $mimeTypeManager,
        protected PdfExtractor $pdfExtractor,
        protected LanguageDetector $languageDetector,
        protected HtmlToText $htmlToText,
        protected DomQueryRunner $domQueryRunner,
    ) {
    }

    /**
     * @param int           $searchPageId
     * @param int           $contentCacheId
     * @param DomQuery|null $contentQuery
     *
     * @return void
     */
    public function indexSearchPage(
        int $searchPageId,
        int $contentCacheId,
        ?DomQuery $contentQuery = null,
    ): void {
        /** @var ContentCache|null */
        $contentCache = ContentCache::find($contentCacheId);
        /** @var SearchPage|null */
        $searchPage = SearchPage::find($searchPageId);
        if (!$contentCache || !$searchPage) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var SearchPageMeta */
        $meta = $searchPage->searchPageMeta;

        $mimeType = $this->mimeTypeManager->extractFromHeaders($contentCache->response_headers ?? []) ?? 'text/html';
        $meta->mime_type = $mimeType;
        if ($this->mimeTypeManager->isPdf($mimeType)) {
            $text = $this->pdfExtractor->getText($contentCache->response_body ?? '');
        } else {
            $domCrawler = new DomCrawler($contentCache->response_body ?? '');
            $contentQuery = $contentQuery ?? new DomQuery(['query' => 'body', 'type' => DomQueryType::CSS]);
            $domCrawler = $this->domQueryRunner->runQuery($contentQuery, $domCrawler);
            $text = $this->htmlToText->convert($domCrawler->outerHtml());
        }

        $meta->language_code = $meta->language_code ?? $this->languageDetector->detect($text);
        $meta->metadata = $this->getMetaData($contentCache);

        $meta->save();

        $this->index($searchPage, $text);
    }

    /**
     * @param ContentCache $contentCache
     *
     * @return array<string, mixed>
     */
    protected function getMetaData(ContentCache $contentCache): array
    {
        $mimeType = $this->mimeTypeManager->extractFromHeaders($contentCache->response_headers ?? []) ?? 'text/html';
        if ($this->mimeTypeManager->isPdf($mimeType)) {
            $title = $this->pdfExtractor->getProperty(PdfProperty::TITLE, $contentCache->response_body ?? '');
        } else {
            $domCrawler = new DomCrawler($contentCache->response_body ?? '');
            $title = $domCrawler->filter('title')->getNode(0)?->textContent ?? '';
        }

        return [
            'title' => trim($title),
        ];
    }

    protected function index(SearchPage $searchPage, string $text): void
    {
        $url = $searchPage->url ?? '';
        $uri = new Uri($url);
        $host = $uri->getHost();
        $path = $uri->getPath();
        $title = $searchPage->searchPageMeta?->metadata['title'] ?? null;
        if (!$title && !$text) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $doc = [
            'id' => $searchPage->id,
            'source_id' => $searchPage->source_id,
            'primary_location_id' => $searchPage->searchPageMeta?->primary_location_id,
            'url' => $url,
            'host' => $host,
            'path' => $path,
            'text' => $text,
            'language_code' => $searchPage->searchPageMeta?->language_code,
            'title' => $title,
        ];
        $useChunker = $this->mimeTypeManager->isPdf($searchPage->searchPageMeta?->mime_type ?? '');
        $this->indexDocument($doc, $useChunker);
    }

    /**
     * @param array<string,mixed> $document
     * @param bool                $useChunker
     *
     * @return void
     */
    protected function indexDocument(array $document, bool $useChunker): void
    {
        // $this->indexDocumentWithMeilisearch($document);
        $this->indexDocumentWithMagi($document, $useChunker);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param array<string,mixed> $document
     *
     * @return void
     */
    protected function indexDocumentWithMeilisearch(array $document): void
    {
        $client = new Client(config('scout.meilisearch.host'));
        $index = $client->index('arachno_search');

        $index->addDocuments([$document], 'id');
    }

    /**
     * @codeCoverageIgnore
     *
     * @param array<string,mixed> $document
     * @param bool                $useChunker
     *
     * @return Response|null
     */
    protected function indexDocumentWithMagi(array $document, bool $useChunker): ?Response
    {
        /** @var string $endpoint */
        $endpoint = config('services.norma_ai.host');

        /** @var bool $enabled */
        $enabled = config('services.norma_ai.enabled');
        if (!$enabled) {
            return null;
        }

        $data = [
            'page_id' => $document['id'],
            'source_id' => $document['source_id'],
            'page_url' => $document['url'],
            'url_host' => $document['host'],
            'url_path' => $document['path'],
            'title' => $document['title'],
            'text' => $document['text'],
            'language_code' => $document['language_code'],
            'primary_location_id' => [$document['primary_location_id']],
            'use_chunker' => $useChunker,
        ];

        try {
            return Http::baseUrl($endpoint)
                ->asJson()
                ->acceptJson()
                ->post('norma-ai/pages', $data);
        } catch (Throwable $th) {
        }

        return null;
    }
}
