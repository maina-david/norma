<?php

namespace App\Http\Requests\Compilation;

use App\Http\Requests\Traits\HasCheckboxes;
use App\Models\Compilation\Library;
use Illuminate\Foundation\Http\FormRequest;

class LibraryRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = ['auto_compiled'];

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
        $libraries = Library::class;

        $rules = [
            'title' => ['string', "unique:{$libraries}", 'required', 'max:255'],
            'description' => ['string', 'max:2000', 'nullable'],
            'auto_compiled' => ['boolean'],
        ];

        if ($this->method() === 'POST') {
            return $rules;
        }

        /** @var string $libStr */
        $libStr = request('library');
        $rules['title'] = "required|unique:{$libraries},title," . $libStr;

        return $rules;
    }
}
