<?php

namespace App\Http\Requests\Notify;

use App\Models\Arachno\Source;
use App\Models\Geonames\Location;
use App\Rules\Ontology\LegalDomainLocationCheck;
use App\Support\Languages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property bool $affected_available
 */
class UpdateCandidateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'content_resource_file' => [Rule::requiredIf($this->method() === self::METHOD_POST), 'file'],
            'title' => ['required', 'string', 'max:1000'],
            'title_translation' => ['nullable', 'string', 'max:1000'],
            'source_url' => ['nullable', 'string', 'max:1000'],
            'language_code' => ['required', 'string', Rule::in(array_keys(Languages::$alpha3To2))],
            'primary_location_id' => ['required', 'integer',  Rule::exists(Location::class, 'id')],
            'source_id' => ['required', 'integer',  Rule::exists(Source::class, 'id')],
            'domains' => ['array', new LegalDomainLocationCheck($this->get('primary_location_id'))],
            'domains.*' => ['integer'],
            'document_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
