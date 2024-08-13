<?php

namespace App\Http\Requests\Assess;

use App\Enums\Assess\AssessmentItemType;
use App\Enums\Assess\RiskRating;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssessmentItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'component_1' => ['nullable'],
            'component_2' => ['nullable'],
            'component_3' => ['nullable'],
            'component_4' => ['nullable'],
            'component_5' => ['nullable'],
            'component_6' => ['nullable'],
            'component_7' => ['nullable'],
            'component_8' => ['nullable'],
            'frequency' => ['nullable', 'numeric'],
            'start_due_offset' => ['nullable', 'numeric'],
            'type' => ['required', 'numeric', Rule::enum(AssessmentItemType::class)],
            'risk_rating' => ['required', 'numeric', Rule::in(RiskRating::options())],
            'legal_domain_id' => ['nullable', 'numeric', Rule::exists((new LegalDomain())->getTable(), 'id')],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['numeric', Rule::exists((new Category())->getTable(), 'id')],
        ];
    }
}
