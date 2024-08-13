<?php

namespace App\Http\Requests\Arachno;

use App\Enums\Arachno\ConsolidationFrequency;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SourceRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'monitoring' => $this->has('monitoring'),
            'ingestion' => $this->has('ingestion'),
            'permission_obtained' => $this->has('permission_obtained'),
            'hide_content' => $this->has('hide_content'),
            'has_script' => $this->has('has_script'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $sources = Source::class;

        $rules = [
            'title' => ['string', "unique:{$sources}", 'required', 'max:255'],
            'source_url' => ['url', 'nullable', 'max:255'],
            'source_content' => ['string', 'required'],
            'primary_language' => ['string', 'required'],
            'permission_obtained' => ['bool', 'required'],
            'hide_content' => ['bool', 'required'],
            'monitoring' => ['bool', 'required'],
            'ingestion' => ['bool', 'required'],
            'has_script' => ['bool', 'required'],
            'permission_note' => ['string', 'nullable'],
            'rights' => ['string', 'nullable'],
            'consolidation_frequency' => ['int', 'required', Rule::enum(ConsolidationFrequency::class)],
            'owner_id' => ['integer', 'nullable', Rule::exists(User::class, 'id')],
            'manager_id' => ['integer', 'nullable', Rule::exists(User::class, 'id')],
        ];

        if ($this->method() === 'POST') {
            return $rules;
        }

        /** @var string $sourceStr */
        $sourceStr = request('source');
        $rules['title'] = "required|unique:{$sources},title," . $sourceStr;

        return $rules;
    }
}
