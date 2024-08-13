<?php

namespace App\Http\Requests\Corpus;

use App\Models\Arachno\Source;
use App\Models\Ontology\WorkType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocForCatalogueDocRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'content_resource_file' => [Rule::requiredIf($this->method() === self::METHOD_POST), 'file', 'mimetypes:application/pdf'],
            'title' => ['required', 'string', 'max:1000'],
            'title_translation' => ['nullable', 'string', 'max:1000'],
            'source_url' => ['nullable', 'string', 'max:1000'],
            'download_url' => ['nullable', 'string', 'max:1000'],
            'work_number' => ['nullable', 'string', 'max:1000'],
            'work_type_id' => ['required', 'numeric',  Rule::exists(WorkType::class, 'id')],
            'publication_number' => ['nullable', 'string', 'max:1000'],
            'publication_document_number' => ['nullable', 'string', 'max:1000'],
            'source_id' => ['required', 'integer',  Rule::exists(Source::class, 'id')],
            'work_date' => ['nullable', 'date_format:Y-m-d'],
            'effective_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
