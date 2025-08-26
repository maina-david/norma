<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Traits\HasCheckboxes;
use Illuminate\Foundation\Http\FormRequest;

class NormaCompilationSettingRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = [
        'use_collections',
        'use_legal_domains',
        'include_no_legal_domains',
        'use_context_questions',
        'include_no_context_questions',
        'use_topics',
        'include_no_topics',
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
            'use_collections' => ['boolean'],
            'use_legal_domains' => ['boolean'],
            'include_no_legal_domains' => ['boolean'],
            'use_context_questions' => ['boolean'],
            'include_no_context_questions' => ['boolean'],
            'use_topics' => ['boolean'],
            'include_no_topics' => ['boolean'],
        ];
    }
}
