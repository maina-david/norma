<?php

namespace App\Http\Requests\Collaborators;

use Illuminate\Foundation\Http\FormRequest;

class TeamBankDetailsRequest extends FormRequest
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
            'bank_name' => ['string', 'required', 'max:255'],
            'bank_country' => ['string', 'required', 'max:255'],
            'branch_code' => ['string', 'nullable', 'max:255'],
            'account_name' => ['string', 'required', 'max:255'],
            'account_number' => ['string', 'required', 'max:255'],
            'account_currency' => ['string', 'required', 'max:255'],
            'iban' => ['string', 'nullable', 'max:255'],
            'swift_code' => ['string', 'nullable', 'max:255'],
            'billing_address' => ['string', 'nullable', 'max:255'],
            'other_billing_instructions' => ['string', 'nullable', 'max:255'],
            // 'bank_details_plain' => ['string', 'nullable'],
        ];
    }
}
