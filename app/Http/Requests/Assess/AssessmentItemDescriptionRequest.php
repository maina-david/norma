<?php

namespace App\Http\Requests\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Geonames\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssessmentItemDescriptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        return [
            'assessment_item_id' => ['required', Rule::exists(AssessmentItem::class, 'id')],
            'location_id' => ['nullable', Rule::exists(Location::class, 'id')],
            'description' => ['string', 'required'],
        ];
    }
}
