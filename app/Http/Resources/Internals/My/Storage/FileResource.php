<?php

namespace App\Http\Resources\Internals\My\Storage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Storage\My\File */
class FileResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'extension' => $this->extension,
            'date_last_accessed' => $this->date_last_accessed,
            'created_at' => $this->created_at,
            'title' => $this->title,
            'count_views' => $this->count_views,
            'show_preview' => $this->show_preview,
            'size' => $this->getHumanReadableSize(),
            'mime_type' => $this->mime_type,
        ];
    }
}
