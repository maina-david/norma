<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

class SystemNotificationRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        $this->merge([
            'is_permanent' => $this->boolean('is_permanent'),
            'active' => $this->boolean('active'),
            'has_user_action' => $this->boolean('has_user_action'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $rules = [
            'title' => ['string', 'required', 'max:255'],
            'expiry_date' => ['date', 'required'],
            'type' => ['integer', 'required'],
            'content' => ['string', 'required'],
            'is_permanent' => ['boolean', 'nullable'],
            'active' => ['boolean', 'nullable'],
            'has_user_action' => ['boolean', 'nullable'],
            'user_action_text' => ['string', 'nullable', 'max:255'],
            'user_action_link' => ['string', 'nullable', 'max:255'],
        ];

        return $rules;
    }
}
