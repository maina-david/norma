<?php

namespace App\Http\Resources\Corpus\Work\V2;

use App\Models\Corpus\Work;

/**
 * @mixin Work
 */
class WorkForOrganisationResource extends WorkResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            ...parent::toArray($request),
            'parent_ids' => $this->parents->modelKeys(),
        ];
    }
}
