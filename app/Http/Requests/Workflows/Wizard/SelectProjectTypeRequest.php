<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Enums\Workflows\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectProjectTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'project_type' => ['integer', Rule::enum(ProjectType::class)],
        ];
    }
}
