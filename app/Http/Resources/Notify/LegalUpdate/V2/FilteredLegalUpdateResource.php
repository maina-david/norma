<?php

namespace App\Http\Resources\Notify\LegalUpdate\V2;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Corpus\Work\V2\WorkForFilteredUpdatesResource;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Notify\LegalUpdate;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin LegalUpdate
 */
class FilteredLegalUpdateResource extends AbstractResource
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
        $arr = [
            'sent_to_user' => false,
        ];
        if ($me = $this->users->find(Auth::user())) {
            /** @var User $me */
            $arr['read_status'] = (bool) ($me->pivot->read_status ?? false);
            $arr['understood_status'] = (bool) ($me->pivot->understood_status ?? false);
            $arr['sent_to_user'] = true;
        }

        // this is a hack for this filtered updates end point so we can keep the structure the same
        // for partners, but use the new relations on the update
        $this->load(['work']);

        // @codeCoverageIgnoreStart
        if (!$this->work) {
            $this->work = new Work();
        }
        // @codeCoverageIgnoreEnd

        // @phpstan-ignore-next-line
        $this->work['for_update_legal_domains'] = $this->legalDomains;
        // @phpstan-ignore-next-line
        $this->work['for_update_locations'] = $this->primaryLocation ? collect([$this->primaryLocation]) : collect([]);

        return array_merge([
            'id' => $this->id,
            'hash_id' => $this->hash_id,
            'title' => $this->title,
            'notification_date' => $this->notification_date->format('Y-m-d'),
            'interpretation' => $this->work->highlights ?? '',
            'release_at' => $this->release_at?->format('Y-m-d') ?? null,
            'work' => new WorkForFilteredUpdatesResource($this->whenLoaded('work')),
            'places_count' => $this->places_count,
            ...$arr,
        ], $this->timestamps());
    }
}
