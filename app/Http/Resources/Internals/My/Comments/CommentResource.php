<?php

namespace App\Http\Resources\Internals\My\Comments;

use App\Http\Resources\Internals\My\Auth\UserResource;
use App\Http\Resources\Internals\My\Storage\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Comments\Comment */
class CommentResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'pinned' => $this->pinned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'files_count' => $this->files_count,
            'replies_count' => $this->replies_count,
            'comments_count' => $this->comments_count,
            'author_id' => $this->author_id,
            'place_id' => $this->place_id,

            'author' => new UserResource($this->whenLoaded('author')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'files' => FileResource::collection($this->whenLoaded('files')),
        ];
    }
}
