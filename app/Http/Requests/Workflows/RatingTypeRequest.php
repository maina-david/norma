<?php

namespace App\Http\Requests\Workflows;

use Illuminate\Foundation\Http\FormRequest;

class RatingTypeRequest extends FormRequest
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
            'name' => ['string', 'required', 'max:255'],
            'description' => ['string', 'nullable', 'max:255'],
            'course_link' => ['url', 'nullable', 'max:255'],
            'icon' => ['string', 'required', 'max:255'],
        ];
    }
}
