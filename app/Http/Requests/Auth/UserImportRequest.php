<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Traits\HasExcelImport;
use App\Models\Customer\Team;
use Illuminate\Foundation\Http\FormRequest;

class UserImportRequest extends FormRequest
{
    use HasExcelImport;

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
            'team_id' => ['required', 'array'],
            'team_id.*' => ['required', 'numeric', sprintf('exists:%s,id', Team::class)],
            'users' => $this->excelImportRules(),
        ];
    }
}
