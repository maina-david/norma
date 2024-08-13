<?php

namespace App\Stores\Corpus;

use App\Models\Corpus\Doc;
use App\Models\Corpus\Keyword;
use App\Models\Ontology\LegalDomain;
use App\Services\Language\LanguageDetector;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class DocStore
{
    use AttachesDetaches;

    public function __construct(protected LanguageDetector $languageDetector)
    {
    }

    /**
     * @param Doc           $doc
     * @param array<string> $keywords
     *
     * @return void
     */
    public function createAndAttachKeywords(Doc $doc, array $keywords): void
    {
        $languageCode = $this->languageDetector->detect(implode(',', $keywords)) ?? LanguageDetector::DEFAULT_LANGUAGE;
        foreach ($keywords as $keyword) {
            $word = Keyword::firstOrCreateForUid([
                'label' => $keyword,
                'language_code' => $languageCode,
            ]);
            $this->attachRelations($doc, 'keywords', collect([$word]), ['score' => 1.0]);
        }
    }

    /**
     * @param Doc                     $doc
     * @param Collection<LegalDomain> $domains
     *
     * @return void
     */
    public function attachLegalDomains(Doc $doc, Collection $domains): void
    {
        $this->attachRelations($doc, 'legalDomains', $domains);
    }
}
