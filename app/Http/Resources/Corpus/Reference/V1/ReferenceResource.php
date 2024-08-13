<?php

namespace App\Http\Resources\Corpus\Reference\V1;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Ontology\Tag\V1\TagCollection;
use App\Http\Resources\Requirements\Summary\V1\SummaryResource;
use App\Models\Corpus\Reference;

/**
 * @mixin Reference
 */
class ReferenceResource extends AbstractResource
{
    /**
     * Copied from the original transformer in the old code base - so lots of legacy here.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        /** @var array<string, mixed> */
        return array_merge([
            'id' => (int) $this->id,
            'citation_type' => null, // $this->citation_type,
            'number' => $this->refPlainText?->plain_text ? '' : ($this->citation->number ?? ''),
            'register_id' => $this->work_id,
            'version' => null, // $this->version,
            'status' => 1, // $this->status,
            'title' => $this->refPlainText?->plain_text ?? ($this->citation->heading ?? ''),
            'derived' => true,
            'sub_line' => '', // $this->sub_line,
            'content' => $this->htmlContent?->cached_content,
            'position' => $this->position ?? ($this->citation->position ?? ''),
            'item_type' => $this->typeToInt($this->citation->type ?? '') ?? null,
            'summary_id' => $this->id,
            'level' => $this->level,
            'is_toc_item' => true,
            'visible_in_toc' => true,
            'type' => $this->citation->type ?? '',
            'is_section' => (bool) ($this->is_section ?? ($this->citation->is_section ?? false)),
            'author_id' => null, // legacy
            $this->mergeWhen($this->relationLoaded('summary') && !is_null($this->summary), [
                'summary' => [
                    'data' => new SummaryResource($this->whenLoaded('summary')),
                ],
            ]),
            'tags' => new TagCollection($this->whenLoaded('tags')),
            'raisesConsequenceGroups' => new RaisesConsequenceGroupCollection($this->whenLoaded('raisesConsequenceGroups')),
        ], $this->timestamps());
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $type
     *
     * @return int|null
     */
    private function typeToInt(string $type): ?int
    {
        switch ($type) {
            case 'general':
                return 1;
            case 'chapter':
                return 2;
            case 'part':
                return 3;
            case 'section':
                return 4;
            case 'toc':
                return 5;
            case 'definition':
                return 6;
            case 'article':
                return 9;
            default:
                return null;
        }
    }
}
