<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Traits\HasCheckboxes;
use Illuminate\Foundation\Http\FormRequest;

class OrganisationModulesRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = [
        'actions',
        'tasks',
        'comply',
        'drives',
        'comments',
        'live_chat',
        'update_emails',
        'keyword_search',
        'search_requirements_and_drives',
        'sso',
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
            'tasks' => ['boolean'],
            'comply' => ['boolean'],
            'drives' => ['boolean'],
            'comments' => ['boolean'],
            'live_chat' => ['boolean'],
            'update_emails' => ['boolean'],
            'keyword_search' => ['boolean'],
            'search_requirements_and_drives' => ['boolean'],
            'sso' => ['boolean'],
        ];
    }
}
