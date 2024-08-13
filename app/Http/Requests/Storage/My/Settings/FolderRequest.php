<?php

namespace App\Http\Requests\Storage\My\Settings;

use App\Enums\Storage\My\FolderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FolderRequest extends FormRequest
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
            'title' => ['string', 'required', 'max:255'],
            'folder_type' => ['required', Rule::in([FolderType::organisation()->value, FolderType::libryo()->value])],
        ];
    }
}
