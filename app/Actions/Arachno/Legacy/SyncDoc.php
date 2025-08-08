<?php

namespace App\Actions\Arachno\Legacy;

use App\Models\Arachno\Link;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Ontology\WorkType;
use App\Services\Storage\WorkStorageProcessor;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use Throwable;

class SyncDoc implements ShouldBeUnique
{
    use AsAction;

    /** @var array<int, Link> */
    protected array $volumeLinks = [];

    /** @var array<int> */
    protected array $volumeResources = [];

    /** @var array<int, int> */
    protected array $refTocIds = [];

    public function __construct(
        protected WorkStorageProcessor $workStorageProcessor,
        protected ContentResourceStore $contentResourceStore
    ) {
    }

    public function handle(WorkExpression $expression): void
    {
        $expression->load(['work.source']);
        $doc = $this->createDoc($expression->work);
        $links = $this->createContentAndLinks($expression, $doc);
        $this->createTocItems($expression->work, $doc);
    }

    public function getJobUniqueId(WorkExpression $expression): string
    {
        return get_class($this) . '_' . $expression->id;
    }

    public function getJobUniqueFor(WorkExpression $expression): int
    {
        return 600;
    }

    public function configureJob(JobDecorator $job): void
    {
        $job->onQueue('long-running');
    }

    public function asJob(WorkExpression $expression): void
    {
        $this->handle($expression);
    }

    protected function createDoc(Work $work): Doc
    {
        /** @var WorkType|null */
        $workType = WorkType::where('slug', $work->work_type)->first();

        $doc = new Doc([
            'work_id' => $work->id,
            'source_unique_id' => $work->source_unique_id ?? 'norma_work_' . $work->id,
            'title' => $work->title,
            'source_id' => $work->source_id,
            'primary_location_id' => $work->primary_location_id,
        ]);
        $doc->source()->associate($work->source);
        $doc->setUid();
        $doc->save();
        $doc->docMeta->update([
            'language_code' => $work->language_code,
            'work_type_id' => $workType->id ?? null,
            'source_url' => $work->source_url,
            'work_number' => $work->work_number,
            'publication_number' => $work->notice_number,
            'publication_document_number' => $work->gazette_number,
            'work_date' => $work->work_date,
            'effective_date' => $work->effective_date,
        ]);

        return $doc;
    }

    /**
     * @param WorkExpression $expression
     * @param Doc            $doc
     *
     * @return Collection<Link>
     */
    protected function createContentAndLinks(WorkExpression $expression, Doc $doc): Collection
    {
        /** @var Collection<Link> */
        $links = (new Link())->newCollection();
        for ($v = 1; $v <= $expression->volume; $v++) {
            $url = $this->getVolumeLink($doc, $v);
            $uid = Link::hashForDB($url);
            /** @var Link|null $link */
            $link = Link::where('uid', $uid)->first();
            if (is_null($link)) {
                $link = new Link([
                    'url' => $url,
                ]);
                $link->setUid();
                $link->save();
            }
            $content = $this->workStorageProcessor->getCachedWorkVolume($expression->work, $v);
            if ($content) {
                $content = $this->replaceIdsInContent($content);
                $resource = $this->contentResourceStore->storeResource($content, 'text/html');
                try {
                    $link->contentResources()->attach($resource);
                    // @codeCoverageIgnoreStart
                } catch (Throwable $th) {
                }
                $this->volumeResources[$v] = $resource->id;
                // @codeCoverageIgnoreEnd
            }
            $this->volumeLinks[$v] = $link;
            $links->add($link);
        }

        /** @var Collection<Link> */
        return $links;
    }

    protected function createTocItems(Work $work, Doc $doc): void
    {
        $refIndex = 0;
        $chunkBy = 500;
        $work->references()->with(['citation', 'refPlainText'])->chunk($chunkBy, function ($references) use ($doc, &$refIndex) {
            foreach ($references as $index => $ref) {
                $link = $this->volumeLinks[$ref->volume] ?? $this->volumeLinks[1];
                /** @var TocItem */
                $newItem = TocItem::create([
                    'uid' => TocItem::hashForDB('reference_uid_' . $ref->id),
                    'source_unique_id' => $ref->id,
                    'label' => $ref->refPlainText->plain_text ?? '',
                    'doc_id' => $doc->id,
                    'link_id' => $link->id,
                    'content_resource_id' => $this->volumeResources[$ref->volume] ?? null,
                    'uri_fragment' => 'id_' . $ref->id,
                    'parent_id' => $ref->parent_id ? ($this->refTocIds[$ref->parent_id] ?? null) : null,
                    'level' => $ref->level,
                    'position' => null,
                    'doc_position' => $ref->position ?? $refIndex,
                ]);
                $this->refTocIds[(int) $ref->id] = (int) $newItem->id;
                $refIndex++;
            }
        });
    }

    public function replaceIdsInContent(string $content): string
    {
        return preg_replace('/ data\-id=\"([0-9]+)\"/', ' id="id_$1"', $content) ?? $content;
    }

    /**
     * @param Doc $doc
     * @param int $volume
     *
     * @return string
     */
    public function getVolumeLink(Doc $doc, int $volume): string
    {
        return 'https://my.norma.com/corpus/legacy/' . $doc->uid_hash . '/volume/' . $volume;
    }
}
