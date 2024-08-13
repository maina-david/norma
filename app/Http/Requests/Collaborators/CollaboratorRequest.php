<?php

namespace App\Http\Requests\Collaborators;

use Illuminate\Foundation\Http\FormRequest;

class CollaboratorRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_team_admin' => (int) ($this->get('team_id') == '-1' || $this->has('is_team_admin')),
        ]);
    }

    /**
     * @codeCoverageIgnore
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fname' => ['required', 'string', 'max:255'],
            'sname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'max:255', 'unique:users'],
            'team_id' => ['required', 'integer'],
            'is_team_admin' => ['integer', 'boolean'],
            'groups' => ['required', 'array'],
        ];
    }
}
