<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Traits\HasCheckboxes;
use Illuminate\Foundation\Http\FormRequest;

class LibryoModulesRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = [
        'actions',
        'comply',
        'tasks',
        'update_emails',
        'search_requirements_and_drives',
        'keyword_search',
        'drives',
        'hide_applicability',
    ];

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
        return [
            'actions' => ['boolean'],
            'comply' => ['boolean'],
            'tasks' => ['boolean'],
            'update_emails' => ['boolean'],
            'search_requirements_and_drives' => ['boolean'],
            'keyword_search' => ['boolean'],
            'drives' => ['boolean'],
            'hide_applicability' => ['boolean'],
        ];
    }
}
