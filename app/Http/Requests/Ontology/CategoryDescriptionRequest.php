<?php

namespace App\Http\Requests\Ontology;

use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryDescriptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed>
     */
    public function rules()
    {
        return [
            'category_id' => ['required', Rule::exists(Category::class, 'id')],
            'location_id' => ['nullable', Rule::exists(Location::class, 'id')],
            'description' => ['string', 'required'],
        ];
    }
}
