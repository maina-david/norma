<?php

namespace App\Http\Requests\Corpus;

use App\Models\Arachno\Source;
use App\Models\Corpus\CatalogueWork;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'changes_to_register' => ['nullable', 'string'],
            'comment_date' => ['nullable', 'date'],
            'effective_date' => ['nullable', 'date'],
            'emanating_actor' => ['nullable'],
            'gazette_number' => ['nullable', 'string', 'max:255'],
            'highlights' => ['nullable', 'string'],
            'iri' => ['nullable', 'string', 'max:255'],
            'issuing_authority' => ['nullable', 'string'],
            'language_code' => ['required', 'string', 'size:3'],
            'last_accessed' => ['nullable'],
            'notice_number' => ['nullable'],
            'organisation_id' => ['nullable', Rule::exists(Organisation::class, 'id')],
            'primary_location_id' => ['required', Rule::exists(Location::class, 'id')],
            'repealed_date' => ['nullable', 'date'],
            'short_title' => ['nullable', 'string', 'max:255'],
            'source_document' => ['nullable', 'file'],
            'source_id' => ['required', Rule::exists(Source::class, 'id')],
            'source_url' => ['nullable', 'url'],
            'status' => ['required', 'numeric'],
            'sub_type' => ['nullable'],
            'title' => ['required', 'string', 'max:1000'],
            'title_translation' => ['nullable', 'string', 'max:1000'],
            'update_report' => ['nullable', 'string'],
            'work_date' => ['required', 'date'],
            'work_number' => ['required'],
            'work_type' => ['required', 'string'],
            'catalogue_work_id' => ['nullable', 'integer', Rule::exists(CatalogueWork::class, 'id')],
        ];
    }
}
