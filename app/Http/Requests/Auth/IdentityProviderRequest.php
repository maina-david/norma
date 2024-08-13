<?php

namespace App\Http\Requests\Auth;

use App\Models\Customer\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdentityProviderRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'enabled' => $this->has('enabled'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entity_id' => ['required', 'string'],
            'slo_url' => ['nullable', 'active_url'],
            'certificate' => ['required', 'string'],
            'enabled' => ['required', 'boolean'],
            'team_id' => ['nullable', Rule::exists(Team::class, 'id')],
            'sso_url' => ['required', 'active_url'],
        ];
    }
}
