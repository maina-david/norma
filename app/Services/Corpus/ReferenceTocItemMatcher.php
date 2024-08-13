<?php

namespace App\Services\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Traits\CleansUpLabels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReferenceTocItemMatcher
{
    use CleansUpLabels;

    /**
     * Attempt to match ToC Items to References.
     *
     * @param int $workId
     *
     * @return void
     */
    public function attempt(int $workId): void
    {
        $work = Work::findOrFail($workId);
        $available = $this->getUnlinkedToCItems($work);

        if ($available->isNotEmpty()) {
            $this->matchReferences($work, $available);
        }
    }

    /**
     * Get ToC Items that are not linked to anything in the doc.
     *
     * @param Work $work
     *
     * @return Collection<int, TocItem>
     */
    protected function getUnlinkedToCItems(Work $work): Collection
    {
        $referenceUids = Reference::where('work_id', $work->id)
            ->typeCitation()
            ->whereNotNull('uid')
            ->select(['uid']);

        return TocItem::where('doc_id', $work->active_doc_id)
            ->whereNotNull('label')
            ->whereNotIn('uid', $referenceUids)
            ->get(['id', 'uid', 'label'])
            ->map(function ($item) {
                /** @var TocItem $item */
                $item->label = static::cleanText($item->label ?? '');

                return $item;
            });
    }

    /**
     * Match the references to a ToC Item.
     *
     * @param Work                     $work
     * @param Collection<int, TocItem> $tocs
     *
     * @return void
     */
    public function matchReferences(Work $work, Collection $tocs): void
    {
        $toUpdate = [];
        $existing = $this->getUnlinkedReferences($work);

        foreach ($existing as $reference) {
            $matches = $tocs->where('label', $reference->getAttribute('label'));

            if ($matches->count() === 1) {
                /** @var TocItem $match */
                $match = $matches->first();
                $toUpdate[$match->uid_hash] = ['id' => $reference->id, 'uid' => $match->uid];
            }
        }

        if (!empty($toUpdate)) {
            /** @var Collection<int, array<string, mixed>> $insert */
            $insert = collect($toUpdate)->values();
            $this->updateReferences($insert);
        }
    }

    /**
     * Get references not linked to any toc item.
     *
     * @param Work $work
     *
     * @return Collection<int, Reference>
     */
    protected function getUnlinkedReferences(Work $work): Collection
    {
        $references = Reference::where('work_id', $work->id)
            ->typeCitation()
            ->whereNull('uid')
            ->with(['refPlainText:reference_id,plain_text', 'contentDraft:reference_id,title'])
            ->get(['id', 'uid'])
            ->map(function ($ref) {
                /** @var Reference $ref */
                $text = $ref->contentDraft->title ?? $ref->refPlainText->plain_text ?? '';
                $ref->setAttribute('label', static::cleanText($text));
                $ref->unsetRelation('refPlainText');

                return $ref;
            });

        return $references
            ->filter(function ($ref) use ($references) {
                // remove references that have the same label since it will not be possible to tell which
                // reference we should match it to.
                return $references->where('label', $ref->getAttribute('label'))->count() < 2;
            })
            ->values();
    }

    /**
     * Update the provided references.
     *
     * @param Collection<int, array<string, mixed>> $references
     *
     * @return void
     */
    protected function updateReferences(Collection $references): void
    {
        $ids = $references->pluck('id')->join(',');
        $table = (new Reference())->getTable();
        $fields = [];
        $values = [];

        foreach ($references as $ref) {
            $fields[] = " WHEN {$ref['id']} THEN ?";
            $values[] = $ref['uid'];
        }

        $query = sprintf("UPDATE %s SET uid = (CASE id %s \nEND) WHERE id IN (%s)", $table, implode("\n", $fields), $ids);

        DB::statement($query, $values);
    }
}
