<?php

namespace App\Http\Requests\Compilation;

use App\Http\Requests\Traits\HasCheckboxes;
use Illuminate\Foundation\Http\FormRequest;

class GenericCompileRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = ['include_children'];

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
            'location_id' => ['integer', 'required'],
            'legal_domains' => ['array', 'nullable'],
            'include_children' => ['boolean'],
        ];
    }
}
