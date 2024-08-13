<?php

namespace App\Services\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Citation;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceSelector;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\ReferenceTitleText;
use App\Models\Corpus\WorkExpression;

class CitationCreator
{
    /** @var callable|null */
    protected $onCreate = null;

    //    /**
    //     * Set Callback to be handled when creating.
    //     *
    //     * @param callable $onCreate
    //     *
    //     * @return CitationCreator
    //     */
    //    public function setOnCreate(callable $onCreate): self
    //    {
    //        $this->onCreate = $onCreate;
    //
    //        return $this;
    //    }

    /**
     * Create multiple citations for the given expression.
     *
     * @param WorkExpression          $expression
     * @param array<array-key, mixed> $citations
     *
     * @return void
     */
    public function createMulti(WorkExpression $expression, array $citations): void
    {
        foreach ($citations as $cite) {
            $this->createCitationForExpression($expression, $cite);
        }
    }

    /**
     * Create a citation for the given expression.
     *
     * @param WorkExpression       $expression
     * @param array<string, mixed> $citationArr
     *
     * @return Citation
     */
    protected function createCitationForExpression(WorkExpression $expression, array $citationArr): Citation
    {
        $citationData = $this->prepareDataForCitation($expression, $citationArr, 0);

        // @codeCoverageIgnoreStart
        if ($citationArr['type'] === 'body') {
            /** @var Citation|null $citation */
            $citation = Citation::where('work_id', $expression->work_id)->where('type', 'body')->first();

            if ($citation) {
                return $citation;
            }
        }
        // @codeCoverageIgnoreEnd

        /** @var Citation $citation */
        $citation = Citation::create($citationData);

        $title = trim(trim($citation->number ?? '') . ' ' . trim($citation->heading ?? ''));
        $data = [
            'referenceable_id' => $citation->id,
            'referenceable_type' => 'citations',
            'work_id' => $expression->work_id,
            'volume' => $citationArr['volume'],
            'status' => $citationArr['status'],
            'start' => $citationArr['start'],
            'type' => ReferenceType::citation()->value,
            'level' => $citationArr['level'],
            'position' => $citation->position,
            'is_section' => $citation->is_section,
        ];

        $ref = new Reference($data);

        Reference::withoutEvents(function () use ($ref) {
            $ref->save();

            return $ref;
        });

        $refSelector = new ReferenceSelector([
            'reference_id' => $ref->id,
            'selectors' => $citationArr['selectors'],
        ]);
        $ref->refSelector()->save($refSelector);

        $refTitleText = new ReferenceTitleText([
            'reference_id' => $ref->id,
            'text' => $citationArr['text'],
        ]);
        $ref->refTitleText()->save($refTitleText);

        $refPlainText = new ReferenceText([
            'reference_id' => $ref->id,
            'plain_text' => empty($title) ? $citationArr['text'] : $title,
        ]);

        $ref->refPlainText()->save($refPlainText);

        if ($this->onCreate) {
            // @codeCoverageIgnoreStart
            call_user_func($this->onCreate, $citation);
            // @codeCoverageIgnoreEnd
        }

        return $citation;
    }

    /**
     * Update the citation array for insertion.
     *
     * @param WorkExpression       $expression
     * @param array<string, mixed> $citationArr
     * @param int                  $position
     *
     * @return array<string, mixed>
     */
    public function prepareDataForCitation(WorkExpression $expression, array $citationArr, int $position): array
    {
        $isSection = $citationArr['type'] === 'section' || $citationArr['type'] === 'article';

        return [
            'work_id' => $expression->work_id,
            'level' => $citationArr['level'] ?? 1,
            'type' => $citationArr['type'] ?? null,
            'position' => $position,
            'visible' => true,
            'is_toc_item' => true,
            'is_section' => $citationArr['is_section'] ?? $isSection,
            'element_id' => $citationArr['element_id'] ?? null,
            'number' => $citationArr['number'] ?? null,
            'heading' => $citationArr['heading'] ?? null,
        ];
    }
}
