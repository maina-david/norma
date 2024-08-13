<?php

namespace App\Http\Requests\Auth;

use App\Enums\Auth\UserType;
use App\Models\Auth\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsUserRequest extends FormRequest
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
        $rules = [
            'fname' => ['required', 'string', 'max:255'],
            'sname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('user'))],
            'mobile_country_code' => ['nullable', 'string', 'max:255'],
            'phone_mobile' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:255', 'timezone'],
        ];

        /** @var User $user */
        $user = $this->user();

        if ($user->can('access.org.settings.all')) {
            return array_merge($rules, [
                'user_type' => ['required', Rule::in(UserType::options())],
                'user_roles' => ['required', 'array'],
                'user_roles.*' => ['required', 'numeric'],
            ]);
        }

        return $rules;
    }
}
