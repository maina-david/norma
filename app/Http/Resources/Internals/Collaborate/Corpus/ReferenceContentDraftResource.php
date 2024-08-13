<?php

namespace App\Http\Resources\Internals\Collaborate\Corpus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Corpus\ReferenceContentDraft
 *
 * @codeCoverageIgnore
 */
class ReferenceContentDraftResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'html_content' => $this->html_content,
            'title' => $this->title,
        ];
    }
}
