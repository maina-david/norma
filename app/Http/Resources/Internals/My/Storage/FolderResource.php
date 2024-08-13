<?php

namespace App\Http\Resources\Internals\My\Storage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Storage\My\Folder */
class FolderResource extends JsonResource
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
            'children_count' => $this->children_count,
            'title' => $this->title,
        ];
    }
}
