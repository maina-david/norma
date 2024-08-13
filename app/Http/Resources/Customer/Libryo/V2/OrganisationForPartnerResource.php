<?php

namespace App\Http\Resources\Customer\Libryo\V2;

use App\Models\Customer\Organisation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Organisation */
class OrganisationForPartnerResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'integration_id' => $this->integration_id,
            'partner_id' => $this->partner_id,
        ];
    }
}
