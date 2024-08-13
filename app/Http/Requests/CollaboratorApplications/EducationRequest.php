<?php

namespace App\Http\Requests\CollaboratorApplications;

use App\Enums\Collaborators\LanguageProficiency;
use App\Services\Storage\MimeTypeManager;
use App\Support\Languages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EducationRequest extends FormRequest
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
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $mimes = implode(',', (new MimeTypeManager())->getAcceptedUploadMimes());

        return [
            'skills' => ['required', 'array'],
            'university' => ['required', 'string', 'max:255'],
            'year_of_study' => ['required', 'string', 'max:255'],
            'language' => ['required', 'array', 'max:255'],
            'language.*' => ['required', 'string', Rule::in(array_keys(Languages::all()))],
            'proficiency' => ['required', 'array', 'max:255'],
            'proficiency.*' => ['required', 'string', Rule::in(array_keys(LanguageProficiency::lang()))],
            'education_transcript' => ['required', 'file', 'max:51200', "mimetypes:{$mimes}"],
            'curriculum_vitae' => ['required', 'file', 'max:51200', "mimetypes:{$mimes}"],
            'linkedin_url' => ['nullable', 'active_url', 'max:255'],
        ];
    }
}
