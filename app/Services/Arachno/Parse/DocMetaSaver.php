<?php

namespace App\Services\Arachno\Parse;

use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\WorkType;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Language\LanguageDetector;
use App\Services\Translation\TranslatorServiceInterface;
use App\Stores\Arachno\LinkStore;
use App\Stores\Corpus\ContentResourceStore;
use App\Stores\Corpus\DocStore;
use App\Support\Languages;
use Carbon\Exceptions\InvalidFormatException;
use DOMNode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class DocMetaSaver
{
    public function __construct(
        // protected DomQueryRunner $domQueryRunner,
        protected LanguageDetector $languageDetector,
        protected LinkStore $linkStore,
        protected PdfExtractor $pdfExtractor,
        protected DocStore $docStore,
        protected TranslatorServiceInterface $translatorService,
        protected ContentResourceStore $contentResourceStore,
    ) {
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $property
     * @param mixed     $value
     *
     * @return Doc
     */
    public function setDocMetaProperty(PageCrawl $pageCrawl, string $property, $value)
    {
        $doc = $pageCrawl->getDoc();
        if (!$doc && $property !== 'source_unique_id') {
            throw new RuntimeException('source_unique_id needs to be set first');
        }
        $pageUrl = $pageCrawl->pageUrl;
        $redirectedUrl = $pageCrawl->getFinalRedirectedUrl();
        $source = $pageUrl->crawl?->crawler?->source;
        if (!$source) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('No Source found for crawler when parsing url: ' . $redirectedUrl);
            // @codeCoverageIgnoreEnd
        }
        if (!$doc) {
            $doc = $this->createInitialDoc($pageCrawl, $source, $value);
        }

        if ($property === 'primary_location') {
            $doc->primary_location_id = $value ? $this->getLocationFromText($value)?->id : null;
        }

        if ($property === 'language_code') {
            $doc->docMeta->language_code = $value ?: $this->detectLanguageCode($pageCrawl);
        }
        if ($property === 'title') {
            if (!$doc->docMeta->language_code) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException('Please set the language_code first, so we can detect whether the title needs to be translated');
                // @codeCoverageIgnoreEnd
            }
            $doc->title = trim($value);
            if (is_null($doc->docMeta->title_translation)) {
                $doc->docMeta->title_translation = $value ? $this->getTitleTranslation($doc->title, $doc->docMeta->language_code ?? 'eng') : null;
            }
        }
        if ($property === 'work_type') {
            $doc->docMeta->work_type_id = $value ? $this->getWorkTypeByText($value)?->id : null;
        }
        if ($property === 'source_url') {
            $sourceUrl = $value ? substr(trim($value), 0, 999) : ($pageUrl->url ? substr($pageUrl->url, 0, 999) : null);
            $doc->docMeta->source_url = $sourceUrl ?? '';
        }
        if ($property === 'download_url') {
            $doc->docMeta->download_url = $value ? substr(trim($value), 0, 999) : null;
        }
        if ($property === 'work_number') {
            $doc->docMeta->work_number = $value ? trim($value) : null;
        }
        if ($property === 'publication_number') {
            $doc->docMeta->publication_number = $value ? trim($value) : null;
        }
        if ($property === 'publication_document_number') {
            $doc->docMeta->publication_document_number = $value ? trim($value) : null;
        }
        if ($property === 'work_date') {
            $doc->docMeta->work_date = $value ? $this->extractDate($value)?->format('Y-m-d') : null;
        }
        if ($property === 'effective_date') {
            $doc->docMeta->effective_date = $value ? $this->extractDate($value)?->format('Y-m-d') : null;
        }
        if ($property === 'summary' && $value) {
            $summaryResource = null;
            $summaryResource = $this->contentResourceStore->storeResource($value, 'text/plain');

            $trimmedSummary = mb_strlen(trim($value)) > 255 ? mb_substr(trim($value), 0, 250) . '...' : trim($value);
            $doc->docMeta->summary = $trimmedSummary ?: null;
            $doc->docMeta->summary_content_resource_id = $summaryResource->id ?? null;
        }

        if ($property === 'keywords' && !empty($value)) {
            $keywords = Arr::map($value, fn (string $word) => trim($word));
            $this->docStore->createAndAttachKeywords($doc, $keywords);
        }

        if ($property === 'legal_domains' && !empty($value)) {
            /** @var Collection<LegalDomain> */
            $domains = LegalDomain::whereKey($value)->get();
            $this->docStore->attachLegalDomains($doc, $domains);
        }

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param Source    $source
     * @param string    $sourceUniqueId
     *
     * @return Doc
     */
    protected function createInitialDoc(
        PageCrawl $pageCrawl,
        Source $source,
        string $sourceUniqueId
    ): Doc {
        $pageUrl = $pageCrawl->pageUrl;
        $doc = new Doc([
            'source_unique_id' => $sourceUniqueId,
            'source_id' => $source->id,
        ]);
        $doc->setUid();
        $doc->source_id = $source->id;
        $doc->crawl_id = $pageUrl->crawl_id;
        $doc->crawler_id = $pageUrl->crawl?->crawler_id;
        $doc->delete_if_unused = $pageCrawl->getSettingDeleteIfUnused();
        $doc->for_update = $pageCrawl->getSettingForUpdate();
        $doc->save();
        $pageCrawl->setDoc($doc);
        if (!$doc->start_link_id) {
            $doc->start_link_id = $this->linkStore->firstOrCreateFromUri($pageUrl->url)->id;
        }

        $doc->source()->associate($source);

        $frontierMeta = $pageCrawl->getFrontierMeta();
        // need to set the language code first so we know whether title needs to be translated
        if (isset($frontierMeta['language_code'])) {
            $this->setDocMetaProperty($pageCrawl, 'language_code', $frontierMeta['language_code']);
        // @codeCoverageIgnoreStart
        } elseif (isset($frontierMeta['title'])) {
            // default to english if not set
            $this->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
            // @codeCoverageIgnoreEnd
        }
        foreach ($pageCrawl->getFrontierMeta() as $property => $value) {
            if ($property === 'language_code') {
                continue;
            }
            $this->setDocMetaProperty($pageCrawl, $property, $value);
        }

        return $doc;
    }

    private function getLocationFromText(string $text): ?Location
    {
        $location = null;
        $text = trim($text);
        if (!is_numeric($text)) {
            $location = Location::where('title', $text)
                ->orWhere('slug', $text)
                ->orWhere('geoname_id', $text)
                ->first();
        } else {
            $location = Location::find((int) $text);
        }

        /** @var Location|null */
        return $location;
    }

    /**
     * @param string $title
     * @param string $languageCode
     *
     * @return string|null
     */
    public function getTitleTranslation(string $title, string $languageCode): ?string
    {
        if ($languageCode === 'eng') {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $fromLanguage = Languages::$alpha3To2[$languageCode] ?? null;
        if (!$fromLanguage) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        try {
            return $this->translatorService->translate($title, 'en', $fromLanguage);
            // @codeCoverageIgnoreStart
        } catch (Throwable $th) {
        }

        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return string|null
     */
    public function detectLanguageCode(PageCrawl $pageCrawl): ?string
    {
        if ($pageCrawl->isPdf()) {
            if (is_null($pageCrawl->contentCache)) {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }

            $text = $this->pdfExtractor->getText($pageCrawl->contentCache->response_body ?? '');

            return $this->languageDetector->detect($text);
        }
        $domCrawler = $pageCrawl->domCrawler;

        /** @var DOMNode */
        $n = $domCrawler->filter('body')->getNode(0);
        $text = $n->textContent;

        return $this->languageDetector->detect($text);
    }

    /**
     * @param string $text
     *
     * @return WorkType|null
     */
    public function getWorkTypeByText(string $text): ?WorkType
    {
        $text = trim($text);
        if (is_numeric($text)) {
            /** @var WorkType|null */
            return WorkType::find((int) $text);
        }
        if (!$workType = WorkType::where('title', $text)->orWhere('slug', $text)->first()) {
            $workType = WorkType::where('slug', 'act')->first();
        }

        /** @var WorkType|null */
        return $workType;
    }

    /**
     * @param string $text
     *
     * @return Carbon|null
     */
    private function extractDate(string $text): ?Carbon
    {
        /** @var string */
        $text = str_replace(',', ' ', $text);
        $text = trim($text);
        try {
            $carbon = Carbon::parse($text);
            // @codeCoverageIgnoreStart
        } catch (InvalidFormatException $th) {
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $carbon;
    }
}
