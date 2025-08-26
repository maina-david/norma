<?php

namespace App\Http\Resources\Notify\LegalUpdate\V2;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Corpus\Work\V2\WorkResource;
use App\Http\Resources\Customer\Norma\V2\NormaForUpdateResource;
use App\Models\Notify\LegalUpdate;
use Illuminate\Http\Request;

/**
 * @mixin LegalUpdate
 */
class LegalUpdateResource extends AbstractResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
            'hash_id' => $this->hash_id,
            'title' => $this->title,
            'change_type' => null,
            'notifiable_id' => null, // legacy
            'update_report' => $this->work->update_report ?? $this->update_report,
            'interpretation' => $this->work->highlights ?? $this->highlights,
            'changes_to_register' => $this->work->changes_to_register ?? $this->changes_to_register,
            'gazette_number' => $this->work->gazette_number ?? $this->publication_number,
            'notice_number' => $this->work->notice_number ?? $this->publication_document_number,
            'work_id' => $this->work_id,
            'notify_register_item_id' => $this->notify_reference_id,
            'publication_date' => null,
            'effective_date' => $this->work ? ($this->work->effective_date?->format('Y-m-d') ?? null) : ($this->effective_date?->format('Y-m-d') ?? null),
            'comment_date' => $this->work ? ($this->work->comment_date?->format('Y-m-d') ?? null) : null,
            'release_at' => $this->release_at ? $this->release_at->format('Y-m-d') : null,
            'notification_date' => $this->notification_date->format('Y-m-d'),
            'notified' => false, // legacy - doesn't exist anymore
            'normas' => NormaForUpdateResource::collection($this->whenLoaded('normas')),
            'work' => new WorkResource($this->whenLoaded('work')),
        ], $this->timestamps());
    }
}
