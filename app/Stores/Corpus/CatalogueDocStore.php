<?php

namespace App\Stores\Corpus;

use App\Models\Arachno\Crawler;
use App\Models\Arachno\SourceCategory;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Parse\DocMetaSaver;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class CatalogueDocStore
{
    use AttachesDetaches;

    public function __construct(protected DocMetaSaver $docMetaSaver)
    {
    }

    /**
     * @param CatalogueDoc $catalogueDoc
     * @param Crawler      $crawler
     *
     * @return CatalogueDoc
     */
    public function create(
        CatalogueDoc $catalogueDoc,
        Crawler $crawler,
    ): CatalogueDoc {
        if (is_null($catalogueDoc->source_unique_id)) {
            throw new RuntimeException('source_unique_id is required for ' . ($catalogueDoc->start_url ?? ''));
        }
        if (is_null($catalogueDoc->start_url)) {
            throw new RuntimeException('start_url is required for ' . ($catalogueDoc->title ?? ''));
        }
        if (!$catalogueDoc->language_code) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('language_code is required for ' . ($catalogueDoc->title ?? ''));
            // @codeCoverageIgnoreEnd
        }

        $catalogueDoc->source_id = $crawler->source_id;
        $catalogueDoc->crawler_id = $crawler->id;
        $catalogueDoc->setUid();

        /** @var CatalogueDoc|null */
        $catDoc = CatalogueDoc::where('uid', $catalogueDoc->uid)->first();
        if ($catDoc) {
            // @codeCoverageIgnoreStart
            $catDoc->fill($catalogueDoc->toArray());
        // @codeCoverageIgnoreEnd
        } else {
            if ($catalogueDoc->title) {
                $catalogueDoc->title_translation = $this->docMetaSaver->getTitleTranslation(
                    $catalogueDoc->title,
                    $catalogueDoc->language_code
                );
            }
            $catDoc = $catalogueDoc;
        }

        $catDoc->save();

        return $catDoc;
    }

    /**
     * @param CatalogueDoc               $catalogueDoc
     * @param Collection<SourceCategory> $collection
     *
     * @return void
     */
    public function attachSourceCategories(CatalogueDoc $catalogueDoc, Collection $collection): void
    {
        $this->attachRelations($catalogueDoc, 'sourceCategories', $collection);
    }
}
