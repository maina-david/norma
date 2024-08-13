<?php

namespace App\Actions\Arachno\Doc;

use App\Actions\Corpus\Doc\LinkToWork;
use App\Actions\Corpus\TocItem\UpdateTocItemDocPositions;
use App\Actions\Corpus\Work\CreateFromDoc;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Services\Arachno\Frontier\DocHashGenerator;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleCrawlComplete
{
    use AsAction;

    public function __construct(
        protected DocHashGenerator $docHashGenerator,
        protected LinkToWork $linkToWork,
        protected UpdateTocItemDocPositions $updateTocItemDocPositions,
    ) {
    }

    public function handle(int $docId): void
    {
        /** @var Doc $doc */
        $doc = Doc::findOrFail($docId);

        $this->setTocContentResource($doc);
        $this->updateTocDocPositions($doc);
        $this->setFirstContentResource($doc);
        $this->docHashGenerator->setHash($doc);

        $this->linkToWork($doc);

        $this->stopFetchStarted($doc);

        app(PopulateDocRequirementScores::class)->handle($docId);
    }

    /**
     * @param Doc|CatalogueDoc $doc
     *
     * @return void
     */
    public function stopFetchStarted(Doc|CatalogueDoc $doc): void
    {
        CatalogueDoc::where('uid', $doc->uid)
            ->whereNotNull('fetch_started_at')
            ->update(['fetch_started_at' => null]);
    }

    /**
     * @param Doc $doc
     *
     * @return void
     */
    protected function linkToWork(Doc $doc): void
    {
        // if there's already a linked work for the doc, and the content hash has changed,
        // link the new version to the work automatically
        /** @var Work|null */
        $work = Work::where('uid', $doc->uid)->first();
        if ($work && !$work->docs()->where('version_hash', $doc->version_hash)->exists()) {
            $this->linkToWork->handle($doc, $work);
        }

        // if no work exists yet, but there's a catalogue doc, then it was triggered for fetching from source,
        // and then we will also automatically create a work
        if (!$doc->for_update
             && !$work
             && CatalogueDoc::where('uid', $doc->uid)
                 ->whereNotNull('fetch_started_at')
                 ->exists()
        ) {
            CreateFromDoc::dispatch($doc->id);
        }
    }

    /**
     * @param Doc $doc
     *
     * @return void
     */
    protected function setTocContentResource(Doc $doc): void
    {
        $doc->tocItems()
            ->whereNull('content_resource_id')
            ->with(['link', 'link.contentResources'])
            ->chunkById(200, function ($items) {
                /** @var Collection<TocItem> $items */
                foreach ($items as $item) {
                    /** @var ContentResource|null */
                    $resource = $item->link?->contentResources->last();
                    $item->content_resource_id = $resource?->id;
                    $item->save();
                }
            });
    }

    /**
     * @param Doc $doc
     *
     * @return void
     */
    protected function updateTocDocPositions(Doc $doc): void
    {
        $this->updateTocItemDocPositions->handle($doc);
    }

    /**
     * @param Doc $doc
     *
     * @return void
     */
    protected function setFirstContentResource(Doc $doc): void
    {
        /** @var TocItem|null */
        $tocItem = $doc->tocItems()
            ->where('position', 0)
            ->with(['contentResource'])
            ->first();

        if ($tocItem) {
            /** @var ContentResource|null */
            $resource = $tocItem->contentResource;
        } else {
            $link = $doc->startLink;
            /** @var ContentResource|null */
            $resource = $link?->contentResources->last();
        }

        $doc->first_content_resource_id = $resource?->id;
        $doc->save();
    }
}
