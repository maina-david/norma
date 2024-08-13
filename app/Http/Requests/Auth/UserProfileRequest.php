<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserProfileRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'available' => $this->has('available'),
            'allows_communication' => $this->has('allows_communication'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        return [
            'nationality' => ['required', 'string', 'max:255'],
            'current_residency' => ['required', 'string', 'max:255'],
            'mobile_country_code' => ['nullable', 'string', 'max:255'],
            'phone_mobile' => ['nullable', 'string', 'max:255'],
            'hear_about' => ['nullable', 'string', 'max:255'],
            'university' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'year_of_study' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'active_url', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'address_line_3' => ['nullable', 'string', 'max:255'],
            'address_country_code' => ['nullable', 'string', 'max:255'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'available' => ['boolean'],
            'allows_communication' => ['boolean'],
            'languages' => ['nullable', 'array'],
            'proficiency' => ['nullable', 'array'],
            'photo' => ['nullable', 'image'],
        ];
    }
}
