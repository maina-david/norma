<?php

namespace App\Http\Requests\Workflows;

use App\Models\Workflows\RatingGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property RatingGroup $rating_group
 */
class RatingGroupRequest extends FormRequest
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
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        return [
            'description' => ['string', 'nullable', 'max:255'],
            'name' => [
                'string', 'required', 'max:255',
                Rule::unique(RatingGroup::class)->ignore($this->rating_group, 'id'),
            ],
        ];
    }
}
