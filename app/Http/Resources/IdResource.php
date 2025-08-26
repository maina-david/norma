<?php

namespace App\Http\Resources;

use App\Models\Customer\Norma;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * //mixin can be any model with an ID...
 *
 * @mixin Norma
 */
class IdResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string,mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
