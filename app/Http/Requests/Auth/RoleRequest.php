<?php

namespace App\Http\Requests\Auth;

use App\Models\Auth\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'max:255'],
            'title' => [
                'required', 'string', 'max:255', Rule::unique(Role::class)->ignore($this->route('role')),
            ],
        ];
    }
}
