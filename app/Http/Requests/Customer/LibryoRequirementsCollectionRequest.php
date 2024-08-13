<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Traits\HasCheckboxes;
use Illuminate\Foundation\Http\FormRequest;

class LibryoRequirementsCollectionRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = ['include_ancestors', 'include_descendants'];

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
     * @return array<string,mixed>
     */
    public function rules()
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'include_ancestors' => ['boolean'],
            'include_descendants' => ['boolean'],
        ];
    }
}
