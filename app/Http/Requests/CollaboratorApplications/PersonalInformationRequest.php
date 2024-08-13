<?php

namespace App\Http\Requests\CollaboratorApplications;

use App\Models\Auth\User;
use App\Models\Collaborators\CollaboratorApplication;
use Illuminate\Foundation\Http\FormRequest;

class PersonalInformationRequest extends FormRequest
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
        $applications = CollaboratorApplication::class;
        $users = User::class;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:{$applications}", "unique:{$users}"],
            'mobile_country_code' => ['nullable', 'string', 'max:255'],
            'phone_mobile' => ['nullable', 'string', 'max:255'],
            'hear_about' => ['nullable', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
            'current_residency' => ['required', 'string', 'max:255'],
        ];
    }
}
