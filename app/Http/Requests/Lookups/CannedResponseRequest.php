<?php

namespace App\Http\Requests\Lookups;

use App\Enums\Lookups\CannedResponseField;
use App\Models\Lookups\CannedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CannedResponseRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules()
    {
        return [
            'for_field' => [
                'string', 'max:255', Rule::in(array_values(CannedResponseField::options())),
            ],
            'response' => [
                'string',
                'required',
                Rule::unique((new CannedResponse())->getTable())
                    ->where(function ($query) {
                        return $query->where('for_field', $this->get('for_field'));
                    })
                    ->ignore($this->route('canned_response')),
            ],
        ];
    }
}
