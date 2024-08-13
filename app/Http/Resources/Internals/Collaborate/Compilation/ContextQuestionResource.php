<?php

namespace App\Http\Resources\Internals\Collaborate\Compilation;

use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ContextQuestionReferenceDraft $pivot
 *
 * @mixin \App\Models\Compilation\ContextQuestion
 */
class ContextQuestionResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->question,
            'change_status' => $this->whenPivotLoaded(ContextQuestionReferenceDraft::class, fn () => $this->pivot->change_status),
            'hash_id' => $this->hash_id,
        ];
    }
}
