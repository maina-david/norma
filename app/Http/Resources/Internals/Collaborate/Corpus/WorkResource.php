<?php

namespace App\Http\Resources\Internals\Collaborate\Corpus;

use App\Models\Corpus\Work;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Work */
class WorkResource extends JsonResource
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
            'status' => $this->status,
            'title' => $this->title,
            'title_translation' => $this->title_translation,
            'work_type' => $this->work_type,
        ];
    }
}
