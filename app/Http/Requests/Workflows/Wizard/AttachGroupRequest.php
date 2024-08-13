<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Models\Collaborators\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'group_id' => ['required', Rule::exists(Group::class, 'id')],
        ];
    }
}
