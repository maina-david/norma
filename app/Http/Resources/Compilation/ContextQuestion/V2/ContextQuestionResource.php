<?php

namespace App\Http\Resources\Compilation\ContextQuestion\V2;

use App\Models\Compilation\ContextQuestion;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContextQuestion
 */
class ContextQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'question' => $this->toQuestion(),
            'category_id' => $this->category_id,
            'description' => $this->when($this->relationLoaded('descriptions'), function () {
                return $this->descriptions->first()?->description ?? null;
            }),
            'answer' => $this->whenLoaded('answers', function () {
                return $this->answers->first()?->answer;
            }),
        ];
    }
}
