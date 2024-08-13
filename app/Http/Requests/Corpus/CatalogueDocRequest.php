<?php

namespace App\Http\Requests\Corpus;

use App\Models\Arachno\Source;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Geonames\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogueDocRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:3000'],
            'title_translation' => ['nullable', 'string', 'max:3000'],
            'source_unique_id' => ['required', 'string', 'max:255', Rule::unique(CatalogueDoc::class)->where('source_id', $this->get('source_id'))],
            'start_url' => ['nullable', 'string', 'max:3000'],
            'view_url' => ['nullable', 'string', 'max:3000'],
            'source_id' => ['required', Rule::exists(Source::class, 'id')],
            'language_code' => ['required', 'string'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'primary_location_id' => ['required', Rule::exists(Location::class, 'id')],
        ];
    }
}
