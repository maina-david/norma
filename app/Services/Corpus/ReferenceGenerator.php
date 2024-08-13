<?php

namespace App\Services\Corpus;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use Illuminate\Support\Collection;

class ReferenceGenerator
{
    /** @var array<int, int> */
    protected array $itemToRefMap = [];

    public function __construct(
        protected TocItemContentExtractor $tocItemContentExtractor,
    ) {
    }

    public function forWork(Work $work, Doc $doc): void
    {
        $work->load([
            'references:id,uid,work_id',
            'references.refPlainText:reference_id,plain_text',
            'references.htmlContent:reference_id,cached_content',
        ]);
        /** @var Collection<TocItem> $tocItemsWithContent */
        $tocItemsWithContent = $this->tocItemContentExtractor->extract($doc);
        foreach ($tocItemsWithContent->chunk(100) as $chunk) {
            $items = TocItem::whereKey($chunk->modelKeys())->get()->keyBy('id');
            foreach ($chunk as $itemWithContent) {
                /** @var TocItem */
                $item = $items[$itemWithContent->id];
                $item->_content = $itemWithContent->_content;
                $this->generateReferenceFromItem($item, $work);
            }
        }
    }

    protected function generateReferenceFromItem(TocItem $tocItem, Work $work): void
    {
        /** @var Reference|null */
        $reference = $work->references->first(function ($ref) use ($tocItem) {
            /** @var Reference $ref */
            return $ref->uid_hash === $tocItem->uid_hash;
        });

        if ($reference) {
            // we update the position as new items might have been inserted
            $reference->position = $tocItem->doc_position;
            $reference->start = $tocItem->doc_position;
            $reference->save();

            if ($reference->refPlainText?->plain_text !== $tocItem->label) {
                $this->setLabel($tocItem, $reference);
            }

            if ($reference->htmlContent?->cached_content
                && sha1($reference->htmlContent->cached_content) === sha1($tocItem->_content ?? '')
            ) {
                // @codeCoverageIgnoreStart
                return;
                // @codeCoverageIgnoreEnd
            }
            $this->setContent($tocItem, $reference);

            return;
        }

        $reference = $this->createNew($tocItem, $work);
        $this->itemToRefMap[$tocItem->id] = $reference->id;
    }

    protected function createNew(TocItem $tocItem, Work $work): Reference
    {
        /** @var Reference */
        $reference = Reference::create([
            'uid' => $tocItem->uid,
            'work_id' => $work->id,
            'parent_id' => $tocItem->parent_id ? ($this->itemToRefMap[$tocItem->parent_id] ?? null) : null,
            'type' => ReferenceType::citation()->value,
            'level' => $tocItem->level,
            'position' => $tocItem->doc_position,
            'start' => $tocItem->doc_position,
            'toc_item_id' => $tocItem->id,
            'volume' => 1,
            'referenceable_type' => 'citations',
            'status' => ReferenceStatus::pending()->value,
        ]);

        $this->setLabel($tocItem, $reference);
        $this->setContent($tocItem, $reference);

        return $reference;
    }

    /**
     * @param TocItem   $tocItem
     * @param Reference $reference
     *
     * @return void
     */
    protected function setLabel(TocItem $tocItem, Reference $reference): void
    {
        $reference->refPlainText()->updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'plain_text' => $tocItem->label,
        ]);
    }

    /**
     * @param TocItem   $tocItem
     * @param Reference $reference
     *
     * @return void
     */
    protected function setContent(TocItem $tocItem, Reference $reference): void
    {
        $reference->htmlContent()->updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'cached_content' => $tocItem->_content,
        ]);
    }
}
