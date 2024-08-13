<?php

namespace App\Http\Resources\Corpus\Reference\V3;

use App\Services\Html\HtmlToText;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\Reference */
class ReferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->refPlainText->plain_text ?? '',
            'content' => trim(app(HtmlToText::class)->convert($this->htmlContent->cached_content ?? '')),
            'work_id' => $this->work_id,
            'streams' => $this->whenLoaded('libryos', fn () => $this->libryos->pluck('id')->all()),
            'link' => route('my.corpus.references.show', ['reference' => $this->id], false),
            'consequences' => $this->whenLoaded('raisesConsequenceGroups', function () {
                return $this->raisesConsequenceGroups->pluck('id')->reject(fn ($item) => $item === $this->id)->all();
            }),
        ];
    }
}
