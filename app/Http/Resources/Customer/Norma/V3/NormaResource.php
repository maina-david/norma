<?php

namespace App\Http\Resources\Customer\Norma\V3;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Customer\Norma */
class NormaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'geo_lat' => $this->geo_lat,
            'geo_lng' => $this->geo_lng,
            'address' => $this->address,
            'description' => $this->description,
            'link' => route('my.normas.activate.redirect', [
                'norma' => $this->id,
                'redirect' => route('my.dashboard', [], false),
            ], false),
        ];
    }
}
