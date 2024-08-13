<?php

namespace App\Http\Requests\Customer;

use App\Models\Partners\Partner;
use Illuminate\Validation\Rule;

class OrganisationPartnerRequest extends OrganisationRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'partner_id' => ['required', 'int', Rule::exists((new Partner())->getTable(), 'id')],
            'integration_id' => ['nullable', 'string'],
        ]);
    }
}
