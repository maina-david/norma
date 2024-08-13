<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Traits\HasCheckboxes;
use App\Models\Customer\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class OrganisationRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = ['translation_enabled', 'is_parent'];

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'parent_id' => $this->has('is_parent') ? null : $this->get('parent_id'),
        ]);
    }

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
        $libraries = Organisation::class;

        $rules = [
            'title' => ['string', "unique:{$libraries}", 'required', 'max:255'],
            'is_parent' => ['boolean'],
            'translation_enabled' => ['boolean'],
            'type' => ['integer', 'nullable'],
            'plan' => ['integer', 'nullable'],
            'customer_category' => ['string', 'nullable'],
            'whitelabel_id' => ['integer', 'nullable'],
            'partner_id' => ['integer', 'nullable'],
            'parent_id' => ['integer', 'nullable'],
        ];

        if ($this->method() === 'POST') {
            return $rules;
        }

        /** @var string|Organisation $org */
        $org = request('organisation');
        $org = $org instanceof Organisation ? $org->id : $org;
        $rules['title'] = "required|unique:{$libraries},title," . $org;

        return $rules;
    }
}
