<?php

namespace App\Http\Requests\Assess;

use App\Models\Corpus\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssessmentItemReferenceRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reference_id' => ['required', Rule::exists(Reference::class, 'id')],
        ];
    }
}
